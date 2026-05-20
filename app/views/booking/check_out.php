<?php
/** @var string $q */
/** @var array<string,mixed>|null $booking */
/** @var array<int,array<string,mixed>> $details */
?>
<section class="content-grid">
    <div class="panel">
        <form class="search-row" method="get"><input type="hidden" name="page" value="check-out"><input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Nhập mã booking"><button class="primary-button">Tìm kiếm</button></form>
        <?php if ($booking): ?>
            <dl class="summary-list"><dt>Khách hàng</dt><dd><?= htmlspecialchars($booking['HoTen']) ?></dd><dt>Phòng</dt><dd><?= htmlspecialchars($booking['SoPhong']) ?></dd><dt>Tổng tiền</dt><dd><?= number_format($booking['TongTien'], 0, ',', '.') ?>đ</dd></dl>

            <form method="post" id="checkout-form">
                <input type="hidden" name="MaBooking" value="<?= htmlspecialchars($booking['MaBooking']) ?>">

                <div class="payment-methods">
                    <label class="pay-option">
                        <input type="radio" name="PhuongThuc" value="TienMat" checked>
                        <span class="pay-label">Tiền mặt</span>
                    </label>
                    <label class="pay-option">
                        <input type="radio" name="PhuongThuc" value="ChuyenKhoan">
                        <span class="pay-label">Chuyển khoản</span>
                    </label>
                    <label class="pay-option">
                        <input type="radio" name="PhuongThuc" value="The" >
                        <span class="pay-label">Quẹt thẻ</span>
                    </label>
                    <label class="pay-option">
                        <input type="radio" name="PhuongThuc" value="VNPAY">
                        <span class="pay-label">Thanh toán VNPAY</span>
                    </label>
                </div>

                <div id="payment-extra" class="payment-extra">
                    <div data-for="ChuyenKhoan" class="extra-field" style="display:none;">
                        <p><strong>HƯỚNG DẪN CHUYỂN KHOẢN</strong></p>
                        <p>Ngân hàng: <b>Vietcombank</b><br>STK: <b>0123456789</b><br>Chủ tài khoản: <b>HOTEL COMPANY</b></p>
                        <label>Ghi chú chuyển khoản (tùy chọn)<br><input type="text" name="GhiChuChuyenKhoan" placeholder="Mã booking hoặc tên khách"></label>
                    </div>
                    <div data-for="The" class="extra-field" style="display:none;">
                        <p>Quẹt thẻ tại máy POS. Ghi chú (tùy chọn):<br><input type="text" name="GhiChuThe" placeholder="Ghi chú"></p>
                    </div>
                    <div data-for="VNPAY" class="extra-field" style="display:none;">
                        <p>Bấm <button type="button" id="vnpay-btn" class="vnpay-button">Thanh toán bằng VNPAY</button> để mở cổng thanh toán (mô phỏng).</p>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="primary-button">Thanh toán và trả phòng</button>
                    <button type="button" class="secondary-button" id="cancel-checkout">Hủy</button>
                </div>
            </form>

            <form id="vnpay-form" method="post" action="index.php?page=vnpay-create" style="display:none;">
                <input type="hidden" name="MaBooking" value="<?= htmlspecialchars($booking['MaBooking']) ?>">
            </form>

            <script>
                (function(){
                    const form = document.getElementById('checkout-form');
                    const radios = form.querySelectorAll('input[name="PhuongThuc"]');
                    const extras = document.querySelectorAll('.payment-extra .extra-field');

                    function showExtra(value){
                        extras.forEach(el => el.style.display = el.getAttribute('data-for') === value ? '' : 'none');
                    }

                    radios.forEach(r => r.addEventListener('change', e => showExtra(e.target.value)));

                    // initial
                    const checked = form.querySelector('input[name="PhuongThuc"]:checked');
                    if (checked) showExtra(checked.value);

                    // VNPAY button: submit hidden VNPay create form (server will redirect to VNPay)
                    const vnpayBtn = document.getElementById('vnpay-btn');
                    if (vnpayBtn) vnpayBtn.addEventListener('click', function(){
                        document.getElementById('vnpay-form').submit();
                    });

                    // intercept submit: if VNPAY selected, submit to VNPay create endpoint
                    form.addEventListener('submit', function(e){
                        const selected = form.querySelector('input[name="PhuongThuc"]:checked');
                        if (selected && selected.value === 'VNPAY') {
                            e.preventDefault();
                            document.getElementById('vnpay-form').submit();
                        }
                    });

                    document.getElementById('cancel-checkout').addEventListener('click', function(){
                        window.location.href = 'index.php?page=invoices';
                    });
                })();
            </script>
        <?php endif; ?>
    </div>
    <section class="panel table-panel"><div class="panel-head"><h2>Chi tiết thanh toán</h2></div><div class="table-wrap"><table><thead><tr><th>Nội dung</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead><tbody>
    <?php foreach ($details as $d): ?><tr><td><?= htmlspecialchars($d['Ten']) ?></td><td><?= htmlspecialchars((string) $d['SoLuong']) ?></td><td><?= number_format($d['DonGia'], 0, ',', '.') ?>đ</td><td><?= number_format($d['ThanhTien'], 0, ',', '.') ?>đ</td></tr><?php endforeach; ?>
    </tbody></table></div></section>
</section>
