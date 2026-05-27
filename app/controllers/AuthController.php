<?php

class AuthController extends Controller
{
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim((string) $this->post('TenDangNhap'));
            $password = (string) $this->post('MatKhau');
            $user = Database::fetch("SELECT * FROM TaiKhoan WHERE TenDangNhap = ? AND TrangThai = N'Đang hoạt động'", [$username]);

            if ($user && ($password === $user['MatKhau'] || password_verify($password, $user['MatKhau']))) {
                $_SESSION['user'] = [
                    'TenDangNhap' => $user['TenDangNhap'],
                    'HoTen' => $user['HoTen'],
                    'VaiTro' => $user['VaiTro'],
                    'MaKH' => $user['MaKH'] ?? null,
                ];
                $this->redirect(($user['VaiTro'] ?? '') === 'Khách hàng' ? 'customer-rooms' : 'dashboard');
            }

            $error = 'Sai tài khoản, mật khẩu hoặc tài khoản đã bị khóa.';
        }

        $this->render('auth/login', [
            'title' => 'Đăng nhập hệ thống',
            'active' => 'login',
            'error' => $error ?? '',
        ]);
    }

    public function forgotPassword(): void
    {
        $this->ensureAccountEmailColumn();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim((string) $this->post('TenDangNhap'));
            $email = trim((string) $this->post('Email'));
            $user = Database::fetch('SELECT * FROM TaiKhoan WHERE TenDangNhap = ?', [$username]);

            if (!$user) {
                $message = 'Không tìm thấy tài khoản.';
            } elseif ($email === '') {
                $message = 'Vui lòng nhập email nhận mật khẩu.';
            } else {
                $newPassword = 'KS' . random_int(100000, 999999);
                Database::execute('UPDATE TaiKhoan SET MatKhau = ?, Email = ? WHERE TenDangNhap = ?', [password_hash($newPassword, PASSWORD_DEFAULT), $email, $username]);

                Mailer::send(
                    $email,
                    'Cap lai mat khau HOTEL',
                    '<p>Xin chao ' . htmlspecialchars($user['HoTen'] ?? $username, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ',</p>' .
                    '<p>Mat khau tam thoi cua ban la: <b>' . htmlspecialchars($newPassword, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</b></p>' .
                    '<p>Vui long dang nhap va doi mat khau ngay sau do.</p>'
                );

                $message = 'Đã gửi mật khẩu tạm thời về email. Nếu đang dùng chế độ log, xem file trong storage/mail_outbox.';
            }
        }

        $this->render('auth/forgot_password', [
            'title' => 'Quên mật khẩu',
            'active' => 'login',
            'message' => $message ?? '',
        ]);
    }

    public function logout(): void
    {
        session_destroy();
        $this->redirect('login');
    }

    public function password(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old = (string) $this->post('MatKhauCu');
            $new = (string) $this->post('MatKhauMoi');
            $confirm = (string) $this->post('NhapLaiMatKhau');
            $username = $_SESSION['user']['TenDangNhap'];
            $user = Database::fetch('SELECT * FROM TaiKhoan WHERE TenDangNhap = ?', [$username]);

            if (!$user || ($old !== $user['MatKhau'] && !password_verify($old, $user['MatKhau']))) {
                $message = 'Mật khẩu cũ không đúng.';
            } elseif ($new === '' || $new !== $confirm) {
                $message = 'Mật khẩu mới và xác nhận mật khẩu chưa khớp.';
            } else {
                Database::execute('UPDATE TaiKhoan SET MatKhau = ? WHERE TenDangNhap = ?', [password_hash($new, PASSWORD_DEFAULT), $username]);
                $message = 'Đã đổi mật khẩu thành công.';
            }
        }

        $this->render('forms/password', [
            'title' => 'Đổi mật khẩu',
            'active' => 'password',
            'message' => $message ?? '',
        ]);
    }

    public function accounts(): void
    {
        $this->requireRole(['Admin']);
        $this->ensureAccountEmailColumn();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->post('action');
            $rawPassword = (string) $this->post('MatKhau');
            $params = [
                trim((string) $this->post('TenDangNhap')),
                $rawPassword,
                (string) $this->post('HoTen'),
                (string) $this->post('Email'),
                (string) $this->post('VaiTro'),
                (string) $this->post('TrangThai'),
            ];

            if ($action === 'create') {
                Database::execute(
                    'INSERT INTO TaiKhoan (TenDangNhap, MatKhau, HoTen, Email, VaiTro, TrangThai) VALUES (?, ?, ?, ?, ?, ?)',
                    [$params[0], password_hash($rawPassword, PASSWORD_DEFAULT), $params[2], $params[3], $params[4], $params[5]]
                );
            } elseif ($action === 'update') {
                if ($rawPassword === '') {
                    Database::execute(
                        'UPDATE TaiKhoan SET HoTen = ?, Email = ?, VaiTro = ?, TrangThai = ? WHERE TenDangNhap = ?',
                        [$params[2], $params[3], $params[4], $params[5], $params[0]]
                    );
                } else {
                    Database::execute(
                        'UPDATE TaiKhoan SET MatKhau = ?, HoTen = ?, Email = ?, VaiTro = ?, TrangThai = ? WHERE TenDangNhap = ?',
                        [password_hash($rawPassword, PASSWORD_DEFAULT), $params[2], $params[3], $params[4], $params[5], $params[0]]
                    );
                }
            } elseif ($action === 'delete') {
                Database::execute('DELETE FROM TaiKhoan WHERE TenDangNhap = ?', [$params[0]]);
            } elseif ($action === 'reset') {
                Database::execute('UPDATE TaiKhoan SET MatKhau = ? WHERE TenDangNhap = ?', [password_hash('123456', PASSWORD_DEFAULT), $params[0]]);
            }

            $this->redirect('accounts');
        }

        $edit = $this->get('edit') ? Database::fetch('SELECT * FROM TaiKhoan WHERE TenDangNhap = ?', [$this->get('edit')]) : null;
        $rows = Database::fetchAll('SELECT TenDangNhap, HoTen, Email, VaiTro, TrangThai FROM TaiKhoan ORDER BY TenDangNhap');

        $this->render('forms/module', [
            'title' => 'Quản lý tài khoản',
            'active' => 'accounts',
            'description' => 'Phân quyền tài khoản: Admin, Lễ tân, Kế toán, Khách hàng.',
            'key' => 'TenDangNhap',
            'fields' => [
                ['TenDangNhap', 'Tên đăng nhập', 'text', $edit['TenDangNhap'] ?? ''],
                ['MatKhau', 'Mật khẩu', 'password', $edit ? '' : '123456'],
                ['HoTen', 'Họ tên', 'text', $edit['HoTen'] ?? ''],
                ['Email', 'Email', 'email', $edit['Email'] ?? ''],
                ['VaiTro', 'Vai trò', 'select', $edit['VaiTro'] ?? 'Lễ tân', ['Admin', 'Lễ tân', 'Kế toán', 'Khách hàng']],
                ['TrangThai', 'Trạng thái', 'select', $edit['TrangThai'] ?? 'Đang hoạt động', ['Đang hoạt động', 'Không hoạt động']],
            ],
            'columns' => ['Tên đăng nhập', 'Họ tên', 'Email', 'Vai trò', 'Trạng thái'],
            'rowKeys' => ['TenDangNhap', 'HoTen', 'Email', 'VaiTro', 'TrangThai'],
            'rows' => $rows,
            'actions' => ['create' => 'Thêm', 'update' => 'Sửa', 'delete' => 'Xóa', 'reset' => 'Đặt lại mật khẩu'],
        ]);
    }

    private function ensureAccountEmailColumn(): void
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
