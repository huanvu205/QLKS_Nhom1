<?php
/** @var string $todayRevenue */
/** @var int $pendingBookings */
/** @var array<string, int|string|null> $stats */
/** @var array<int, array{SoPhong:string, TrangThai:string}> $rooms */
?>
<section class="metric-grid">
    <article class="metric"><span>Tổng doanh thu hôm nay</span><strong id="dashboard-today-revenue"><?= htmlspecialchars($todayRevenue) ?></strong></article>
    <article class="metric"><span>Phòng trống</span><strong id="dashboard-room-empty"><?= (int) ($stats['PhongTrong'] ?? 0) ?></strong></article>
    <article class="metric"><span>Phòng đang ở</span><strong id="dashboard-room-occupied"><?= (int) ($stats['PhongDangO'] ?? 0) ?></strong></article>
    <article class="metric"><span>Booking chờ xử lý</span><strong id="dashboard-pending-bookings"><?= (int) $pendingBookings ?></strong></article>
</section>

<section class="dashboard-grid">
    <div class="panel">
        <div class="panel-head"><h2>Doanh thu trong 7 ngày gần đây</h2></div>
        <div class="chart">
            <span style="height:42%"></span><span style="height:58%"></span><span style="height:35%"></span>
            <span style="height:70%"></span><span style="height:62%"></span><span style="height:85%"></span><span style="height:74%"></span>
        </div>
    </div>
    <div class="panel">
        <div class="panel-head"><h2>Trạng thái phòng</h2></div>
        <div class="room-board">
            <?php foreach ($rooms as $room): ?>
                <?php
                $stateClass = [
                    'Trống' => 'available',
                    'Đang ở' => 'occupied',
                    'Đã đặt' => 'booked',
                    'Dọn dẹp' => 'cleaning',
                    'Bảo trì' => 'maintenance',
                ][$room['TrangThai']] ?? 'other';
                ?>
                <span class="room-tile <?= htmlspecialchars($stateClass) ?>">
                    <?= htmlspecialchars($room['SoPhong']) ?><small><?= htmlspecialchars($room['TrangThai']) ?></small>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
</section>
