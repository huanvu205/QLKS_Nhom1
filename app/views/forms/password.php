<section class="single-panel">
    <form class="panel data-form" method="post">
        <div class="panel-head"><div><h2>Đổi mật khẩu</h2><p>Cập nhật mật khẩu tài khoản đang đăng nhập.</p></div></div>
        <?php if (!empty($message)): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>
        <div class="field-grid one-column">
            <label><span>Mật khẩu hiện tại</span><input name="MatKhauCu" type="password" required></label>
            <label><span>Mật khẩu mới</span><input name="MatKhauMoi" type="password" required></label>
            <label><span>Xác nhận mật khẩu mới</span><input name="NhapLaiMatKhau" type="password" required></label>
        </div>
        <div class="button-row">
            <button class="primary-button" type="submit">Lưu thay đổi</button>
            <a class="ghost-button" href="index.php?page=dashboard">Hủy bỏ</a>
        </div>
    </form>
</section>
