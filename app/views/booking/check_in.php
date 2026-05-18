<?php
/** @var string $q */
/** @var array<int,array<string,mixed>> $rows */
?>
<section class="panel">
    <form class="search-row" method="get"><input type="hidden" name="page" value="check-in"><input name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Nhập mã booking"><button class="primary-button">Tìm kiếm</button></form>
    <div class="table-wrap"><table><thead><tr><th>Mã booking</th><th>Khách hàng</th><th>CCCD</th><th>Phòng</th><th>Ngày nhận</th><th>Ngày trả</th><th>Số người</th><th>Trạng thái</th><th></th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?><tr><td><?= htmlspecialchars($r['MaBooking']) ?></td><td><?= htmlspecialchars($r['HoTen']) ?></td><td><?= htmlspecialchars($r['CCCD']) ?></td><td><?= htmlspecialchars($r['SoPhong']) ?></td><td><?= htmlspecialchars((string) $r['NgayNhan']) ?></td><td><?= htmlspecialchars((string) $r['NgayTra']) ?></td><td><?= htmlspecialchars((string) $r['SoNguoi']) ?></td><td><?= htmlspecialchars($r['TrangThai']) ?></td><td><form method="post"><input type="hidden" name="MaBooking" value="<?= htmlspecialchars($r['MaBooking']) ?>"><button class="primary-button">Xác nhận nhận phòng</button></form></td></tr><?php endforeach; ?>
    </tbody></table></div>
</section>
