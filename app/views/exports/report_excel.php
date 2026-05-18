<table border="1">
    <tr><th colspan="3">Báo cáo thống kê từ <?= htmlspecialchars($from) ?> đến <?= htmlspecialchars($to) ?></th></tr>
    <tr><th>Ngày</th><th>Số hóa đơn</th><th>Doanh thu</th></tr>
    <?php foreach ($rows as $r): ?>
        <tr><td><?= htmlspecialchars((string) $r['Ngay']) ?></td><td><?= htmlspecialchars((string) $r['SoHoaDon']) ?></td><td><?= htmlspecialchars((string) $r['DoanhThu']) ?></td></tr>
    <?php endforeach; ?>
    <tr><th colspan="2">Tổng doanh thu</th><th><?= htmlspecialchars((string) ($summary['DoanhThu'] ?? 0)) ?></th></tr>
</table>
