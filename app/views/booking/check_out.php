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
            <form method="post"><input type="hidden" name="MaBooking" value="<?= htmlspecialchars($booking['MaBooking']) ?>"><button class="primary-button">Thanh toán và trả phòng</button></form>
        <?php endif; ?>
    </div>
    <section class="panel table-panel"><div class="panel-head"><h2>Chi tiết thanh toán</h2></div><div class="table-wrap"><table><thead><tr><th>Nội dung</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead><tbody>
    <?php foreach ($details as $d): ?><tr><td><?= htmlspecialchars($d['Ten']) ?></td><td><?= htmlspecialchars((string) $d['SoLuong']) ?></td><td><?= number_format($d['DonGia'], 0, ',', '.') ?>đ</td><td><?= number_format($d['ThanhTien'], 0, ',', '.') ?>đ</td></tr><?php endforeach; ?>
    </tbody></table></div></section>
</section>
