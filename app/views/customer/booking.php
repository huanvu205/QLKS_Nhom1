<?php
/** @var array<int,array<string,mixed>> $rooms */
?>
<section class="customer-booking-layout">
    <form class="panel data-form booking-request-card" method="post">
        <div class="panel-head">
            <div>
                <span class="customer-kicker">Yêu cầu đặt phòng</span>
                <h2>Hoàn tất thông tin lưu trú</h2>
                <p>Booking sẽ ở trạng thái chờ xác nhận để lễ tân kiểm tra và liên hệ lại.</p>
            </div>
        </div>
        <?php if (!empty($error)): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <div class="field-grid">
            <label><span>Ngày nhận phòng</span><input name="NgayNhan" type="datetime-local" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime((string)$from))) ?>" required></label>
            <label><span>Ngày trả phòng</span><input name="NgayTra" type="datetime-local" value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime((string)$to))) ?>" required></label>
            <label class="span-2">
                <span>Chọn phòng còn khả dụng</span>
                <select name="MaPhong" id="customer-room-select" required>
                    <?php foreach ($rooms as $room): ?>
                        <option
                            value="<?= htmlspecialchars($room['MaPhong']) ?>"
                            data-room="<?= htmlspecialchars($room['SoPhong']) ?>"
                            data-type="<?= htmlspecialchars($room['TenLP']) ?>"
                            data-status="<?= htmlspecialchars($room['TrangThai']) ?>"
                            data-price="<?= htmlspecialchars(number_format((float) $room['GiaPhong'], 0, ',', '.') . 'đ/đêm') ?>"
                            <?= $roomCode === $room['MaPhong'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($room['SoPhong'] . ' - ' . $room['TenLP'] . ' - ' . $room['TrangThai'] . ' - ' . number_format((float) $room['GiaPhong'], 0, ',', '.') . 'đ/đêm') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label><span>Số người</span><input name="SoNguoi" type="number" value="1" min="1" required></label>
            <label><span>Hotline hỗ trợ</span><input value="<?= htmlspecialchars($hotline) ?>" readonly></label>
            <label class="span-2"><span>Ghi chú</span><textarea name="GhiChu" rows="3" placeholder="Nhu cầu thêm giường, giờ đến dự kiến..."></textarea></label>
        </div>
        <div class="button-row">
            <button class="primary-button" type="submit" <?= !$rooms ? 'disabled' : '' ?>>Gửi yêu cầu đặt phòng</button>
            <a class="ghost-button" href="index.php?page=customer-rooms&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>">Xem phòng khác</a>
        </div>
    </form>

    <section class="panel booking-preview-card">
        <div class="panel-head">
            <div>
                <span class="customer-kicker">Phòng đã chọn</span>
                <h2>Thông tin phòng</h2>
            </div>
        </div>
        <?php if ($selectedRoom): ?>
            <?php $isCleaning = ($selectedRoom['TrangThai'] ?? '') === 'Dọn dẹp'; ?>
            <div id="selected-room-banner" class="selected-room-banner <?= $isCleaning ? 'is-cleaning' : '' ?>">
                <span id="selected-room-status" class="status-badge <?= $isCleaning ? 'status-cleaning' : 'status-available' ?>"><?= htmlspecialchars($selectedRoom['TrangThai']) ?></span>
                <strong>Phòng <?= htmlspecialchars($selectedRoom['SoPhong']) ?></strong>
                <small><?= htmlspecialchars($selectedRoom['TenLP']) ?></small>
            </div>
            <div class="detail-grid">
                <div class="detail-row"><span class="detail-icon">#</span><strong>Số phòng</strong><span><?= htmlspecialchars($selectedRoom['SoPhong']) ?></span></div>
                <div class="detail-row"><span class="detail-icon">P</span><strong>Loại phòng</strong><span><?= htmlspecialchars($selectedRoom['TenLP']) ?></span></div>
                <div class="detail-row"><span class="detail-icon">$</span><strong>Giá phòng</strong><span><?= number_format((float) $selectedRoom['GiaPhong'], 0, ',', '.') ?>đ/đêm</span></div>
                <div class="detail-row"><span class="detail-icon">+</span><strong>Đặc quyền</strong><span>Ưu tiên xác nhận booking, nhận thông báo qua email, hỗ trợ qua hotline <?= htmlspecialchars($hotline) ?>.</span></div>
            </div>
        <?php else: ?>
            <div class="empty-detail">Chọn một phòng trống hoặc đang dọn dẹp để xem chi tiết.</div>
        <?php endif; ?>
    </section>
</section>
<script>
(function () {
    const select = document.getElementById('customer-room-select');
    const banner = document.getElementById('selected-room-banner');
    const status = document.getElementById('selected-room-status');

    if (!select || !banner || !status) {
        return;
    }

    const title = banner.querySelector('strong');
    const type = banner.querySelector('small');
    const detailValues = document.querySelectorAll('.booking-preview-card .detail-row > span:last-child');

    function syncPreview() {
        const option = select.options[select.selectedIndex];
        if (!option) {
            return;
        }

        const roomNumber = option.dataset.room || '';
        const roomType = option.dataset.type || '';
        const roomStatus = option.dataset.status || '';
        const roomPrice = option.dataset.price || '';
        const isCleaning = roomStatus !== 'Trống';

        status.textContent = roomStatus;
        status.className = 'status-badge ' + (isCleaning ? 'status-cleaning' : 'status-available');
        banner.className = 'selected-room-banner' + (isCleaning ? ' is-cleaning' : '');

        if (title) {
            title.textContent = 'Phòng ' + roomNumber;
        }
        if (type) {
            type.textContent = roomType;
        }
        if (detailValues[0]) {
            detailValues[0].textContent = roomNumber;
        }
        if (detailValues[1]) {
            detailValues[1].textContent = roomType;
        }
        if (detailValues[2]) {
            detailValues[2].textContent = roomPrice;
        }
    }

    select.addEventListener('change', syncPreview);
    syncPreview();
})();
</script>
