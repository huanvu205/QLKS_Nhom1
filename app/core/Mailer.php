<?php

class Mailer
{
    public static function send(string $to, string $subject, string $body): bool
    {
        $to = trim($to);
        if ($to === '') {
            return false;
        }

        $config = require __DIR__ . '/../config/mail.php';

        if (($config['mode'] ?? 'log') === 'smtp') {
            return self::sendSmtp($config, $to, $subject, $body);
        }

        return self::writeLog($config, $to, $subject, $body);
    }

    private static function writeLog(array $config, string $to, string $subject, string $body): bool
    {
        $dir = __DIR__ . '/../../storage/mail_outbox';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $file = $dir . '/' . date('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9_-]+/', '_', $to) . '.html';
        $html = '<meta charset="utf-8">';
        $html .= '<h2>' . htmlspecialchars($subject, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</h2>';
        $html .= '<p><b>From:</b> ' . htmlspecialchars($config['from'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        $html .= '<p><b>To:</b> ' . htmlspecialchars($to, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
        $html .= '<hr>' . $body;

        return file_put_contents($file, $html) !== false;
    }

    private static function sendSmtp(array $config, string $to, string $subject, string $body): bool
    {
        if (empty($config['smtp_host']) || empty($config['smtp_user']) || empty($config['smtp_pass'])) {
            self::writeLog(
                $config,
                $to,
                '[Chua cau hinh SMTP] ' . $subject,
                '<p>Chua dien smtp_user/smtp_pass trong app/config/mail.php hoac bien moi truong.</p>' . $body
            );
            return false;
        }

        $mailFile = tempnam(sys_get_temp_dir(), 'qlks_mail_') . '.html';
        $psFile = tempnam(sys_get_temp_dir(), 'qlks_mail_') . '.ps1';
        file_put_contents($mailFile, "\xEF\xBB\xBF" . $body);

        $script = <<<'PS'
param(
    [string]$To,
    [string]$From,
    [string]$Subject,
    [string]$BodyFile,
    [string]$SmtpHost,
    [int]$SmtpPort,
    [string]$User,
    [string]$Pass
)
$ErrorActionPreference = 'Stop'
$body = Get-Content -LiteralPath $BodyFile -Raw -Encoding UTF8
if ($User -and $Pass) {
    $secure = ConvertTo-SecureString $Pass -AsPlainText -Force
    $cred = New-Object System.Management.Automation.PSCredential($User, $secure)
    Send-MailMessage -To $To -From $From -Subject $Subject -Body $body -BodyAsHtml -SmtpServer $SmtpHost -Port $SmtpPort -UseSsl -Credential $cred -Encoding UTF8
} else {
    Send-MailMessage -To $To -From $From -Subject $Subject -Body $body -BodyAsHtml -SmtpServer $SmtpHost -Port $SmtpPort -Encoding UTF8
}
PS;

        file_put_contents($psFile, $script);
        $command = implode(' ', [
            'powershell',
            '-NoProfile',
            '-ExecutionPolicy', 'Bypass',
            '-File', escapeshellarg($psFile),
            '-To', escapeshellarg($to),
            '-From', escapeshellarg($config['from']),
            '-Subject', escapeshellarg($subject),
            '-BodyFile', escapeshellarg($mailFile),
            '-SmtpHost', escapeshellarg($config['smtp_host']),
            '-SmtpPort', (string) $config['smtp_port'],
            '-User', escapeshellarg($config['smtp_user']),
            '-Pass', escapeshellarg($config['smtp_pass']),
        ]) . ' 2>&1';

        exec($command, $output, $code);
        @unlink($mailFile);
        @unlink($psFile);

        if ($code !== 0) {
            self::writeLog($config, $to, '[SMTP loi] ' . $subject, '<pre>' . htmlspecialchars(implode("\n", $output)) . '</pre>' . $body);
            return false;
        }

        return true;
    }
}
