<?php

class RoomController extends Controller
{
    public function dashboard(): void
    {
        $this->requireLogin();

        $stats = Database::fetch(
            "SELECT
                SUM(CASE WHEN TrangThai = N'Trống' THEN 1 ELSE 0 END) AS PhongTrong,
                SUM(CASE WHEN TrangThai = N'Đang ở' THEN 1 ELSE 0 END) AS PhongDangO,
                COUNT(*) AS TongPhong
             FROM Phong"
        ) ?? ['PhongTrong' => 0, 'PhongDangO' => 0, 'TongPhong' => 0];

        $today = Database::fetch(
            "SELECT COALESCE(SUM(TongTien), 0) AS DoanhThu FROM HoaDon WHERE CAST(NgayLap AS date) = CAST(GETDATE() AS date)"
        );
        $bookings = Database::fetch("SELECT COUNT(*) AS ChoXuLy FROM Booking WHERE TrangThai IN (N'Đã đặt', N'Chờ xác nhận')");
        $rooms = Database::fetchAll('SELECT TOP 18 SoPhong, TrangThai FROM Phong ORDER BY SoPhong');

        $this->render('dashboard', [
            'title' => 'Màn hình chính',
            'active' => 'dashboard',
            'stats' => $stats,
            'todayRevenue' => $this->money($today['DoanhThu'] ?? 0),
            'pendingBookings' => $bookings['ChoXuLy'] ?? 0,
            'rooms' => $rooms,
        ]);
    }

    public function roomTypes(): void
    {
        $this->requireRole(['Admin']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->post('action');
            $params = [
                trim((string) $this->post('MaLP')),
                (string) $this->post('TenLP'),
                (float) $this->post('GiaPhong'),
                (int) $this->post('SucChua'),
                (string) $this->post('MoTa'),
            ];

            if ($action === 'create') {
                Database::execute('INSERT INTO LoaiPhong (MaLP, TenLP, GiaPhong, SucChua, MoTa) VALUES (?, ?, ?, ?, ?)', $params);
            } elseif ($action === 'update') {
                Database::execute('UPDATE LoaiPhong SET TenLP = ?, GiaPhong = ?, SucChua = ?, MoTa = ? WHERE MaLP = ?', [$params[1], $params[2], $params[3], $params[4], $params[0]]);
            } elseif ($action === 'delete') {
                Database::execute('DELETE FROM LoaiPhong WHERE MaLP = ?', [$params[0]]);
            }

            $this->redirect('room-types');
        }

        $q = trim((string) $this->get('q'));
        $edit = $this->get('edit') ? Database::fetch('SELECT * FROM LoaiPhong WHERE MaLP = ?', [$this->get('edit')]) : null;
        $rows = Database::fetchAll(
            "SELECT MaLP, TenLP, GiaPhong, SucChua, MoTa FROM LoaiPhong WHERE ? = '' OR TenLP LIKE ? OR MaLP LIKE ? ORDER BY MaLP",
            [$q, "%$q%", "%$q%"]
        );

        $this->render('forms/module', [
            'title' => 'Quản lý loại phòng',
            'active' => 'room-types',
            'description' => 'Quản lý loại phòng, đơn giá và sức chứa.',
            'key' => 'MaLP',
            'searchPlaceholder' => 'Nhập mã hoặc tên loại phòng',
            'fields' => [
                ['MaLP', 'Mã loại phòng', 'text', $edit['MaLP'] ?? ''],
                ['TenLP', 'Tên loại phòng', 'text', $edit['TenLP'] ?? ''],
                ['GiaPhong', 'Giá phòng', 'number', $edit['GiaPhong'] ?? ''],
                ['SucChua', 'Sức chứa', 'number', $edit['SucChua'] ?? ''],
                ['MoTa', 'Mô tả', 'textarea', $edit['MoTa'] ?? ''],
            ],
            'columns' => ['Mã loại', 'Tên loại', 'Giá phòng', 'Sức chứa', 'Mô tả'],
            'rowKeys' => ['MaLP', 'TenLP', 'GiaPhong', 'SucChua', 'MoTa'],
            'rows' => $rows,
        ]);
    }

    public function rooms(): void
    {
        $this->requireRole(['Admin', 'Lễ tân']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->post('action');
            $params = [
                trim((string) $this->post('MaPhong')),
                (string) $this->post('SoPhong'),
                (string) $this->post('MaLP'),
                (string) $this->post('Tang'),
                (string) $this->post('TrangThai'),
                (string) $this->post('GhiChu'),
            ];

            if ($action === 'create') {
                Database::execute('INSERT INTO Phong (MaPhong, SoPhong, MaLP, Tang, TrangThai, GhiChu) VALUES (?, ?, ?, ?, ?, ?)', $params);
            } elseif ($action === 'update') {
                Database::execute('UPDATE Phong SET SoPhong = ?, MaLP = ?, Tang = ?, TrangThai = ?, GhiChu = ? WHERE MaPhong = ?', [$params[1], $params[2], $params[3], $params[4], $params[5], $params[0]]);
            } elseif ($action === 'delete') {
                Database::execute('DELETE FROM Phong WHERE MaPhong = ?', [$params[0]]);
            }

            $this->redirect('rooms');
        }

        $q = trim((string) $this->get('q'));
        $edit = $this->get('edit') ? Database::fetch('SELECT * FROM Phong WHERE MaPhong = ?', [$this->get('edit')]) : null;
        $types = array_column(Database::fetchAll('SELECT MaLP FROM LoaiPhong ORDER BY MaLP'), 'MaLP');
        $rows = Database::fetchAll(
            "SELECT p.MaPhong, p.SoPhong, lp.TenLP, p.Tang, p.TrangThai, p.GhiChu
             FROM Phong p JOIN LoaiPhong lp ON lp.MaLP = p.MaLP
             WHERE ? = '' OR p.SoPhong LIKE ? OR p.MaPhong LIKE ? OR lp.TenLP LIKE ?
             ORDER BY p.SoPhong",
            [$q, "%$q%", "%$q%", "%$q%"]
        );

        $this->render('forms/module', [
            'title' => 'Quản lý phòng',
            'active' => 'rooms',
            'description' => 'Quản lý danh sách phòng và trạng thái sử dụng.',
            'key' => 'MaPhong',
            'searchPlaceholder' => 'Nhập số phòng, mã phòng hoặc loại phòng',
            'fields' => [
                ['MaPhong', 'Mã phòng', 'text', $edit['MaPhong'] ?? ''],
                ['SoPhong', 'Số phòng', 'text', $edit['SoPhong'] ?? ''],
                ['MaLP', 'Loại phòng', 'select', $edit['MaLP'] ?? '', $types],
                ['Tang', 'Tầng', 'text', $edit['Tang'] ?? ''],
                ['TrangThai', 'Trạng thái', 'select', $edit['TrangThai'] ?? 'Trống', ['Trống', 'Đang ở', 'Đã đặt', 'Dọn dẹp', 'Bảo trì']],
                ['GhiChu', 'Ghi chú', 'textarea', $edit['GhiChu'] ?? ''],
            ],
            'columns' => ['Mã phòng', 'Số phòng', 'Loại phòng', 'Tầng', 'Trạng thái', 'Ghi chú'],
            'rowKeys' => ['MaPhong', 'SoPhong', 'TenLP', 'Tang', 'TrangThai', 'GhiChu'],
            'rows' => $rows,
        ]);
    }

    public function services(): void
    {
        $this->requireRole(['Admin', 'Lễ tân']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->post('action');
            $params = [
                trim((string) $this->post('MaDV')),
                (string) $this->post('TenDV'),
                (float) $this->post('DonGia'),
                (string) $this->post('DonViTinh'),
                (string) $this->post('MoTa'),
            ];

            if ($action === 'create') {
                Database::execute('INSERT INTO DichVu (MaDV, TenDV, DonGia, DonViTinh, MoTa) VALUES (?, ?, ?, ?, ?)', $params);
            } elseif ($action === 'update') {
                Database::execute('UPDATE DichVu SET TenDV = ?, DonGia = ?, DonViTinh = ?, MoTa = ? WHERE MaDV = ?', [$params[1], $params[2], $params[3], $params[4], $params[0]]);
            } elseif ($action === 'delete') {
                Database::execute('DELETE FROM DichVu WHERE MaDV = ?', [$params[0]]);
            }

            $this->redirect('services');
        }

        $q = trim((string) $this->get('q'));
        $edit = $this->get('edit') ? Database::fetch('SELECT * FROM DichVu WHERE MaDV = ?', [$this->get('edit')]) : null;
        $rows = Database::fetchAll(
            "SELECT MaDV, TenDV, DonGia, DonViTinh, MoTa FROM DichVu WHERE ? = '' OR TenDV LIKE ? OR MaDV LIKE ? ORDER BY MaDV",
            [$q, "%$q%", "%$q%"]
        );

        $this->render('forms/module', [
            'title' => 'Quản lý dịch vụ',
            'active' => 'services',
            'description' => 'Quản lý các dịch vụ khách sạn.',
            'key' => 'MaDV',
            'searchPlaceholder' => 'Nhập mã hoặc tên dịch vụ',
            'fields' => [
                ['MaDV', 'Mã dịch vụ', 'text', $edit['MaDV'] ?? ''],
                ['TenDV', 'Tên dịch vụ', 'text', $edit['TenDV'] ?? ''],
                ['DonGia', 'Đơn giá', 'number', $edit['DonGia'] ?? ''],
                ['DonViTinh', 'Đơn vị tính', 'text', $edit['DonViTinh'] ?? 'Lượt'],
                ['MoTa', 'Mô tả', 'textarea', $edit['MoTa'] ?? ''],
            ],
            'columns' => ['Mã DV', 'Tên dịch vụ', 'Đơn giá', 'Đơn vị', 'Mô tả'],
            'rowKeys' => ['MaDV', 'TenDV', 'DonGia', 'DonViTinh', 'MoTa'],
            'rows' => $rows,
        ]);
    }
}
