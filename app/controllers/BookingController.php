<?php

class BookingController extends Controller
{
    public function create(): void
    {
        $this->requireRole(['Admin', 'Lễ tân']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = trim((string) $this->post('MaBooking'));
            $customerEmail = trim((string) $this->post('Email'));
            if ($customerEmail !== '') {
                Database::execute('UPDATE KhachHang SET Email = ? WHERE MaKH = ?', [$customerEmail, $this->post('MaKH')]);
            }

            Database::execute(
                "INSERT INTO Booking (MaBooking, MaKH, MaPhong, NgayNhan, NgayTra, SoNguoi, TrangThai, GhiChu)
                 VALUES (?, ?, ?, ?, ?, ?, N'Đã đặt', ?)",
                [$code, $this->post('MaKH'), $this->post('MaPhong'), $this->post('NgayNhan'), $this->post('NgayTra'), (int) $this->post('SoNguoi'), $this->post('GhiChu')]
            );
            Database::execute("UPDATE Phong SET TrangThai = N'Đã đặt' WHERE MaPhong = ?", [$this->post('MaPhong')]);
            $this->sendBookingEmail($code);
            $this->redirect('booking-list');
        }

        $rooms = Database::fetchAll(
            "SELECT p.MaPhong, p.SoPhong, lp.TenLP, lp.GiaPhong
             FROM Phong p JOIN LoaiPhong lp ON lp.MaLP = p.MaLP
             WHERE p.TrangThai = N'Trống'
             ORDER BY p.SoPhong"
        );
        $customers = Database::fetchAll('SELECT MaKH, HoTen FROM KhachHang ORDER BY HoTen');
        $rows = Database::fetchAll(
            "SELECT TOP 8 b.MaBooking, kh.HoTen, p.SoPhong, b.NgayNhan, b.NgayTra, b.TrangThai
             FROM Booking b
             JOIN KhachHang kh ON kh.MaKH = b.MaKH
             JOIN Phong p ON p.MaPhong = b.MaPhong
             ORDER BY b.NgayTao DESC"
        );

        $this->render('booking/create', [
            'title' => 'Đặt phòng (Booking)',
            'active' => 'booking',
            'rooms' => $rooms,
            'customers' => $customers,
            'rows' => $rows,
            'nextCode' => 'BK' . date('His'),
        ]);
    }

    public function index(): void
    {
        $this->requireRole(['Admin', 'Lễ tân']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->post('action');
            $code = trim((string) $this->post('MaBooking'));
            $originalCode = trim((string) $this->post('OriginalMaBooking')) ?: $code;

            if ($action === 'create') {
                Database::execute(
                    "INSERT INTO Booking (MaBooking, MaKH, MaPhong, NgayNhan, NgayTra, SoNguoi, TrangThai, GhiChu)
                     VALUES (?, ?, ?, ?, ?, ?, N'Đã đặt', ?)",
                    [$code, $this->post('MaKH'), $this->post('MaPhong'), $this->post('NgayNhan'), $this->post('NgayTra'), (int) $this->post('SoNguoi'), $this->post('GhiChu')]
                );
                Database::execute("UPDATE Phong SET TrangThai = N'Đã đặt' WHERE MaPhong = ?", [$this->post('MaPhong')]);

                if ($this->post('ajax') === '1') {
                    $q = '';
                    $rows = Database::fetchAll(
                        "SELECT b.MaBooking, kh.HoTen, p.SoPhong, b.NgayNhan, b.NgayTra, b.SoNguoi, b.TrangThai
                         FROM Booking b
                         JOIN KhachHang kh ON kh.MaKH = b.MaKH
                         JOIN Phong p ON p.MaPhong = b.MaPhong
                         WHERE ? = '' OR b.MaBooking LIKE ? OR kh.HoTen LIKE ? OR p.SoPhong LIKE ?
                         ORDER BY b.NgayTao DESC",
                        [$q, "%$q%", "%$q%", "%$q%"]
                    );

                    $dashboardStats = Database::fetch(
                        "SELECT
                            SUM(CASE WHEN TrangThai = N'Trống' THEN 1 ELSE 0 END) AS PhongTrong,
                            SUM(CASE WHEN TrangThai = N'Đang ở' THEN 1 ELSE 0 END) AS PhongDangO
                         FROM Phong"
                    );
                    $dashboardRevenue = Database::fetch(
                        "SELECT COALESCE(SUM(TongTien), 0) AS DoanhThu
                         FROM HoaDon
                         WHERE CAST(NgayLap AS date) = CAST(GETDATE() AS date)"
                    );
                    $dashboardPending = Database::fetch(
                        "SELECT COUNT(*) AS ChoXuLy
                         FROM Booking
                         WHERE TrangThai IN (N'Đã đặt', N'Chờ xác nhận')"
                    );

                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'success' => true,
                        'rows' => $rows,
                        'nextCode' => 'BK' . date('His'),
                        'dashboard' => [
                            'PhongTrong' => (int) ($dashboardStats['PhongTrong'] ?? 0),
                            'PhongDangO' => (int) ($dashboardStats['PhongDangO'] ?? 0),
                            'DoanhThu' => number_format((float) ($dashboardRevenue['DoanhThu'] ?? 0), 0, ',', '.') . 'đ',
                            'ChoXuLy' => (int) ($dashboardPending['ChoXuLy'] ?? 0),
                        ],
                    ], JSON_UNESCAPED_UNICODE);
                    exit;
                }

                $this->redirect('booking-list', ['edit' => $code]);
            }

            if ($action === 'update') {
                $oldBooking = Database::fetch('SELECT MaPhong FROM Booking WHERE MaBooking = ?', [$originalCode]);
                Database::execute(
                    'UPDATE Booking SET NgayNhan = ?, NgayTra = ?, SoNguoi = ?, TrangThai = ?, GhiChu = ? WHERE MaBooking = ?',
                    [$this->post('NgayNhan'), $this->post('NgayTra'), (int) $this->post('SoNguoi'), $this->post('TrangThai'), $this->post('GhiChu'), $originalCode]
                );

                if ($oldBooking) {
                    $newStatus = $this->post('TrangThai');
                    if ($newStatus === 'Đã nhận phòng') {
                        Database::execute("UPDATE Phong SET TrangThai = N'Đang ở' WHERE MaPhong = ?", [$oldBooking['MaPhong']]);
                    } elseif ($newStatus === 'Đã đặt') {
                        Database::execute("UPDATE Phong SET TrangThai = N'Đã đặt' WHERE MaPhong = ?", [$oldBooking['MaPhong']]);
                    } elseif (in_array($newStatus, ['Đã trả phòng', 'Đã hủy'], true)) {
                        Database::execute("UPDATE Phong SET TrangThai = N'Trống' WHERE MaPhong = ?", [$oldBooking['MaPhong']]);
                    }
                }

                $this->redirect('booking-list', ['edit' => $originalCode]);
            } elseif ($action === 'delete') {
                $booking = Database::fetch('SELECT MaPhong FROM Booking WHERE MaBooking = ?', [$code]);
                Database::execute('DELETE FROM SuDungDichVu WHERE MaBooking = ?', [$code]);
                Database::execute('DELETE FROM HoaDon WHERE MaBooking = ?', [$code]);
                Database::execute('DELETE FROM Booking WHERE MaBooking = ?', [$code]);
                if ($booking) {
                    Database::execute("UPDATE Phong SET TrangThai = N'Trống' WHERE MaPhong = ?", [$booking['MaPhong']]);
                }
            }

            $this->redirect('booking-list');
        }

        $q = trim((string) $this->get('q'));
        $edit = $this->get('edit') ? Database::fetch('SELECT * FROM Booking WHERE MaBooking = ?', [$this->get('edit')]) : null;
        $rooms = Database::fetchAll(
            "SELECT p.MaPhong, p.SoPhong, lp.TenLP, lp.GiaPhong
             FROM Phong p JOIN LoaiPhong lp ON lp.MaLP = p.MaLP
             WHERE p.TrangThai = N'Trống'
             ORDER BY p.SoPhong"
        );
        $customers = Database::fetchAll('SELECT MaKH, HoTen FROM KhachHang ORDER BY HoTen');
        $rows = Database::fetchAll(
            "SELECT b.MaBooking, kh.HoTen, p.SoPhong, b.NgayNhan, b.NgayTra, b.SoNguoi, b.TrangThai
             FROM Booking b
             JOIN KhachHang kh ON kh.MaKH = b.MaKH
             JOIN Phong p ON p.MaPhong = b.MaPhong
             WHERE ? = '' OR b.MaBooking LIKE ? OR kh.HoTen LIKE ? OR p.SoPhong LIKE ?
             ORDER BY b.NgayTao DESC",
            [$q, "%$q%", "%$q%", "%$q%"]
        );

        $this->render('booking/list', [
            'title' => 'Danh sách booking',
            'active' => 'booking-list',
            'rows' => $rows,
            'edit' => $edit,
            'searchPlaceholder' => 'Nhập mã booking, khách hàng, phòng',
            'rooms' => $rooms,
            'customers' => $customers,
            'nextCode' => 'BK' . date('His'),
        ]);
    }

    public function checkIn(): void
    {
        $this->requireRole(['Admin', 'Lễ tân']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = trim((string) $this->post('MaBooking'));
            $booking = Database::fetch('SELECT MaPhong FROM Booking WHERE MaBooking = ?', [$code]);
            if ($booking) {
                Database::execute("UPDATE Booking SET TrangThai = N'Đã nhận phòng' WHERE MaBooking = ?", [$code]);
                Database::execute("UPDATE Phong SET TrangThai = N'Đang ở' WHERE MaPhong = ?", [$booking['MaPhong']]);
            }
            $this->redirect('check-in', ['q' => $code]);
        }

        $code = trim((string) $this->get('q'));
        $rows = $code === '' ? [] : Database::fetchAll(
            "SELECT b.MaBooking, kh.HoTen, kh.CCCD, p.SoPhong, b.NgayNhan, b.NgayTra, b.SoNguoi, b.TrangThai
             FROM Booking b JOIN KhachHang kh ON kh.MaKH = b.MaKH JOIN Phong p ON p.MaPhong = b.MaPhong
             WHERE b.MaBooking = ?",
            [$code]
        );

        $this->render('booking/check_in', [
            'title' => 'Nhận phòng (Check-in)',
            'active' => 'check-in',
            'rows' => $rows,
            'q' => $code,
        ]);
    }

    public function checkOut(): void
    {
        $this->requireRole(['Admin', 'Lễ tân', 'Kế toán']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $code = trim((string) $this->post('MaBooking'));
            $paymentMethod = trim((string) $this->post('PhuongThuc')) ?: 'TienMat';
            $booking = $this->bookingTotal($code);

            if ($booking) {
                Database::execute(
                    "IF NOT EXISTS (SELECT 1 FROM HoaDon WHERE MaBooking = ?)
                     INSERT INTO HoaDon (MaHD, MaBooking, NgayLap, TongTien, TrangThai, PhuongThuc)
                     VALUES (?, ?, GETDATE(), ?, N'Đã thanh toán', ?)",
                    [$code, 'HD' . date('His'), $code, $booking['TongTien'], $paymentMethod]
                );
                Database::execute("UPDATE Booking SET TrangThai = N'Đã trả phòng' WHERE MaBooking = ?", [$code]);
                Database::execute("UPDATE Phong SET TrangThai = N'Trống' WHERE MaPhong = ?", [$booking['MaPhong']]);
            }

            // collect optional notes
            $note = '';
            if ($paymentMethod === 'ChuyenKhoan') {
                $note = trim((string) $this->post('GhiChuChuyenKhoan'));
            } elseif ($paymentMethod === 'The') {
                $note = trim((string) $this->post('GhiChuThe'));
            }

            $this->sendPaymentEmail($code, $paymentMethod, $note);
            $this->redirect('invoices');
        }

        $code = trim((string) $this->get('q'));
        $booking = $code ? $this->bookingTotal($code) : null;
        $details = $code ? $this->invoiceDetails($code) : [];

        $this->render('booking/check_out', [
            'title' => 'Trả phòng (Check-out)',
            'active' => 'check-out',
            'q' => $code,
            'booking' => $booking,
            'details' => $details,
        ]);
    }

    public function serviceUsage(): void
    {
        $this->requireRole(['Admin', 'Lễ tân']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Database::execute(
                'INSERT INTO SuDungDichVu (MaBooking, MaDV, SoLuong, NgaySD) VALUES (?, ?, ?, ?)',
                [$this->post('MaBooking'), $this->post('MaDV'), (int) $this->post('SoLuong'), $this->post('NgaySD')]
            );
            $this->redirect('service-usage');
        }

        $bookings = Database::fetchAll("SELECT MaBooking FROM Booking WHERE TrangThai IN (N'Đã nhận phòng', N'Đã đặt') ORDER BY MaBooking");
        $services = Database::fetchAll('SELECT MaDV, TenDV FROM DichVu ORDER BY TenDV');
        $rows = Database::fetchAll(
            "SELECT sd.ID, sd.MaBooking, dv.TenDV, sd.SoLuong, dv.DonGia, sd.NgaySD, sd.SoLuong * dv.DonGia AS ThanhTien
             FROM SuDungDichVu sd JOIN DichVu dv ON dv.MaDV = sd.MaDV
             ORDER BY sd.ID DESC"
        );

        $this->render('booking/service_usage', [
            'title' => 'Sử dụng dịch vụ',
            'active' => 'service-usage',
            'bookings' => $bookings,
            'services' => $services,
            'rows' => $rows,
        ]);
    }

    public function invoices(): void
    {
        $this->requireRole(['Admin', 'Kế toán', 'Lễ tân']);
        $q = trim((string) $this->get('q'));
        $rows = Database::fetchAll(
            "SELECT hd.MaHD, hd.MaBooking, kh.HoTen, hd.NgayLap, hd.TongTien, hd.TrangThai
             FROM HoaDon hd
             JOIN Booking b ON b.MaBooking = hd.MaBooking
             JOIN KhachHang kh ON kh.MaKH = b.MaKH
             WHERE ? = '' OR hd.MaHD LIKE ? OR hd.MaBooking LIKE ? OR kh.HoTen LIKE ?
             ORDER BY hd.NgayLap DESC",
            [$q, "%$q%", "%$q%", "%$q%"]
        );

        $this->render('booking/invoices', [
            'title' => 'Quản lý hóa đơn',
            'active' => 'invoices',
            'rows' => $rows,
            'searchPlaceholder' => 'Nhập mã hóa đơn, booking hoặc khách hàng',
        ]);
    }

    public function invoiceWord(): void
    {
        $this->exportInvoice('word');
    }

    public function invoiceExcel(): void
    {
        $this->exportInvoice('excel');
    }

    public function reports(): void
    {
        $this->requireRole(['Admin', 'Kế toán']);
        [$from, $to, $summary, $rows] = $this->reportData();

        $this->render('reports', [
            'title' => 'Báo cáo - thống kê',
            'active' => 'reports',
            'from' => $from,
            'to' => $to,
            'summary' => $summary,
            'rows' => $rows,
        ]);
    }

    public function reportsExcel(): void
    {
        $this->requireRole(['Admin', 'Kế toán']);
        [$from, $to, $summary, $rows] = $this->reportData();

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename=bao-cao-thong-ke.xls');
        echo "\xEF\xBB\xBF";
        require __DIR__ . '/../views/exports/report_excel.php';
    }

    private function sendBookingEmail(string $code): void
    {
        $booking = Database::fetch(
            "SELECT b.MaBooking, kh.HoTen, kh.Email, p.SoPhong, b.NgayNhan, b.NgayTra, b.SoNguoi, b.TrangThai
             FROM Booking b
             JOIN KhachHang kh ON kh.MaKH = b.MaKH
             JOIN Phong p ON p.MaPhong = b.MaPhong
             WHERE b.MaBooking = ?",
            [$code]
        );

        if (!$booking || empty($booking['Email'])) {
            return;
        }

        Mailer::send(
            $booking['Email'],
            'Xac nhan dat phong HOTEL - ' . $booking['MaBooking'],
            '<h2>HOTEL xác nhận đặt phòng</h2>' .
            '<p>Xin chào <b>' . htmlspecialchars($booking['HoTen'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</b>,</p>' .
            '<p>Booking của quý khách đã được ghi nhận.</p>' .
            '<ul>' .
            '<li>Mã booking: <b>' . htmlspecialchars($booking['MaBooking'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</b></li>' .
            '<li>Phòng: <b>' . htmlspecialchars($booking['SoPhong'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</b></li>' .
            '<li>Ngày nhận: ' . htmlspecialchars((string) $booking['NgayNhan'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>' .
            '<li>Ngày trả: ' . htmlspecialchars((string) $booking['NgayTra'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>' .
            '<li>Số người: ' . htmlspecialchars((string) $booking['SoNguoi'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</li>' .
            '</ul>'
        );
    }

    private function sendPaymentEmail(string $code, string $method = '', string $note = ''): void
    {
        $invoice = Database::fetch(
            "SELECT hd.MaHD, hd.MaBooking, hd.TongTien, hd.NgayLap, kh.HoTen, kh.Email, p.SoPhong
             FROM HoaDon hd
             JOIN Booking b ON b.MaBooking = hd.MaBooking
             JOIN KhachHang kh ON kh.MaKH = b.MaKH
             JOIN Phong p ON p.MaPhong = b.MaPhong
             WHERE hd.MaBooking = ?",
            [$code]
        );

        if (!$invoice || empty($invoice['Email'])) {
            return;
        }

        $details = $this->invoiceDetails($code);
        $rows = '';
        foreach ($details as $detail) {
            $rows .= '<tr>' .
                '<td>' . htmlspecialchars($detail['Ten'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</td>' .
                '<td>' . htmlspecialchars((string) $detail['SoLuong'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</td>' .
                '<td>' . number_format((float) $detail['DonGia'], 0, ',', '.') . 'đ</td>' .
                '<td>' . number_format((float) $detail['ThanhTien'], 0, ',', '.') . 'đ</td>' .
                '</tr>';
        }

        $methodLabel = '';
        if ($method) {
            $map = [
                'TienMat' => 'Tiền mặt',
                'ChuyenKhoan' => 'Chuyển khoản',
                'The' => 'Quẹt thẻ',
                'VNPAY' => 'VNPAY',
            ];
            $methodLabel = '<p><strong>Phương thức thanh toán:</strong> ' . htmlspecialchars($map[$method] ?? $method, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
            if ($note !== '') {
                $methodLabel .= '<p><strong>Ghi chú:</strong> ' . htmlspecialchars($note, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
            }
        }

        Mailer::send(
            $invoice['Email'],
            'Hoa don thanh toan HOTEL - ' . $invoice['MaHD'],
            '<h2>HOTEL thông báo thanh toán thành công</h2>' .
            '<p>Xin chào <b>' . htmlspecialchars($invoice['HoTen'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</b>,</p>' .
            '<p>Hóa đơn <b>' . htmlspecialchars($invoice['MaHD'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</b> đã được thanh toán.</p>' .
            $methodLabel .
            '<table border="1" cellpadding="8" cellspacing="0"><thead><tr><th>Nội dung</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead><tbody>' .
            $rows .
            '</tbody></table>' .
            '<h3>Tổng tiền: ' . number_format((float) $invoice['TongTien'], 0, ',', '.') . 'đ</h3>'
        );
    }

    private function reportData(): array
    {
        $from = $this->get('from', date('Y-m-01'));
        $to = $this->get('to', date('Y-m-d'));
        $summary = Database::fetch(
            "SELECT COALESCE(SUM(TongTien), 0) AS DoanhThu, COUNT(*) AS SoHoaDon
             FROM HoaDon
             WHERE CAST(NgayLap AS date) BETWEEN ? AND ?",
            [$from, $to]
        );
        $rooms = Database::fetch(
            "SELECT
                SUM(CASE WHEN TrangThai = N'Trống' THEN 1 ELSE 0 END) AS PhongTrong,
                SUM(CASE WHEN TrangThai = N'Đang ở' THEN 1 ELSE 0 END) AS PhongDangO
             FROM Phong"
        );
        $rows = Database::fetchAll(
            "SELECT CAST(hd.NgayLap AS date) AS Ngay, COUNT(*) AS SoHoaDon, SUM(hd.TongTien) AS DoanhThu
             FROM HoaDon hd
             WHERE CAST(hd.NgayLap AS date) BETWEEN ? AND ?
             GROUP BY CAST(hd.NgayLap AS date)
             ORDER BY Ngay",
            [$from, $to]
        );

        return [$from, $to, array_merge($summary ?: [], $rooms ?: []), $rows];
    }

    private function exportInvoice(string $type): void
    {
        $this->requireRole(['Admin', 'Kế toán', 'Lễ tân']);
        $code = trim((string) $this->get('id'));
        $invoice = Database::fetch(
            "SELECT hd.*, kh.HoTen, kh.SDT, kh.CCCD, p.SoPhong
             FROM HoaDon hd
             JOIN Booking b ON b.MaBooking = hd.MaBooking
             JOIN KhachHang kh ON kh.MaKH = b.MaKH
             JOIN Phong p ON p.MaPhong = b.MaPhong
             WHERE hd.MaHD = ?",
            [$code]
        );

        if (!$invoice) {
            http_response_code(404);
            echo 'Không tìm thấy hóa đơn';
            return;
        }

        $details = $this->invoiceDetails($invoice['MaBooking']);
        $file = 'hoa-don-' . $invoice['MaHD'] . ($type === 'word' ? '.doc' : '.xls');
        header('Content-Type: ' . ($type === 'word' ? 'application/msword' : 'application/vnd.ms-excel') . '; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $file);
        echo "\xEF\xBB\xBF";
        require __DIR__ . '/../views/exports/invoice.php';
    }

    private function bookingTotal(string $code): ?array
    {
        return Database::fetch(
            "SELECT b.MaBooking, b.MaPhong, kh.HoTen, p.SoPhong, DATEDIFF(day, b.NgayNhan, b.NgayTra) AS SoDem,
                    lp.GiaPhong,
                    DATEDIFF(day, b.NgayNhan, b.NgayTra) * lp.GiaPhong +
                    COALESCE((SELECT SUM(sd.SoLuong * dv.DonGia)
                              FROM SuDungDichVu sd JOIN DichVu dv ON dv.MaDV = sd.MaDV
                              WHERE sd.MaBooking = b.MaBooking), 0) AS TongTien
             FROM Booking b
             JOIN KhachHang kh ON kh.MaKH = b.MaKH
             JOIN Phong p ON p.MaPhong = b.MaPhong
             JOIN LoaiPhong lp ON lp.MaLP = p.MaLP
             WHERE b.MaBooking = ?",
            [$code]
        );
    }

    private function invoiceDetails(string $code): array
    {
        $room = Database::fetch(
            "SELECT N'Tiền phòng' AS Ten, DATEDIFF(day, b.NgayNhan, b.NgayTra) AS SoLuong,
                    lp.GiaPhong AS DonGia, DATEDIFF(day, b.NgayNhan, b.NgayTra) * lp.GiaPhong AS ThanhTien
             FROM Booking b JOIN Phong p ON p.MaPhong = b.MaPhong JOIN LoaiPhong lp ON lp.MaLP = p.MaLP
             WHERE b.MaBooking = ?",
            [$code]
        );
        $services = Database::fetchAll(
            "SELECT dv.TenDV AS Ten, sd.SoLuong, dv.DonGia, sd.SoLuong * dv.DonGia AS ThanhTien
             FROM SuDungDichVu sd JOIN DichVu dv ON dv.MaDV = sd.MaDV
             WHERE sd.MaBooking = ?",
            [$code]
        );
        return array_values(array_filter(array_merge([$room], $services)));
    }
} 