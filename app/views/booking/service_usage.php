<?php
/** @var array<int,array<string,mixed>> $bookings */
/** @var array<int,array<string,mixed>> $services */
/** @var array<int,array<string,mixed>> $rows */
?>
<section class="content-grid">
    <form class="panel data-form" method="post">
        <div class="panel-head"><h2>Thêm dịch vụ sử dụng</h2></div>
        <div class="field-grid">
            <label><span>Booking</span><select name="MaBooking"><?php foreach ($bookings as $b): ?><option><?= htmlspecialchars($b['MaBooking']) ?></option><?php endforeach; ?></select></label>
            <label><span>Dịch vụ</span><select name="MaDV"><?php foreach ($services as $s): ?><option value="<?= htmlspecialchars($s['MaDV']) ?>"><?= htmlspecialchars($s['TenDV']) ?></option><?php endforeach; ?></select></label>
            <label><span>Số lượng</span><input name="SoLuong" type="number" value="1" min="1"></label>
            <label><span>Ngày sử dụng</span><input name="NgaySD" type="date" value="<?= date('Y-m-d') ?>"></label>
        </div>
        <div class="button-row"><button class="primary-button">Thêm vào danh sách</button></div>
    </form>
    <section class="panel table-panel"><div class="panel-head"><h2>Danh sách dịch vụ sử dụng</h2></div><div class="table-wrap"><table><thead><tr><th>Booking</th><th>Dịch vụ</th><th>Số lượng</th><th>Đơn giá</th><th>Ngày</th><th>Thành tiền</th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?><tr><td><?= htmlspecialchars($r['MaBooking']) ?></td><td><?= htmlspecialchars($r['TenDV']) ?></td><td><?= htmlspecialchars((string) $r['SoLuong']) ?></td><td><?= number_format($r['DonGia'], 0, ',', '.') ?>đ</td><td><?= htmlspecialchars((string) $r['NgaySD']) ?></td><td><?= number_format($r['ThanhTien'], 0, ',', '.') ?>đ</td></tr><?php endforeach; ?>
    </tbody></table></div></section>
</section>
