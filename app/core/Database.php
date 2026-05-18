<?php

class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        $config = self::config();

        if (($config['driver'] ?? 'pdo_sqlsrv') !== 'pdo_sqlsrv') {
            throw new RuntimeException('Che do hien tai khong dung PDO connection.');
        }

        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $dsn = sprintf(
            'sqlsrv:Server=%s;Database=%s;TrustServerCertificate=%s',
            $config['server'],
            $config['database'],
            $config['trust_server_certificate'] ? 'true' : 'false'
        );

        self::$pdo = new PDO($dsn, $config['username'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        return self::$pdo;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        if (self::usesSqlcmd()) {
            $sql = self::bindParams($sql, $params);
            $sql = rtrim(trim($sql), ';') . ' FOR JSON PATH, INCLUDE_NULL_VALUES;';
            $json = trim(self::runSqlcmd("SET NOCOUNT ON;\n" . $sql));

            if ($json === '') {
                return [];
            }

            $data = json_decode($json, true);
            if (!is_array($data)) {
                throw new RuntimeException('Khong doc duoc JSON tu sqlcmd: ' . $json);
            }

            return $data;
        }

        if (self::usesPowerShell()) {
            $json = trim(self::runPowerShell(self::bindParams($sql, $params), 'fetch'));
            if ($json === '') {
                return [];
            }

            $data = json_decode($json, true);
            if (!is_array($data)) {
                throw new RuntimeException('Khong doc duoc JSON tu PowerShell: ' . $json);
            }

            return $data;
        }

        $stmt = self::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function fetch(string $sql, array $params = []): ?array
    {
        $rows = self::fetchAll($sql, $params);
        return $rows[0] ?? null;
    }

    public static function execute(string $sql, array $params = []): bool
    {
        if (self::usesSqlcmd()) {
            self::runSqlcmd("SET NOCOUNT ON;\n" . self::bindParams($sql, $params));
            return true;
        }

        if (self::usesPowerShell()) {
            self::runPowerShell(self::bindParams($sql, $params), 'execute');
            return true;
        }

        $stmt = self::connection()->prepare($sql);
        return $stmt->execute($params);
    }

    private static function config(): array
    {
        return require __DIR__ . '/../config/database.php';
    }

    private static function usesSqlcmd(): bool
    {
        $config = self::config();
        return ($config['driver'] ?? '') === 'sqlcmd';
    }

    private static function usesPowerShell(): bool
    {
        $config = self::config();
        return ($config['driver'] ?? '') === 'powershell';
    }

    private static function bindParams(string $sql, array $params): string
    {
        foreach ($params as $param) {
            $sql = preg_replace('/\?/', self::quote($param), $sql, 1);
        }

        return $sql;
    }

    private static function quote(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return "N'" . str_replace("'", "''", (string) $value) . "'";
    }

    private static function runSqlcmd(string $sql): string
    {
        $config = self::config();
        $file = tempnam(sys_get_temp_dir(), 'qlks_');

        if ($file === false) {
            throw new RuntimeException('Khong tao duoc file SQL tam.');
        }

        file_put_contents($file, "\xEF\xBB\xBF" . $sql);

        $parts = [
            escapeshellarg($config['sqlcmd'] ?? 'sqlcmd'),
            '-S', escapeshellarg($config['server']),
            '-d', escapeshellarg($config['database']),
            '-W',
            '-h', '-1',
            '-w', '65535',
            '-y', '0',
            '-Y', '0',
            '-f', '65001',
            '-b',
        ];

        if (!empty($config['trust_server_certificate'])) {
            $parts[] = '-C';
        }

        if (!empty($config['windows_auth'])) {
            $parts[] = '-E';
        } else {
            $parts[] = '-U';
            $parts[] = escapeshellarg($config['username']);
            $parts[] = '-P';
            $parts[] = escapeshellarg($config['password']);
        }

        $parts[] = '-i';
        $parts[] = escapeshellarg($file);

        $command = implode(' ', $parts) . ' 2>&1';
        exec($command, $output, $code);
        @unlink($file);

        $text = trim(implode("\n", $output));
        if ($code !== 0) {
            throw new RuntimeException("sqlcmd loi:\n" . $text);
        }

        return $text;
    }

    private static function runPowerShell(string $sql, string $mode): string
    {
        $config = self::config();
        $sqlFile = tempnam(sys_get_temp_dir(), 'qlks_sql_');
        $psFile = tempnam(sys_get_temp_dir(), 'qlks_ps_') . '.ps1';

        if ($sqlFile === false) {
            throw new RuntimeException('Khong tao duoc file SQL tam.');
        }

        file_put_contents($sqlFile, "\xEF\xBB\xBF" . "SET NOCOUNT ON;\n" . $sql);

        $encrypt = !empty($config['encrypt']) ? 'True' : 'False';
        $auth = !empty($config['windows_auth'])
            ? 'Integrated Security=True;'
            : 'User ID=' . str_replace(';', '', $config['username']) . ';Password=' . str_replace(';', '', $config['password']) . ';';
        $conn = 'Server=' . $config['server'] . ';Database=' . $config['database'] . ';' . $auth . 'TrustServerCertificate=True;Encrypt=' . $encrypt . ';';

        $script = <<<'PS'
param(
    [string]$SqlFile,
    [string]$ConnectionString,
    [string]$Mode
)

$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.Data
$sql = Get-Content -LiteralPath $SqlFile -Raw -Encoding UTF8
$connection = New-Object System.Data.SqlClient.SqlConnection($ConnectionString)
$command = $connection.CreateCommand()
$command.CommandText = $sql
$command.CommandTimeout = 60
$connection.Open()

if ($Mode -eq 'execute') {
    [void]$command.ExecuteNonQuery()
    $connection.Close()
    exit 0
}

$reader = $command.ExecuteReader()
$rows = New-Object System.Collections.Generic.List[object]
while ($reader.Read()) {
    $obj = [ordered]@{}
    for ($i = 0; $i -lt $reader.FieldCount; $i++) {
        $name = $reader.GetName($i)
        if ($reader.IsDBNull($i)) {
            $obj[$name] = $null
        } else {
            $obj[$name] = $reader.GetValue($i).ToString()
        }
    }
    $rows.Add([pscustomobject]$obj)
}
$reader.Close()
$connection.Close()
$json = $rows | ConvertTo-Json -Depth 6 -Compress
if (-not $json) { $json = '[]' }
[Convert]::ToBase64String([System.Text.Encoding]::UTF8.GetBytes($json))
PS;

        file_put_contents($psFile, $script);

        $command = implode(' ', [
            'powershell',
            '-NoProfile',
            '-ExecutionPolicy', 'Bypass',
            '-File', escapeshellarg($psFile),
            '-SqlFile', escapeshellarg($sqlFile),
            '-ConnectionString', escapeshellarg($conn),
            '-Mode', escapeshellarg($mode),
        ]) . ' 2>&1';

        exec($command, $output, $code);
        @unlink($sqlFile);
        @unlink($psFile);

        $text = trim(implode("\n", $output));
        if ($code !== 0) {
            throw new RuntimeException("PowerShell SQL loi:\n" . $text);
        }

        if ($mode === 'fetch' && $text !== '') {
            $decoded = base64_decode($text, true);
            if ($decoded === false) {
                return $text;
            }

            if (str_starts_with($decoded, '{')) {
                return '[' . $decoded . ']';
            }

            return $decoded;
        }

        return $text;
    }
}
