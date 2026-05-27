<?php
/** @var array<int,array<string,mixed>> $rows */
?>
<section class="panel table-panel">
    <div class="panel-head">
        <div>
            <h2>Lịch sử đặt phòng</h2>
            <p>Theo dõi các booking được tạo từ tài khoản khách hàng.</p>
        </div>
        <a class="ghost-button" href="tel:<?= htmlspecialchars($hotline) ?>">Hotline <?= htmlspecialchars($hotline) ?></a>
    </div>
    <?php if (!empty($success)): ?><div class="alert alert-success">Đã gửi yêu cầu đặt phòng mã <?= htmlspecialchars($success) ?>. Lễ tân sẽ xác nhận sớm.</div><?php endif; ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Mã booking</th>
                    <th>Phòng</th>
                    <th>Loại phòng</th>
                    <th>Ngày nhận</th>
                    <th>Ngày trả</th>
                    <th>Số người</th>
                    <th>Trạng thái</th>
                    <th>Ghi chú</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['MaBooking']) ?></td>
                        <td><?= htmlspecialchars($row['SoPhong']) ?></td>
                        <td><?= htmlspecialchars($row['TenLP']) ?></td>
                        <td><?= htmlspecialchars((string) $row['NgayNhan']) ?></td>
                        <td><?= htmlspecialchars((string) $row['NgayTra']) ?></td>
                        <td><?= htmlspecialchars((string) $row['SoNguoi']) ?></td>
                        <td><span class="status-badge status-booked"><?= htmlspecialchars($row['TrangThai']) ?></span></td>
                        <td><?= htmlspecialchars((string) $row['GhiChu']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr><td colspan="8">Bạn chưa có booking nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
