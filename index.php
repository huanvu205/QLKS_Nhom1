<?php

session_start();

require_once __DIR__ . '/app/config/database.php';
require_once __DIR__ . '/app/core/Database.php';
require_once __DIR__ . '/app/core/Mailer.php';
require_once __DIR__ . '/app/core/Controller.php';
require_once __DIR__ . '/app/core/Router.php';
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/RoomController.php';
require_once __DIR__ . '/app/controllers/CustomerController.php';
require_once __DIR__ . '/app/controllers/BookingController.php';

$router = new Router();

try {
    $router->dispatch($_GET['page'] ?? 'dashboard');
} catch (Throwable $e) {
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><link rel="stylesheet" href="public/assets/app.css">';
    echo '<section class="panel" style="margin:24px">';
    echo '<h1>Loi ket noi hoac xu ly du lieu</h1>';
    echo '<p>Project dang dung <b>System.Data.SqlClient</b> qua PowerShell, khong dung pdo_sqlsrv.</p>';
    echo '<p>Kiem tra SQL Server, database <b>QLKS_Nhom1</b>, server trong <b>app/config/database.php</b>, va dam bao ban chay <b>php -S</b> bang dung user Windows co quyen vao SQL Server.</p>';
    echo '<p>Mo <a class="link-action" href="test-db.php">test-db.php</a> de xem test ket noi rieng.</p>';
    $debug = get_class($e) . "\n" . $e->getMessage() . "\n" . $e->getFile() . ':' . $e->getLine();
    echo '<pre style="white-space:pre-wrap">' . htmlspecialchars($debug, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';
    echo '</section>';
}
