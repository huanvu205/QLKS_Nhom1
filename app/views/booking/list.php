<?php
/** @var string $searchPlaceholder */
/** @var array<int,array<string,mixed>> $rows */
/** @var array<string,mixed>|null $edit */
/** @var array<int,array<string,mixed>> $rooms */
/** @var array<int,array<string,mixed>> $customers */
/** @var string $nextCode */
$statusClassMap = [
    'Chờ xác nhận' => 'pending',
    'Đã đặt' => 'booked',
    'Đã nhận phòng' => 'checked-in',
    'Đã trả phòng' => 'checked-out',
    'Đã hủy' => 'cancelled',
];
?>
<section class="content-grid booking-page">
    <div class="booking-form-grid">
        <form id="booking-create-form" class="panel data-form" method="post" action="index.php?page=booking-list">
            <input type="hidden" name="ajax" value="0">
        <div class="panel-head"><div><h2>Thêm booking</h2><p>Thêm booking mới và hiển thị ngay trong danh sách.</p></div></div>
        <div id="booking-notification" class="alert" style="display:none"></div>
        <div class="field-grid">
            <label><span>Mã booking</span><input name="MaBooking" value="<?= htmlspecialchars($nextCode) ?>" required></label>
            <label><span>Khách hàng</span><select name="MaKH"><?php foreach ($customers as $c): ?><option value="<?= htmlspecialchars($c['MaKH']) ?>"><?= htmlspecialchars($c['HoTen']) ?></option><?php endforeach; ?></select></label>
            <label><span>Phòng</span><select name="MaPhong"><?php foreach ($rooms as $r): ?><option value="<?= htmlspecialchars($r['MaPhong']) ?>"><?= htmlspecialchars($r['SoPhong'] . ' - ' . $r['TenLP'] . ' - ' . number_format($r['GiaPhong'], 0, ',', '.') . 'đ') ?></option><?php endforeach; ?></select></label>
            <label><span>Ngày nhận</span><input name="NgayNhan" type="datetime-local" value="<?= date('Y-m-d\T14:00') ?>" required></label>
            <label><span>Ngày trả</span><input name="NgayTra" type="datetime-local" value="<?= date('Y-m-d\T12:00', strtotime('+1 day')) ?>" required></label>
            <label><span>Số người</span><input name="SoNguoi" type="number" value="1" min="1"></label>
            <label class="span-2"><span>Ghi chú</span><textarea name="GhiChu" rows="3"></textarea></label>
        </div>
        <div class="button-row"><button class="primary-button" name="action" value="create">Thêm booking</button></div>
    </form>
        <form class="panel data-form" method="post" action="index.php?page=booking-list">
            <input type="hidden" name="OriginalMaBooking" value="<?= htmlspecialchars($edit['MaBooking'] ?? '') ?>">
            <div class="panel-head"><div><h2>Cập nhật booking</h2><p>Chọn một dòng trong bảng để sửa hoặc hủy booking.</p></div></div>
        <div class="field-grid">
            <label><span>Mã booking</span><input name="MaBooking" value="<?= htmlspecialchars($edit['MaBooking'] ?? '') ?>" readonly></label>
            <label><span>Ngày nhận</span><input name="NgayNhan" type="datetime-local" value="<?= htmlspecialchars(isset($edit['NgayNhan']) ? date('Y-m-d\TH:i', strtotime((string)$edit['NgayNhan'])) : date('Y-m-d\T14:00')) ?>" required></label>
            <label><span>Ngày trả</span><input name="NgayTra" type="datetime-local" value="<?= htmlspecialchars(isset($edit['NgayTra']) ? date('Y-m-d\TH:i', strtotime((string)$edit['NgayTra'])) : date('Y-m-d\T12:00', strtotime('+1 day'))) ?>"></label>
            <label><span>Số người</span><input name="SoNguoi" type="number" value="<?= htmlspecialchars($edit['SoNguoi'] ?? '1') ?>"></label>
            <label><span>Trạng thái</span><select name="TrangThai"><?php foreach (['Chờ xác nhận', 'Đã đặt', 'Đã nhận phòng', 'Đã trả phòng', 'Đã hủy'] as $s): ?><option <?= ($edit['TrangThai'] ?? '') === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option><?php endforeach; ?></select></label>
            <label><span>Ghi chú</span><textarea name="GhiChu" rows="3"><?= htmlspecialchars($edit['GhiChu'] ?? '') ?></textarea></label>
        </div>
        <div class="button-row"><button class="primary-button" name="action" value="update">Cập nhật</button><button class="ghost-button danger" name="action" value="delete">Hủy booking</button></div>
    </form>
    </div>
    <div class="booking-details-column">
        <section class="panel table-panel">
            <form class="search-row" method="get"><input type="hidden" name="page" value="booking-list"><input name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="<?= htmlspecialchars($searchPlaceholder) ?>"><button class="primary-button">Tìm kiếm</button></form>
            <div class="table-wrap"><table><thead><tr><th><span class="header-icon">📌</span> Mã booking</th><th><span class="header-icon">👤</span> Khách hàng</th><th><span class="header-icon">🛏️</span> Phòng</th><th><span class="header-icon">📅</span> Ngày nhận</th><th><span class="header-icon">📅</span> Ngày trả</th><th><span class="header-icon">👥</span> Số người</th><th><span class="header-icon">🏷️</span> Trạng thái</th><th></th></tr></thead><tbody id="booking-table-body">
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= htmlspecialchars($r['MaBooking']) ?></td>
                    <td><?= htmlspecialchars($r['HoTen']) ?></td>
                    <td><?= htmlspecialchars($r['SoPhong']) ?></td>
                    <td><?= htmlspecialchars((string) $r['NgayNhan']) ?></td>
                    <td><?= htmlspecialchars((string) $r['NgayTra']) ?></td>
                    <td><?= htmlspecialchars((string) $r['SoNguoi']) ?></td>
                    <td><span class="status-badge status-<?= htmlspecialchars($statusClassMap[$r['TrangThai']] ?? 'default') ?>"><?= htmlspecialchars($r['TrangThai']) ?></span></td>
                    <td><a class="link-action" href="index.php?page=booking-list&edit=<?= urlencode($r['MaBooking']) ?>">✍️ Chọn</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody></table></div>
        </section>

        <section class="panel detail-panel">
            <div class="panel-head"><div><h2>Chi tiết booking</h2><p>Xem nhanh thông tin booking đã chọn.</p></div></div>
            <?php if ($edit): ?>
                <div class="detail-grid">
                    <div class="detail-row"><span class="detail-icon">🆔</span><strong>Mã booking</strong><span><?= htmlspecialchars($edit['MaBooking']) ?></span></div>
                    <div class="detail-row"><span class="detail-icon">🧾</span><strong>Mã khách hàng</strong><span><?= htmlspecialchars($edit['MaKH']) ?></span></div>
                    <div class="detail-row"><span class="detail-icon">🏨</span><strong>Mã phòng</strong><span><?= htmlspecialchars($edit['MaPhong']) ?></span></div>
                    <div class="detail-row"><span class="detail-icon">📅</span><strong>Ngày nhận</strong><span><?= htmlspecialchars((string) $edit['NgayNhan']) ?></span></div>
                    <div class="detail-row"><span class="detail-icon">📅</span><strong>Ngày trả</strong><span><?= htmlspecialchars((string) $edit['NgayTra']) ?></span></div>
                    <div class="detail-row"><span class="detail-icon">👥</span><strong>Số người</strong><span><?= htmlspecialchars((string) $edit['SoNguoi']) ?></span></div>
                    <div class="detail-row"><span class="detail-icon">🏷️</span><strong>Trạng thái</strong><span class="status-badge status-<?= htmlspecialchars($statusClassMap[$edit['TrangThai']] ?? 'default') ?>"><?= htmlspecialchars($edit['TrangThai'] ?? 'Chưa chọn') ?></span></div>
                    <div class="detail-row detail-note"><span class="detail-icon">📝</span><strong>Ghi chú</strong><span><?= nl2br(htmlspecialchars($edit['GhiChu'] ?? 'Không có ghi chú')) ?></span></div>
                </div>
            <?php else: ?>
                <div class="empty-detail">
                    <p>Chọn một booking trong danh sách để xem chi tiết.</p>
                </div>
            <?php endif; ?>
        </section>
    </div>
    <script>
    (function () {
        const form = document.getElementById('booking-create-form');
        const body = document.getElementById('booking-table-body');
        if (!form || !body) return;

        function statusClass(status) {
            return {
                'Chờ xác nhận': 'pending',
                'Đã đặt': 'booked',
                'Đã nhận phòng': 'checked-in',
                'Đã trả phòng': 'checked-out',
                'Đã hủy': 'cancelled',
            }[status] || 'default';
        }

        function showNotification(message, type) {
            const notification = document.getElementById('booking-notification');
            if (!notification) return;
            notification.textContent = message;
            notification.className = 'alert ' + (type === 'error' ? 'alert-error' : 'alert-success');
            notification.style.display = 'block';
            clearTimeout(window.bookingNotificationTimeout);
            window.bookingNotificationTimeout = setTimeout(() => {
                notification.style.display = 'none';
            }, 4500);
        }

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            const data = new FormData(form);
            data.set('action', 'create');
            data.set('ajax', '1');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: data,
                    credentials: 'include',
                    headers: {
                        Accept: 'application/json',
                    },
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    showNotification('Lỗi máy chủ khi thêm booking. ' + (errorText || 'Vui lòng thử lại.'), 'error');
                    return;
                }

                let json;
                try {
                    json = await response.json();
                } catch (parseError) {
                    const text = await response.text();
                    showNotification('Phản hồi không hợp lệ từ server. ' + (text || ''), 'error');
                    return;
                }
                if (!json.success) {
                    showNotification(json.message || 'Lỗi khi thêm booking.', 'error');
                    return;
                }

                body.innerHTML = '';
                json.rows.forEach(function (row) {
                    const tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td>' + String(row.MaBooking) + '</td>' +
                        '<td>' + String(row.HoTen) + '</td>' +
                        '<td>' + String(row.SoPhong) + '</td>' +
                        '<td>' + String(row.NgayNhan) + '</td>' +
                        '<td>' + String(row.NgayTra) + '</td>' +
                        '<td>' + String(row.SoNguoi) + '</td>' +
                        '<td><span class="status-badge status-' + statusClass(row.TrangThai) + '">' + String(row.TrangThai) + '</span></td>' +
                        '<td><a class="link-action" href="index.php?page=booking-list&edit=' + encodeURIComponent(row.MaBooking) + '">Chọn</a></td>';
                    body.appendChild(tr);
                });

                form.reset();
                form.querySelector('[name="MaBooking"]').value = json.nextCode;
                form.querySelector('[name="ajax"]').value = '0';

                showNotification('Booking đã được tạo thành công.', 'success');

                if (json.dashboard) {
                    const revenue = document.getElementById('dashboard-today-revenue');
                    const emptyRooms = document.getElementById('dashboard-room-empty');
                    const occupiedRooms = document.getElementById('dashboard-room-occupied');
                    const pendingBookings = document.getElementById('dashboard-pending-bookings');

                    if (revenue && typeof json.dashboard.DoanhThu !== 'undefined') {
                        revenue.textContent = json.dashboard.DoanhThu;
                    }
                    if (emptyRooms && typeof json.dashboard.PhongTrong !== 'undefined') {
                        emptyRooms.textContent = json.dashboard.PhongTrong;
                    }
                    if (occupiedRooms && typeof json.dashboard.PhongDangO !== 'undefined') {
                        occupiedRooms.textContent = json.dashboard.PhongDangO;
                    }
                    if (pendingBookings && typeof json.dashboard.ChoXuLy !== 'undefined') {
                        pendingBookings.textContent = json.dashboard.ChoXuLy;
                    }
                }
            } catch (error) {
                showNotification('Lỗi không xác định khi thêm booking.', 'error');
                console.error(error);
            }
        });
    })();
    </script>
</section>
