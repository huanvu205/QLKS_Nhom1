<main class="login-screen register-screen">
    <section class="login-panel refined-login register-panel">
        <div class="login-image register-image">
            <img src="public/assets/hotel-login.svg" alt="Hotel">
            <div class="register-benefits">
                <span>Đặt phòng nhanh</span>
                <span>Ưu tiên xác nhận</span>
                <span>Hotline <?= htmlspecialchars($hotline) ?></span>
            </div>
        </div>
        <form class="login-card register-card" method="post" action="index.php?page=register">
            <div class="login-logo">H</div>
            <h1>Đăng ký khách hàng</h1>
            <p>Tạo tài khoản để xem phòng trống, phòng đang dọn dẹp và gửi yêu cầu đặt phòng trực tuyến.</p>
            <?php if (!empty($error)): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <div class="register-field-grid">
                <label>
                    <span>Tên đăng nhập</span>
                    <input name="TenDangNhap" type="text" required>
                </label>
                <label>
                    <span>Họ tên</span>
                    <input name="HoTen" type="text" required>
                </label>
                <label>
                    <span>Mật khẩu</span>
                    <input name="MatKhau" type="password" required>
                </label>
                <label>
                    <span>Nhập lại mật khẩu</span>
                    <input name="NhapLaiMatKhau" type="password" required>
                </label>
                <label>
                    <span>Số điện thoại</span>
                    <input name="SDT" type="text" required>
                </label>
                <label>
                    <span>Email</span>
                    <input name="Email" type="email" required>
                </label>
                <label>
                    <span>CCCD</span>
                    <input name="CCCD" type="text">
                </label>
                <label>
                    <span>Địa chỉ</span>
                    <input name="DiaChi" type="text">
                </label>
            </div>
            <div class="login-actions">
                <button class="primary-button" type="submit">Đăng ký</button>
                <a class="ghost-button" href="index.php?page=login">Đăng nhập</a>
            </div>
            <p class="login-help">Cần hỗ trợ? Gọi hotline <?= htmlspecialchars($hotline) ?>.</p>
        </form>
    </section>
</main>
