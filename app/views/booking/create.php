<?php
/** @var string $nextCode */
/** @var array<int,array<string,mixed>> $customers */
/** @var array<int,array<string,mixed>> $rooms */
/** @var array<int,array<string,mixed>> $rows */
?>
<section class="content-grid">
    <form class="panel data-form" method="post">
        <div class="panel-head"><div><h2>Thông tin đặt phòng</h2><p>Chỉ hiển thị phòng đang trống.</p></div></div>
        <div class="field-grid">
            <label><span>Mã booking</span><input name="MaBooking" value="<?= htmlspecialchars($nextCode) ?>" required></label>
            <label><span>Khách hàng</span><select name="MaKH"><?php foreach ($customers as $c): ?><option value="<?= htmlspecialchars($c['MaKH']) ?>"><?= htmlspecialchars($c['HoTen']) ?></option><?php endforeach; ?></select></label>
            <label><span>Email khách hàng</span><input name="Email" type="email" placeholder="khachhang@gmail.com" required></label>
            <label><span>Ngày nhận phòng</span><input name="NgayNhan" type="date" value="<?= date('Y-m-d') ?>" required></label>
            <label><span>Ngày trả phòng</span><input name="NgayTra" type="date" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required></label>
            <label><span>Chọn phòng</span><select name="MaPhong"><?php foreach ($rooms as $r): ?><option value="<?= htmlspecialchars($r['MaPhong']) ?>"><?= htmlspecialchars($r['SoPhong'] . ' - ' . $r['TenLP'] . ' - ' . number_format($r['GiaPhong'], 0, ',', '.') . 'đ') ?></option><?php endforeach; ?></select></label>
            <label><span>Số người</span><input name="SoNguoi" type="number" value="1" min="1"></label>
            <label class="span-2"><span>Ghi chú</span><textarea name="GhiChu" rows="3"></textarea></label>
        </div>
        <div class="button-row"><button class="primary-button" type="submit">Đặt phòng</button><a class="ghost-button" href="index.php?page=booking">Reset</a></div>
    </form>
    <section class="panel table-panel">
        <div class="panel-head"><h2>Danh sách phòng trống</h2></div>
        <div class="table-wrap"><table><thead><tr><th>Mã booking</th><th>Khách hàng</th><th>Phòng</th><th>Ngày nhận</th><th>Ngày trả</th><th>Trạng thái</th></tr></thead><tbody>
        <?php foreach ($rows as $row): ?><tr><td><?= htmlspecialchars($row['MaBooking']) ?></td><td><?= htmlspecialchars($row['HoTen']) ?></td><td><?= htmlspecialchars($row['SoPhong']) ?></td><td><?= htmlspecialchars((string) $row['NgayNhan']) ?></td><td><?= htmlspecialchars((string) $row['NgayTra']) ?></td><td><?= htmlspecialchars($row['TrangThai']) ?></td></tr><?php endforeach; ?>
        </tbody></table></div>
    </section>
</section>
