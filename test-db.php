<?php

require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/core/Database.php';

header('Content-Type: text/html; charset=utf-8');

echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="public/assets/app.css">';
echo '<section class="panel" style="margin:24px">';
echo '<h1>Test ket noi SQL Server</h1>';
echo '<p>Driver: <b>System.Data.SqlClient</b> qua PowerShell</p>';

try {
    $config = require __DIR__ . '/app/config/database.php';
    echo '<p>Server: <b>' . htmlspecialchars($config['server']) . '</b></p>';
    echo '<p>Database: <b>' . htmlspecialchars($config['database']) . '</b></p>';

    $rows = Database::fetchAll("SELECT DB_NAME() AS DatabaseName, SUSER_SNAME() AS LoginName, SYSTEM_USER AS SystemUser, @@SERVERNAME AS ServerName");
    echo '<div class="alert">Ket noi thanh cong.</div>';
    echo '<pre>' . htmlspecialchars(print_r($rows, true), ENT_QUOTES, 'UTF-8') . '</pre>';

    $tables = Database::fetchAll("SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME");
    echo '<h2>Danh sach bang</h2>';
    echo '<pre>' . htmlspecialchars(print_r($tables, true), ENT_QUOTES, 'UTF-8') . '</pre>';
} catch (Throwable $e) {
    echo '<div class="alert">Ket noi that bai.</div>';
    echo '<pre style="white-space:pre-wrap">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
}

echo '</section>';
