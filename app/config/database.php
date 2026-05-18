<?php

return [
    // powershell: dung System.Data.SqlClient qua PowerShell, khong can pdo_sqlsrv.
    // sqlcmd: dung SQLCMD neu may ket noi duoc bang sqlcmd.
    // pdo_sqlsrv: chi dung khi da cai extension pdo_sqlsrv.
    'driver' => getenv('QLKS_DB_DRIVER') ?: 'powershell',
    'server' => getenv('QLKS_DB_SERVER') ?: 'LAPTOP-64QR9LTM\\VUVANHUAN',
    'database' => getenv('QLKS_DB_NAME') ?: 'QLKS_Nhom1',
    'username' => getenv('QLKS_DB_USER') ?: '',
    'password' => getenv('QLKS_DB_PASS') ?: '',
    'windows_auth' => true,
    'sqlcmd' => getenv('QLKS_SQLCMD') ?: 'sqlcmd',
    'trust_server_certificate' => true,
    'encrypt' => false,
];
