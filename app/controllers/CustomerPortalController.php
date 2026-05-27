<?php

class CustomerPortalController extends Controller
{
    private const CUSTOMER_ROLE = 'Khách hàng';
    private const HOTLINE = '0336120405';

    public function register(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->ensureCustomerAccountColumns();

            $username = trim((string) $this->post('TenDangNhap'));
            $password = (string) $this->post('MatKhau');
            $confirm = (string) $this->post('NhapLaiMatKhau');
            $fullName = trim((string) $this->post('HoTen'));
            $phone = trim((string) $this->post('SDT'));
            $email = trim((string) $this->post('Email'));
            $cccd = trim((string) $this->post('CCCD'));
            $address = trim((string) $this->post('DiaChi'));

            if ($username === '' || $password === '' || $fullName === '' || $phone === '' || $email === '') {
                $error = 'Vui lòng nhập đầy đủ thông tin bắt buộc.';
            } elseif ($password !== $confirm) {
                $error = 'Mật khẩu xác nhận chưa khớp.';
            } elseif (Database::fetch('SELECT TenDangNhap FROM TaiKhoan WHERE TenDangNhap = ?', [$username])) {
                $error = 'Tên đăng nhập đã tồn tại.';
            } elseif (Database::fetch('SELECT MaKH FROM KhachHang WHERE Email = ?', [$email])) {
                $error = 'Email này đã được đăng ký.';
            } else {
                $customerCode = $this->nextCustomerCode();
                Database::execute(
                    'INSERT INTO KhachHang (MaKH, HoTen, SDT, CCCD, Email, DiaChi) VALUES (?, ?, ?, ?, ?, ?)',
                    [$customerCode, $fullName, $phone, $cccd, $email, $address]
                );
                Database::execute(
                    'INSERT INTO TaiKhoan (TenDangNhap, MatKhau, HoTen, Email, VaiTro, TrangThai, MaKH) VALUES (?, ?, ?, ?, ?, ?, ?)',
                    [$username, password_hash($password, PASSWORD_DEFAULT), $fullName, $email, self::CUSTOMER_ROLE, 'Đang hoạt động', $customerCode]
                );

                $_SESSION['user'] = [
                    'TenDangNhap' => $username,
                    'HoTen' => $fullName,
                    'VaiTro' => self::CUSTOMER_ROLE,
                    'MaKH' => $customerCode,
                ];
                $this->redirect('customer-rooms');
            }
        }

        $this->render('auth/register', [
            'title' => 'Đăng ký tài khoản',
            'active' => 'login',
            'error' => $error ?? '',
            'hotline' => self::HOTLINE,
        ]);
    }

    public function rooms(): void
    {
        $this->requireCustomer();

        $from = str_replace('T', ' ', trim((string) $this->get('from', date('Y-m-d\T14:00'))));
        $to = str_replace('T', ' ', trim((string) $this->get('to', date('Y-m-d\T12:00', strtotime('+1 day')))));
        $rooms = $this->availableRooms($from, $to);

        $this->render('customer/rooms', [
            'title' => 'Phòng còn trống',
            'active' => 'customer-rooms',
            'rooms' => $rooms,
            'from' => $from,
            'to' => $to,
            'hotline' => self::HOTLINE,
        ]);
    }

    public function booking(): void
    {
        $this->requireCustomer();

        $from = str_replace('T', ' ', trim((string) $this->post('NgayNhan', $this->get('from', date('Y-m-d\T14:00')))));
        $to = str_replace('T', ' ', trim((string) $this->post('NgayTra', $this->get('to', date('Y-m-d\T12:00', strtotime('+1 day'))))));
        $roomCode = trim((string) $this->post('MaPhong', $this->get('room')));
        $rooms = $this->availableRooms($from, $to);
        $selectedRoom = null;
        foreach ($rooms as $room) {
            if (($room['MaPhong'] ?? '') === $roomCode) {
                $selectedRoom = $room;
                break;
            }
        }
        if (!$selectedRoom && $rooms) {
            $selectedRoom = $rooms[0];
            $roomCode = (string) $selectedRoom['MaPhong'];
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $roomAvailable = false;
            foreach ($rooms as $room) {
                if (($room['MaPhong'] ?? '') === $roomCode) {
                    $roomAvailable = true;
                    break;
                }
            }

            if ($from === '' || $to === '' || strtotime($to) <= strtotime($from)) {
                $error = 'Ngày trả phòng phải sau ngày nhận phòng.';
            } elseif (!$roomAvailable) {
                $error = 'Phòng đã chọn không còn trống trong khoảng ngày này.';
            } else {
                $code = $this->nextBookingCode();
                Database::execute(
                    "INSERT INTO Booking (MaBooking, MaKH, MaPhong, NgayNhan, NgayTra, SoNguoi, TrangThai, GhiChu)
                     VALUES (?, ?, ?, ?, ?, ?, N'Chờ xác nhận', ?)",
                    [$code, $_SESSION['user']['MaKH'], $roomCode, $from, $to, (int) $this->post('SoNguoi'), trim((string) $this->post('GhiChu'))]
                );
                $this->sendBookingRequestEmail($code);
                $this->redirect('customer-bookings', ['success' => $code]);
            }
        }

        $this->render('customer/booking', [
            'title' => 'Đặt phòng',
            'active' => 'customer-booking',
            'rooms' => $rooms,
            'selectedRoom' => $selectedRoom,
            'from' => $from,
            'to' => $to,
            'roomCode' => $roomCode,
            'error' => $error ?? '',
            'hotline' => self::HOTLINE,
        ]);
    }

    public function bookings(): void
    {
        $this->requireCustomer();

        $rows = Database::fetchAll(
            "SELECT b.MaBooking, p.SoPhong, lp.TenLP, lp.GiaPhong, b.NgayNhan, b.NgayTra, b.SoNguoi, b.TrangThai, b.GhiChu
             FROM Booking b
             JOIN Phong p ON p.MaPhong = b.MaPhong
             JOIN LoaiPhong lp ON lp.MaLP = p.MaLP
             WHERE b.MaKH = ?
             ORDER BY b.NgayTao DESC",
            [$_SESSION['user']['MaKH']]
        );

        $this->render('customer/bookings', [
            'title' => 'Booking của tôi',
            'active' => 'customer-bookings',
            'rows' => $rows,
            'success' => $this->get('success'),
            'hotline' => self::HOTLINE,
        ]);
    }

    private function requireCustomer(): void
    {
        $this->requireLogin();
        if (($_SESSION['user']['VaiTro'] ?? '') !== self::CUSTOMER_ROLE || empty($_SESSION['user']['MaKH'])) {
            http_response_code(403);
            $this->render('error', [
                'title' => 'Không có quyền',
                'active' => 'dashboard',
                'message' => 'Chức năng này chỉ dành cho tài khoản khách hàng.',
            ]);
            exit;
        }
    }

    private function availableRooms(string $from, string $to): array
    {
        return Database::fetchAll(
            "SELECT p.MaPhong, p.SoPhong, p.Tang, p.TrangThai, lp.TenLP, lp.GiaPhong, lp.SucChua, lp.MoTa
             FROM Phong p
             JOIN LoaiPhong lp ON lp.MaLP = p.MaLP
             WHERE p.TrangThai IN (N'Trống', N'Dọn dẹp')
               AND NOT EXISTS (
                   SELECT 1
                   FROM Booking b
                   WHERE b.MaPhong = p.MaPhong
                     AND b.TrangThai NOT IN (N'Đã trả phòng', N'Đã hủy')
                     AND b.NgayNhan < ?
                     AND b.NgayTra > ?
               )
             ORDER BY lp.GiaPhong, p.SoPhong",
            [$to, $from]
        );
    }

    private function nextCustomerCode(): string
    {
        return 'KH' . date('ymdHis');
    }

    private function nextBookingCode(): string
    {
        return 'BK' . date('ymdHis');
    }

    private function sendBookingRequestEmail(string $code): void
    {
        $booking = Database::fetch(
            "SELECT b.MaBooking, kh.HoTen, kh.Email, p.SoPhong, lp.TenLP, b.NgayNhan, b.NgayTra, b.SoNguoi, b.TrangThai
             FROM Booking b
             JOIN KhachHang kh ON kh.MaKH = b.MaKH
             JOIN Phong p ON p.MaPhong = b.MaPhong
             JOIN LoaiPhong lp ON lp.MaLP = p.MaLP
             WHERE b.MaBooking = ?",
            [$code]
        );

        if (!$booking || empty($booking['Email'])) {
            return;
        }

        Mailer::send(
            $booking['Email'],
            'HOTEL da nhan yeu cau dat phong - ' . $booking['MaBooking'],
            '<h2>HOTEL đã nhận yêu cầu đặt phòng</h2>' .
            '<p>Xin chào <b>' . htmlspecialchars($booking['HoTen'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</b>,</p>' .
            '<p>Yêu cầu đặt phòng của quý khách đã được ghi nhận và đang chờ lễ tân xác nhận.</p>' .
            '<table style="width:100%; border-collapse:collapse; margin:16px 0; background:#f7faf9; border:1px solid #e2ebe8; border-radius:8px; overflow:hidden;">' .
            '<tr><td style="padding:12px 16px; border-bottom:1px solid #e2ebe8; color:#2f6f69; width:40%;">Mã booking</td><td style="padding:12px 16px; border-bottom:1px solid #e2ebe8;"><b>' . htmlspecialchars($booking['MaBooking'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</b></td></tr>' .
            '<tr><td style="padding:12px 16px; border-bottom:1px solid #e2ebe8; color:#2f6f69;">Phòng</td><td style="padding:12px 16px; border-bottom:1px solid #e2ebe8;"><b>' . htmlspecialchars($booking['SoPhong'] . ' - ' . $booking['TenLP'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</b></td></tr>' .
            '<tr><td style="padding:12px 16px; border-bottom:1px solid #e2ebe8; color:#2f6f69;">Ngày nhận</td><td style="padding:12px 16px; border-bottom:1px solid #e2ebe8;"><b>' . htmlspecialchars(date('H:i d/m/Y', strtotime((string) $booking['NgayNhan'])), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</b></td></tr>' .
            '<tr><td style="padding:12px 16px; border-bottom:1px solid #e2ebe8; color:#2f6f69;">Ngày trả</td><td style="padding:12px 16px; border-bottom:1px solid #e2ebe8;"><b>' . htmlspecialchars(date('H:i d/m/Y', strtotime((string) $booking['NgayTra'])), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</b></td></tr>' .
            '<tr><td style="padding:12px 16px; border-bottom:1px solid #e2ebe8; color:#2f6f69;">Số người</td><td style="padding:12px 16px; border-bottom:1px solid #e2ebe8;"><b>' . htmlspecialchars((string) $booking['SoNguoi'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</b> người</td></tr>' .
            '<tr><td style="padding:12px 16px; color:#2f6f69;">Trạng thái</td><td style="padding:12px 16px;"><span style="display:inline-block; padding:4px 10px; background:#fff8ef; color:#60401f; border-radius:4px; font-weight:bold; border:1px solid #efd1ad;">' . htmlspecialchars($booking['TrangThai'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</span></td></tr>' .
            '</table>' .
            '<p>Khi booking được xác nhận, hệ thống sẽ tiếp tục gửi email thông báo cho quý khách.</p>' .
            '<p>Hotline hỗ trợ: <b>' . self::HOTLINE . '</b></p>'
        );
    }

    private function ensureCustomerAccountColumns(): void
    {
        if (Database::isMySql()) {
            return;
        }

        Database::execute("IF COL_LENGTH('TaiKhoan', 'Email') IS NULL ALTER TABLE TaiKhoan ADD Email NVARCHAR(100) NULL");
        Database::execute("IF COL_LENGTH('TaiKhoan', 'MaKH') IS NULL ALTER TABLE TaiKhoan ADD MaKH NVARCHAR(20) NULL");
        Database::execute(
            "DECLARE @ConstraintName sysname;
             SELECT TOP 1 @ConstraintName = cc.name
             FROM sys.check_constraints cc
             JOIN sys.tables t ON t.object_id = cc.parent_object_id
             WHERE t.name = 'TaiKhoan' AND cc.definition LIKE '%VaiTro%';
             IF @ConstraintName IS NOT NULL EXEC('ALTER TABLE TaiKhoan DROP CONSTRAINT [' + @ConstraintName + ']');
             IF NOT EXISTS (SELECT 1 FROM sys.check_constraints WHERE name = 'CK_TaiKhoan_VaiTro')
             ALTER TABLE TaiKhoan ADD CONSTRAINT CK_TaiKhoan_VaiTro CHECK (VaiTro IN (N'Admin', N'Lễ tân', N'Kế toán', N'Khách hàng'))"
        );
    }
}
