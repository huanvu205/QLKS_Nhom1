<section class="panel">
    <form class="search-row" method="get"><input type="hidden" name="page" value="invoices"><input name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="<?= htmlspecialchars($searchPlaceholder) ?>"><button class="primary-button">Tìm kiếm</button></form>
    <div class="table-wrap"><table><thead><tr><th>Mã hóa đơn</th><th>Booking</th><th>Khách hàng</th><th>Ngày lập</th><th>Tổng tiền</th><th>Trạng thái</th><th>Xuất file</th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?><tr><td><?= htmlspecialchars($r['MaHD']) ?></td><td><?= htmlspecialchars($r['MaBooking']) ?></td><td><?= htmlspecialchars($r['HoTen']) ?></td><td><?= htmlspecialchars((string) $r['NgayLap']) ?></td><td><?= number_format($r['TongTien'], 0, ',', '.') ?>đ</td><td><?= htmlspecialchars($r['TrangThai']) ?></td><td><a class="link-action" href="index.php?page=invoice-word&id=<?= urlencode($r['MaHD']) ?>">Word</a> <a class="link-action" href="index.php?page=invoice-excel&id=<?= urlencode($r['MaHD']) ?>">Excel</a></td></tr><?php endforeach; ?>
    </tbody></table></div>
</section>
