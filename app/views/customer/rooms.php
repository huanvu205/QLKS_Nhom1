<?php
/** @var array<int,array<string,mixed>> $rooms */
$cleaningCount = 0;
foreach ($rooms as $room) {
    if (($room['TrangThai'] ?? '') === 'Dọn dẹp') {
        $cleaningCount++;
    }
}
?>
<section class="customer-hero panel">
    <div class="customer-hero-copy">
        <span class="customer-kicker">Đặt phòng trực tuyến</span>
        <h2>Chọn phòng phù hợp cho kỳ nghỉ của bạn</h2>
        <p>Chỉ hiển thị phòng đang trống hoặc đang dọn dẹp, không hiển thị phòng đang ở, đã đặt hoặc bảo trì.</p>
        <div class="customer-hero-actions">
            <a class="primary-button" href="index.php?page=customer-booking&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>">Đặt phòng ngay</a>
            <a class="ghost-button hotline-button" href="tel:<?= htmlspecialchars($hotline) ?>">Hotline <?= htmlspecialchars($hotline) ?></a>
        </div>
    </div>
    <form class="availability-card" method="get">
        <input type="hidden" name="page" value="customer-rooms">
        <label><span>Ngày nhận</span><input name="from" type="datetime-local" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime((string)$from))) ?>" required></label>
        <label><span>Ngày trả</span><input name="to" type="datetime-local" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime((string)$to))) ?>" required></label>
        <button class="primary-button full" type="submit">Tìm phòng trống</button>
        <div class="availability-summary">
            <strong><?= count($rooms) ?></strong>
            <span>phòng có thể đặt · <?= $cleaningCount ?> phòng đang dọn dẹp</span>
        </div>
    </form>
</section>

<section class="customer-room-grid">
    <?php foreach ($rooms as $room): ?>
        <?php
            $isCleaning = ($room['TrangThai'] ?? '') === 'Dọn dẹp';
            $badgeClass = $isCleaning ? 'status-cleaning' : 'status-available';
            $badgeText = $isCleaning ? 'Đang dọn dẹp' : 'Trống';
        ?>
        <article class="panel room-card <?= $isCleaning ? 'room-card-cleaning' : '' ?>">
            <div class="room-card-top">
                <span class="status-badge <?= $badgeClass ?>"><?= htmlspecialchars($badgeText) ?></span>
                <span class="room-floor">Tầng <?= htmlspecialchars((string) $room['Tang']) ?></span>
            </div>
            <div>
                <h2>Phòng <?= htmlspecialchars($room['SoPhong']) ?></h2>
                <p><?= htmlspecialchars($room['TenLP']) ?></p>
            </div>
            <dl class="room-facts">
                <div><dt>Sức chứa</dt><dd><?= htmlspecialchars((string) $room['SucChua']) ?> người</dd></div>
                <div><dt>Giá</dt><dd><?= number_format((float) $room['GiaPhong'], 0, ',', '.') ?>đ/đêm</dd></div>
            </dl>
            <?php if (!empty($room['MoTa'])): ?><p class="muted-text"><?= htmlspecialchars($room['MoTa']) ?></p><?php endif; ?>
            <a class="primary-button full" href="index.php?page=customer-booking&room=<?= urlencode($room['MaPhong']) ?>&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>">Đặt phòng này</a>
        </article>
    <?php endforeach; ?>
    <?php if (!$rooms): ?>
        <section class="panel empty-state">
            <h2>Chưa có phòng phù hợp</h2>
            <p>Không có phòng trống hoặc đang dọn dẹp trong khoảng ngày này. Vui lòng đổi ngày hoặc gọi <?= htmlspecialchars($hotline) ?> để được hỗ trợ.</p>
        </section>
    <?php endif; ?>
</section>
