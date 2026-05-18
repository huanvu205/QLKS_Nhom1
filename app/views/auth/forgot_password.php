<main class="login-screen">
    <section class="login-panel refined-login">
        <div class="login-image">
            <img src="public/assets/hotel-login.svg" alt="Hotel">
        </div>
        <form class="login-card" method="post" action="index.php?page=forgot-password">
            <div class="login-logo">H</div>
            <h1>Quên mật khẩu</h1>
            <p>Nhập tài khoản và email để nhận mật khẩu tạm thời.</p>
            <?php if (!empty($message)): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>
            <label>
                <span>Tên đăng nhập</span>
                <input name="TenDangNhap" type="text" required>
            </label>
            <label>
                <span>Email nhận mật khẩu</span>
                <input name="Email" type="email" required>
            </label>
            <div class="login-actions">
                <button class="primary-button" type="submit">Gửi mật khẩu</button>
                <a class="ghost-button" href="index.php?page=login">Quay lại</a>
            </div>
        </form>
    </section>
</main>
