<?php

require_once __DIR__ . '/app/core/Mailer.php';

header('Content-Type: text/html; charset=utf-8');

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to = trim($_POST['email'] ?? '');
    $ok = Mailer::send(
        $to,
        'Test email HOTEL',
        '<h2>HOTEL test email</h2><p>Nếu bạn nhận được email này thì cấu hình SMTP đã hoạt động.</p>'
    );
    $message = $ok ? 'Đã gửi email test thành công.' : 'Gửi email thất bại. Kiểm tra storage/mail_outbox để xem lỗi SMTP.';
}
?>
<!doctype html>
<meta charset="utf-8">
<link rel="stylesheet" href="public/assets/app.css">
<section class="panel" style="margin:24px;max-width:620px">
    <h1>Test gửi email thật</h1>
    <p>Cấu hình SMTP trong <b>app/config/mail.php</b>, sau đó nhập email nhận test.</p>
    <?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <form method="post" class="data-form">
        <label>
            <span>Email nhận</span>
            <input name="email" type="email" required placeholder="khachhang@gmail.com">
        </label>
        <div class="button-row">
            <button class="primary-button">Gửi test</button>
            <a class="ghost-button" href="index.php?page=login">Về trang đăng nhập</a>
        </div>
    </form>
</section>
