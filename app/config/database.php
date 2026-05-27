<?php

// Auto-detect if running on InfinityFree or remote server
$isRemote = isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'infinityfree') !== false;

if ($isRemote) {
    return [
        'driver' => 'mysql',
        'server' => 'sql113.infinityfree.com',
        'username' => 'if0_42025653',
        'password' => 'Huan12042005',
        'database' => 'if0_42025653_qlks',
    ];
}

return [
    // powershell: dung System.Data.SqlClient qua PowerShell, khong can pdo_sqlsrv.
    // sqlcmd: dung SQLCMD neu may ket noi duoc bang sqlcmd.
    // pdo_sqlsrv: chi dung khi da cai extension pdo_sqlsrv.
    'driver' => getenv('QLKS_DB_DRIVER') ?: 'powershell',
    'server' => getenv('QLKS_DB_SERVER') ?: 'LAPTOP-64QR9LTM\VUVANHUAN',
    'database' => getenv('QLKS_DB_NAME') ?: 'QLKS_Nhom1',
    'username' => getenv('QLKS_DB_USER') ?: '',
    'password' => getenv('QLKS_DB_PASS') ?: '',
    'windows_auth' => true,
    'sqlcmd' => getenv('QLKS_SQLCMD') ?: 'sqlcmd',
    'trust_server_certificate' => true,
    'encrypt' => false,
];
