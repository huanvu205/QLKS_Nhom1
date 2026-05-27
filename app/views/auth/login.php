<main class="login-screen">
    <section class="login-panel refined-login">
        <div class="login-image">
            <img src="public/assets/hotel-login.svg" alt="Hotel">
        </div>
        <form class="login-card" method="post" action="index.php?page=login">
            <div class="login-logo">H</div>
            <h1>HOTEL</h1>
            <p>Hệ thống quản lý khách sạn</p>
            <?php if (!empty($error)): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <label>
                <span>Tên đăng nhập</span>
                <input name="TenDangNhap" type="text" value="admin" required>
            </label>
            <label>
                <span>Mật khẩu</span>
                <input name="MatKhau" type="password" value="admin123" required>
            </label>
            <div class="login-options">
                <label class="checkbox">
                    <input type="checkbox" checked>
                    <span>Ghi nhớ đăng nhập</span>
                </label>
                <a href="index.php?page=forgot-password">Quên mật khẩu?</a>
            </div>
            <div class="login-actions">
                <button class="primary-button" type="submit">Đăng nhập</button>
                <a class="ghost-button" href="index.php?page=register">Đăng ký</a>
            </div>
            <p class="login-help">Khách hàng cần hỗ trợ đặt phòng vui lòng gọi hotline 0336120405.</p>
        </form>
    </section>
</main>
