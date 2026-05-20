IF DB_ID(N'QLKS_Nhom1') IS NULL
BEGIN
    CREATE DATABASE QLKS_Nhom1;
END
GO

USE QLKS_Nhom1;
GO

IF OBJECT_ID('SuDungDichVu', 'U') IS NOT NULL DROP TABLE SuDungDichVu;
IF OBJECT_ID('HoaDon', 'U') IS NOT NULL DROP TABLE HoaDon;
IF OBJECT_ID('Booking', 'U') IS NOT NULL DROP TABLE Booking;
IF OBJECT_ID('Phong', 'U') IS NOT NULL DROP TABLE Phong;
IF OBJECT_ID('DichVu', 'U') IS NOT NULL DROP TABLE DichVu;
IF OBJECT_ID('KhachHang', 'U') IS NOT NULL DROP TABLE KhachHang;
IF OBJECT_ID('LoaiPhong', 'U') IS NOT NULL DROP TABLE LoaiPhong;
IF OBJECT_ID('TaiKhoan', 'U') IS NOT NULL DROP TABLE TaiKhoan;
GO

CREATE TABLE TaiKhoan (
    TenDangNhap NVARCHAR(50) NOT NULL PRIMARY KEY,
    MatKhau NVARCHAR(255) NOT NULL,
    HoTen NVARCHAR(100) NOT NULL,
    VaiTro NVARCHAR(30) NOT NULL CHECK (VaiTro IN (N'Admin', N'Lễ tân', N'Kế toán')),
    TrangThai NVARCHAR(30) NOT NULL DEFAULT N'Đang hoạt động'
);

CREATE TABLE LoaiPhong (
    MaLP NVARCHAR(20) NOT NULL PRIMARY KEY,
    TenLP NVARCHAR(100) NOT NULL,
    GiaPhong DECIMAL(18, 0) NOT NULL,
    SucChua INT NOT NULL,
    MoTa NVARCHAR(255) NULL
);

CREATE TABLE Phong (
    MaPhong NVARCHAR(20) NOT NULL PRIMARY KEY,
    SoPhong NVARCHAR(20) NOT NULL UNIQUE,
    MaLP NVARCHAR(20) NOT NULL,
    Tang NVARCHAR(20) NULL,
    TrangThai NVARCHAR(30) NOT NULL DEFAULT N'Trống',
    GhiChu NVARCHAR(255) NULL,
    CONSTRAINT FK_Phong_LoaiPhong FOREIGN KEY (MaLP) REFERENCES LoaiPhong(MaLP)
);

CREATE TABLE KhachHang (
    MaKH NVARCHAR(20) NOT NULL PRIMARY KEY,
    HoTen NVARCHAR(100) NOT NULL,
    SDT NVARCHAR(20) NOT NULL,
    CCCD NVARCHAR(20) NULL,
    Email NVARCHAR(100) NULL,
    DiaChi NVARCHAR(255) NULL
);

CREATE TABLE DichVu (
    MaDV NVARCHAR(20) NOT NULL PRIMARY KEY,
    TenDV NVARCHAR(100) NOT NULL,
    DonGia DECIMAL(18, 0) NOT NULL,
    DonViTinh NVARCHAR(30) NOT NULL,
    MoTa NVARCHAR(255) NULL
);

CREATE TABLE Booking (
    MaBooking NVARCHAR(20) NOT NULL PRIMARY KEY,
    MaKH NVARCHAR(20) NOT NULL,
    MaPhong NVARCHAR(20) NOT NULL,
    NgayNhan DATE NOT NULL,
    NgayTra DATE NOT NULL,
    SoNguoi INT NOT NULL,
    TrangThai NVARCHAR(30) NOT NULL,
    GhiChu NVARCHAR(255) NULL,
    NgayTao DATETIME NOT NULL DEFAULT GETDATE(),
    CONSTRAINT FK_Booking_KhachHang FOREIGN KEY (MaKH) REFERENCES KhachHang(MaKH),
    CONSTRAINT FK_Booking_Phong FOREIGN KEY (MaPhong) REFERENCES Phong(MaPhong)
);

CREATE TABLE HoaDon (
    MaHD NVARCHAR(20) NOT NULL PRIMARY KEY,
    MaBooking NVARCHAR(20) NOT NULL UNIQUE,
    NgayLap DATETIME NOT NULL DEFAULT GETDATE(),
    TongTien DECIMAL(18, 0) NOT NULL,
    TrangThai NVARCHAR(30) NOT NULL,
    CONSTRAINT FK_HoaDon_Booking FOREIGN KEY (MaBooking) REFERENCES Booking(MaBooking)
);

CREATE TABLE SuDungDichVu (
    ID INT IDENTITY(1,1) NOT NULL PRIMARY KEY,
    MaBooking NVARCHAR(20) NOT NULL,
    MaDV NVARCHAR(20) NOT NULL,
    SoLuong INT NOT NULL,
    NgaySD DATE NOT NULL,
    CONSTRAINT FK_SDDV_Booking FOREIGN KEY (MaBooking) REFERENCES Booking(MaBooking),
    CONSTRAINT FK_SDDV_DichVu FOREIGN KEY (MaDV) REFERENCES DichVu(MaDV)
);
GO

INSERT INTO TaiKhoan (TenDangNhap, MatKhau, HoTen, VaiTro, TrangThai) VALUES
(N'admin', N'admin123', N'Nguyễn Văn Hoàng', N'Admin', N'Đang hoạt động'),
(N'letan01', N'123456', N'Lê Thu Mai', N'Lễ tân', N'Đang hoạt động'),
(N'ketoan01', N'123456', N'Phạm Văn Nam', N'Kế toán', N'Đang hoạt động');

INSERT INTO LoaiPhong (MaLP, TenLP, GiaPhong, SucChua, MoTa) VALUES
(N'LP001', N'Phòng Deluxe', 1200000, 2, N'Phòng cao cấp, view đẹp'),
(N'LP002', N'Phòng Superior', 900000, 2, N'Phòng tiện nghi tiêu chuẩn'),
(N'LP003', N'Phòng Standard', 650000, 2, N'Phòng tiết kiệm'),
(N'LP004', N'Phòng VIP', 2200000, 4, N'Phòng rộng, dịch vụ ưu tiên');

INSERT INTO Phong (MaPhong, SoPhong, MaLP, Tang, TrangThai, GhiChu) VALUES
(N'P101', N'101', N'LP001', N'1', N'Trống', N'Phòng gần thang máy'),
(N'P102', N'102', N'LP002', N'1', N'Đang ở', NULL),
(N'P201', N'201', N'LP002', N'2', N'Trống', NULL),
(N'P202', N'202', N'LP003', N'2', N'Đã đặt', NULL),
(N'P301', N'301', N'LP004', N'3', N'Dọn dẹp', NULL),
(N'P302', N'302', N'LP001', N'3', N'Trống', NULL);

INSERT INTO KhachHang (MaKH, HoTen, SDT, CCCD, Email, DiaChi) VALUES
(N'KH001', N'Nguyễn Văn An', N'0912345678', N'001234567890', N'an@gmail.com', N'Hà Nội'),
(N'KH002', N'Trần Thị Bình', N'0912456789', N'002345678901', N'binh@gmail.com', N'Hải Phòng'),
(N'KH003', N'Lê Văn Cường', N'0987654321', N'003456789012', N'cuong@gmail.com', N'Đà Nẵng'),
(N'KH004', N'Phạm Thị Dung', N'0909888777', N'004567890123', N'dung@gmail.com', N'TP.HCM');

INSERT INTO DichVu (MaDV, TenDV, DonGia, DonViTinh, MoTa) VALUES
(N'DV001', N'Ăn sáng buffet', 150000, N'Suất', N'Buffet sáng'),
(N'DV002', N'Giặt ủi', 75000, N'Kg', N'Giặt và sấy'),
(N'DV003', N'Spa thư giãn', 500000, N'Lượt', N'Spa 60 phút'),
(N'DV004', N'Nước suối', 20000, N'Chai', N'Chai 500ml'),
(N'DV005', N'Dịch vụ đưa đón', 300000, N'Chuyến', N'Đưa đón sân bay');

INSERT INTO Booking (MaBooking, MaKH, MaPhong, NgayNhan, NgayTra, SoNguoi, TrangThai, GhiChu) VALUES
(N'BK001', N'KH001', N'P102', '2026-05-16', '2026-05-18', 2, N'Đã nhận phòng', N'Khách VIP'),
(N'BK002', N'KH002', N'P202', '2026-05-18', '2026-05-20', 2, N'Đã đặt', NULL);

INSERT INTO SuDungDichVu (MaBooking, MaDV, SoLuong, NgaySD) VALUES
(N'BK001', N'DV001', 2, '2026-05-17'),
(N'BK001', N'DV002', 3, '2026-05-17');

INSERT INTO HoaDon (MaHD, MaBooking, NgayLap, TongTien, TrangThai, PhuongThuc) VALUES
(N'HD001', N'BK001', GETDATE(), 2175000, N'Đã thanh toán', N'TienMat');
GO
