<html><head><meta charset="utf-8"><style>table{border-collapse:collapse;width:100%}td,th{border:1px solid #333;padding:8px}</style></head><body>
<h1>HÓA ĐƠN THANH TOÁN</h1>
<p>Mã hóa đơn: <b><?= htmlspecialchars($invoice['MaHD']) ?></b></p>
<p>Khách hàng: <?= htmlspecialchars($invoice['HoTen']) ?> - SĐT: <?= htmlspecialchars($invoice['SDT']) ?> - CCCD: <?= htmlspecialchars($invoice['CCCD']) ?></p>
<p>Booking: <?= htmlspecialchars($invoice['MaBooking']) ?> - Phòng: <?= htmlspecialchars($invoice['SoPhong']) ?></p>
<table><thead><tr><th>Nội dung</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead><tbody>
<?php foreach ($details as $d): ?><tr><td><?= htmlspecialchars($d['Ten']) ?></td><td><?= htmlspecialchars((string) $d['SoLuong']) ?></td><td><?= number_format($d['DonGia'], 0, ',', '.') ?>đ</td><td><?= number_format($d['ThanhTien'], 0, ',', '.') ?>đ</td></tr><?php endforeach; ?>
</tbody></table>
<h2>Tổng tiền: <?= number_format($invoice['TongTien'], 0, ',', '.') ?>đ</h2>
</body></html>
