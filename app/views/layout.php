<?php
/** @var string $viewFile */
$title = $title ?? 'QLKS';
$active = $active ?? 'dashboard';
$user = $_SESSION['user'] ?? null;
$menu = [
    'dashboard' => ['Màn hình chính', 'index.php?page=dashboard'],
    'password' => ['Đổi mật khẩu', 'index.php?page=password'],
    'room-types' => ['Quản lý loại phòng', 'index.php?page=room-types'],
    'rooms' => ['Quản lý phòng', 'index.php?page=rooms'],
    'customers' => ['Quản lý khách hàng', 'index.php?page=customers'],
    'booking' => ['Đặt phòng', 'index.php?page=booking'],
    'booking-list' => ['Danh sách booking', 'index.php?page=booking-list'],
    'check-in' => ['Nhận phòng', 'index.php?page=check-in'],
    'check-out' => ['Trả phòng', 'index.php?page=check-out'],
    'services' => ['Quản lý dịch vụ', 'index.php?page=services'],
    'service-usage' => ['Sử dụng dịch vụ', 'index.php?page=service-usage'],
    'invoices' => ['Quản lý hóa đơn', 'index.php?page=invoices'],
    'reports' => ['Báo cáo - thống kê', 'index.php?page=reports'],
    'accounts' => ['Quản lý tài khoản', 'index.php?page=accounts'],
];
if (($user['VaiTro'] ?? '') === 'Khách hàng') {
    $menu = [
        'dashboard' => ['Màn hình chính', 'index.php?page=dashboard'],
        'password' => ['Đổi mật khẩu', 'index.php?page=password'],
    ];
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?> | QLKS</title>
    <link rel="stylesheet" href="public/assets/app.css">
</head>
<body>
<?php if (($active ?? '') === 'login'): ?>
    <?php require $viewFile; ?>
<?php else: ?>
<div class="app-shell">
    <aside class="sidebar">
        <a class="brand" href="index.php?page=dashboard">
            <span class="brand-mark">H</span>
            <span><strong>HOTEL</strong><small>Quản lý khách sạn</small></span>
        </a>
        <nav class="nav">
            <?php foreach ($menu as $menuKey => [$label, $href]): ?>
                <a class="<?= $active === $menuKey ? 'is-active' : '' ?>" href="<?= $href ?>">
                    <?= htmlspecialchars($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </aside>
    <main class="main">
        <header class="topbar">
            <div>
                <p class="eyebrow"><?= htmlspecialchars($user['VaiTro'] ?? '') ?></p>
                <h1><?= htmlspecialchars($title) ?></h1>
            </div>
            <div class="top-actions">
                <span><?= htmlspecialchars($user['HoTen'] ?? '') ?></span>
                <a class="ghost-button" href="index.php?page=logout">Đăng xuất</a>
            </div>
        </header>
        <?php require $viewFile; ?>
    </main>
</div>
<?php endif; ?>
    <script src="public/assets/app.js"></script>
</html>
