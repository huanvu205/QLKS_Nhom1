# Hướng dẫn chạy dự án QLKS

## 1. Tạo cơ sở dữ liệu SQL Server

Mở SQL Server Management Studio và chạy file:

```text
database/qlks_sqlserver.sql
```

Script này tạo database `QLKS_Nhom1`, toàn bộ bảng, khóa ngoại và dữ liệu mẫu.

## 2. Cấu hình kết nối

Sửa file:

```text
app/config/database.php
```

Ví dụ:

```php
return [
    'server' => 'localhost',
    'database' => 'QLKS_Nhom1',
    'username' => 'sa',
    'password' => '123456',
    'trust_server_certificate' => true,
];
```

Mặc định project đang dùng `driver => powershell`, tức là PHP gọi PowerShell và kết nối SQL Server bằng `System.Data.SqlClient.SqlConnection` với Windows Authentication. Cách này không cần cài `pdo_sqlsrv`.

## 4. Email

Project đã có cấu hình gửi mail thật qua SMTP trong:

```text
app/config/mail.php
```

Ví dụ dùng Gmail:

```php
'mode' => 'smtp',
'from' => 'emailcuaban@gmail.com',
'smtp_host' => 'smtp.gmail.com',
'smtp_port' => 587,
'smtp_user' => 'emailcuaban@gmail.com',
'smtp_pass' => 'APP_PASSWORD_16_KY_TU',
```

Gmail không dùng mật khẩu đăng nhập thường. Bạn cần bật xác minh 2 bước và tạo App Password.

Trang test gửi mail:

```text
http://127.0.0.1:8000/test-mail.php
```

Nếu SMTP lỗi, nội dung lỗi sẽ được ghi vào:

```text
storage/mail_outbox
```

Các luồng có gửi email thật:

```text
Quên mật khẩu: gửi mật khẩu tạm về email tài khoản
Đặt phòng: lễ tân nhập email khách, app lưu email và gửi xác nhận booking
Thanh toán: gửi thông báo hóa đơn đã thanh toán về email khách hàng
```

Nếu muốn dùng driver PHP chính thống thì mới cần bật extension:

```text
pdo_sqlsrv
sqlsrv
```

## 3. Chạy web

```powershell
php -S 127.0.0.1:8000 -t .
```

Mở:

```text
http://127.0.0.1:8000/index.php?page=login
```

Tài khoản mẫu:

```text
admin / admin123
letan01 / 123456
ketoan01 / 123456
```
