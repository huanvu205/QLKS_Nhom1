<?php

class CustomerController extends Controller
{
    public function index(): void
    {
        $this->requireRole(['Admin', 'Lễ tân']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $this->post('action');
            $params = [
                trim((string) $this->post('MaKH')),
                (string) $this->post('HoTen'),
                (string) $this->post('SDT'),
                (string) $this->post('CCCD'),
                (string) $this->post('Email'),
                (string) $this->post('DiaChi'),
            ];

            if ($action === 'create') {
                Database::execute('INSERT INTO KhachHang (MaKH, HoTen, SDT, CCCD, Email, DiaChi) VALUES (?, ?, ?, ?, ?, ?)', $params);
            } elseif ($action === 'update') {
                Database::execute('UPDATE KhachHang SET HoTen = ?, SDT = ?, CCCD = ?, Email = ?, DiaChi = ? WHERE MaKH = ?', [$params[1], $params[2], $params[3], $params[4], $params[5], $params[0]]);
            } elseif ($action === 'delete') {
                Database::execute('DELETE FROM KhachHang WHERE MaKH = ?', [$params[0]]);
            }

            $this->redirect('customers');
        }

        $q = trim((string) $this->get('q'));
        $edit = $this->get('edit') ? Database::fetch('SELECT * FROM KhachHang WHERE MaKH = ?', [$this->get('edit')]) : null;
        $rows = Database::fetchAll(
            "SELECT kh.MaKH, kh.HoTen, kh.SDT, kh.CCCD, kh.Email, kh.DiaChi,
                    COALESCE(tk.VaiTro, N'Khách hàng') AS VaiTro
             FROM KhachHang kh
             LEFT JOIN TaiKhoan tk ON kh.Email = tk.Email
             WHERE ? = '' OR kh.HoTen LIKE ? OR kh.SDT LIKE ? OR kh.CCCD LIKE ? OR kh.Email LIKE ?
             ORDER BY kh.MaKH",
            [$q, "%$q%", "%$q%", "%$q%", "%$q%"]
        );

        $this->render('forms/module', [
            'title' => 'Quản lý khách hàng',
            'active' => 'customers',
            'description' => 'Thêm, sửa, xóa và tìm kiếm thông tin khách hàng. Hiển thị vai trò tài khoản nếu đã đăng ký.',
            'key' => 'MaKH',
            'searchPlaceholder' => 'Nhập họ tên, SĐT, CCCD hoặc email',
            'fields' => [
                ['MaKH', 'Mã khách hàng', 'text', $edit['MaKH'] ?? ''],
                ['HoTen', 'Họ tên', 'text', $edit['HoTen'] ?? ''],
                ['SDT', 'Số điện thoại', 'text', $edit['SDT'] ?? ''],
                ['CCCD', 'CCCD', 'text', $edit['CCCD'] ?? ''],
                ['Email', 'Email', 'email', $edit['Email'] ?? ''],
                ['DiaChi', 'Địa chỉ', 'textarea', $edit['DiaChi'] ?? ''],
            ],
            'columns' => ['Mã KH', 'Họ tên', 'SĐT', 'CCCD', 'Email', 'Vai trò', 'Địa chỉ'],
            'rowKeys' => ['MaKH', 'HoTen', 'SDT', 'CCCD', 'Email', 'VaiTro', 'DiaChi'],
            'rows' => $rows,
        ]);
    }
}
