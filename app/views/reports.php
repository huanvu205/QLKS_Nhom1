<section class="content-grid">
    <form class="panel data-form" method="get">
        <input type="hidden" name="page" value="reports">
        <div class="panel-head"><div><h2>Bộ lọc báo cáo</h2><p>Thống kê doanh thu và tình trạng phòng.</p></div></div>
        <div class="field-grid">
            <label><span>Từ ngày</span><input name="from" type="date" value="<?= htmlspecialchars($from) ?>"></label>
            <label><span>Đến ngày</span><input name="to" type="date" value="<?= htmlspecialchars($to) ?>"></label>
        </div>
        <div class="button-row"><button class="primary-button">Thống kê</button><a class="ghost-button" href="index.php?page=reports-excel&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>">Xuất Excel</a></div>
        <div class="report-cards">
            <span>Doanh thu <b><?= number_format($summary['DoanhThu'] ?? 0, 0, ',', '.') ?>đ</b></span>
            <span>Hóa đơn <b><?= (int) ($summary['SoHoaDon'] ?? 0) ?></b></span>
            <span>Phòng trống <b><?= (int) ($summary['PhongTrong'] ?? 0) ?></b></span>
            <span>Phòng đang ở <b><?= (int) ($summary['PhongDangO'] ?? 0) ?></b></span>
        </div>
    </form>
    <section class="panel table-panel"><div class="panel-head"><h2>Dữ liệu báo cáo</h2></div><div class="table-wrap"><table><thead><tr><th>Ngày</th><th>Số hóa đơn</th><th>Doanh thu</th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?><tr><td><?= htmlspecialchars((string) $r['Ngay']) ?></td><td><?= htmlspecialchars((string) $r['SoHoaDon']) ?></td><td><?= number_format($r['DoanhThu'], 0, ',', '.') ?>đ</td></tr><?php endforeach; ?>
    </tbody></table></div></section>
</section>
