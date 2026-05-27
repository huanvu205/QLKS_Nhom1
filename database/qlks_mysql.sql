SET NAMES utf8mb4;

DROP TABLE IF EXISTS SuDungDichVu;
DROP TABLE IF EXISTS HoaDon;
DROP TABLE IF EXISTS Booking;
DROP TABLE IF EXISTS Phong;
DROP TABLE IF EXISTS DichVu;
DROP TABLE IF EXISTS TaiKhoan;
DROP TABLE IF EXISTS KhachHang;
DROP TABLE IF EXISTS LoaiPhong;

CREATE TABLE KhachHang (
    MaKH VARCHAR(20) NOT NULL PRIMARY KEY,
    HoTen VARCHAR(100) NOT NULL,
    SDT VARCHAR(20) NOT NULL,
    CCCD VARCHAR(20) NULL,
    Email VARCHAR(100) NULL,
    DiaChi VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE TaiKhoan (
    TenDangNhap VARCHAR(50) NOT NULL PRIMARY KEY,
    MatKhau VARCHAR(255) NOT NULL,
    HoTen VARCHAR(100) NOT NULL,
    Email VARCHAR(100) NULL,
    MaKH VARCHAR(20) NULL,
    VaiTro VARCHAR(30) NOT NULL,
    TrangThai VARCHAR(30) NOT NULL DEFAULT 'Đang hoạt động',
    CONSTRAINT FK_TaiKhoan_KhachHang FOREIGN KEY (MaKH) REFERENCES KhachHang(MaKH)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE LoaiPhong (
    MaLP VARCHAR(20) NOT NULL PRIMARY KEY,
    TenLP VARCHAR(100) NOT NULL,
    GiaPhong DECIMAL(18, 0) NOT NULL,
    SucChua INT NOT NULL,
    MoTa VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Phong (
    MaPhong VARCHAR(20) NOT NULL PRIMARY KEY,
    SoPhong VARCHAR(20) NOT NULL UNIQUE,
    MaLP VARCHAR(20) NOT NULL,
    Tang VARCHAR(20) NULL,
    TrangThai VARCHAR(30) NOT NULL DEFAULT 'Trống',
    GhiChu VARCHAR(255) NULL,
    CONSTRAINT FK_Phong_LoaiPhong FOREIGN KEY (MaLP) REFERENCES LoaiPhong(MaLP)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE DichVu (
    MaDV VARCHAR(20) NOT NULL PRIMARY KEY,
    TenDV VARCHAR(100) NOT NULL,
    DonGia DECIMAL(18, 0) NOT NULL,
    DonViTinh VARCHAR(30) NOT NULL,
    MoTa VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE Booking (
    MaBooking VARCHAR(20) NOT NULL PRIMARY KEY,
    MaKH VARCHAR(20) NOT NULL,
    MaPhong VARCHAR(20) NOT NULL,
    NgayNhan DATETIME NOT NULL,
    NgayTra DATETIME NOT NULL,
    SoNguoi INT NOT NULL,
    TrangThai VARCHAR(30) NOT NULL,
    GhiChu VARCHAR(255) NULL,
    NgayTao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT FK_Booking_KhachHang FOREIGN KEY (MaKH) REFERENCES KhachHang(MaKH),
    CONSTRAINT FK_Booking_Phong FOREIGN KEY (MaPhong) REFERENCES Phong(MaPhong)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE HoaDon (
    MaHD VARCHAR(20) NOT NULL PRIMARY KEY,
    MaBooking VARCHAR(20) NOT NULL UNIQUE,
    NgayLap DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    TongTien DECIMAL(18, 0) NOT NULL,
    TrangThai VARCHAR(30) NOT NULL,
    PhuongThuc VARCHAR(50) NULL,
    CONSTRAINT FK_HoaDon_Booking FOREIGN KEY (MaBooking) REFERENCES Booking(MaBooking)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE SuDungDichVu (
    ID INT AUTO_INCREMENT NOT NULL PRIMARY KEY,
    MaBooking VARCHAR(20) NOT NULL,
    MaDV VARCHAR(20) NOT NULL,
    SoLuong INT NOT NULL,
    NgaySD DATE NOT NULL,
    CONSTRAINT FK_SDDV_Booking FOREIGN KEY (MaBooking) REFERENCES Booking(MaBooking),
    CONSTRAINT FK_SDDV_DichVu FOREIGN KEY (MaDV) REFERENCES DichVu(MaDV)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO KhachHang (MaKH, HoTen, SDT, CCCD, Email, DiaChi) VALUES
('KH001', 'Nguyễn Văn An', '0912345678', '001234567890', 'an@gmail.com', 'Hà Nội'),
('KH002', 'Trần Thị Bình', '0912456789', '002345678901', 'binh@gmail.com', 'Hải Phòng'),
('KH003', 'Lê Văn Cường', '0987654321', '003456789012', 'cuong@gmail.com', 'Đà Nẵng'),
('KH004', 'Phạm Thị Dung', '0909888777', '004567890123', 'dung@gmail.com', 'TP.HCM');

INSERT INTO TaiKhoan (TenDangNhap, MatKhau, HoTen, Email, MaKH, VaiTro, TrangThai) VALUES
('admin', '$2y$10$fdL/1ex9G1nvT8iDSTtfkOfsEisTr9j4hCaeJl53OzDl.cUwMK1MS', 'Nguyễn Văn Hoàng', 'admin@example.com', NULL, 'Admin', 'Đang hoạt động'),
('letan01', '$2y$10$CxR6nLWiZYOZXOFD6y7c2Oj9lhAtTeuy8VaIydzCjKEoMKPV9XXrK', 'Lê Thu Mai', 'letan01@example.com', NULL, 'Lễ tân', 'Đang hoạt động'),
('ketoan01', '$2y$10$CxR6nLWiZYOZXOFD6y7c2Oj9lhAtTeuy8VaIydzCjKEoMKPV9XXrK', 'Phạm Văn Nam', 'ketoan01@example.com', NULL, 'Kế toán', 'Đang hoạt động');

INSERT INTO LoaiPhong (MaLP, TenLP, GiaPhong, SucChua, MoTa) VALUES
('LP001', 'Phòng Deluxe', 1200000, 2, 'Phòng cao cấp, view đẹp'),
('LP002', 'Phòng Superior', 900000, 2, 'Phòng tiện nghi tiêu chuẩn'),
('LP003', 'Phòng Standard', 650000, 2, 'Phòng tiết kiệm'),
('LP004', 'Phòng VIP', 2200000, 4, 'Phòng rộng, dịch vụ ưu tiên');

INSERT INTO Phong (MaPhong, SoPhong, MaLP, Tang, TrangThai, GhiChu) VALUES
('P101', '101', 'LP001', '1', 'Trống', 'Phòng gần thang máy'),
('P102', '102', 'LP002', '1', 'Đang ở', NULL),
('P201', '201', 'LP002', '2', 'Trống', NULL),
('P202', '202', 'LP003', '2', 'Đã đặt', NULL),
('P301', '301', 'LP004', '3', 'Dọn dẹp', NULL),
('P302', '302', 'LP001', '3', 'Trống', NULL);

INSERT INTO DichVu (MaDV, TenDV, DonGia, DonViTinh, MoTa) VALUES
('DV001', 'Ăn sáng buffet', 150000, 'Suất', 'Buffet sáng'),
('DV002', 'Giặt ủi', 75000, 'Kg', 'Giặt và sấy'),
('DV003', 'Spa thư giãn', 500000, 'Lượt', 'Spa 60 phút'),
('DV004', 'Nước suối', 20000, 'Chai', 'Chai 500ml'),
('DV005', 'Dịch vụ đưa đón', 300000, 'Chuyến', 'Đưa đón sân bay');

INSERT INTO Booking (MaBooking, MaKH, MaPhong, NgayNhan, NgayTra, SoNguoi, TrangThai, GhiChu) VALUES
('BK001', 'KH001', 'P102', '2026-05-16 14:00:00', '2026-05-18 12:00:00', 2, 'Đã nhận phòng', 'Khách VIP'),
('BK002', 'KH002', 'P202', '2026-05-18 14:00:00', '2026-05-20 12:00:00', 2, 'Đã đặt', NULL);

INSERT INTO SuDungDichVu (MaBooking, MaDV, SoLuong, NgaySD) VALUES
('BK001', 'DV001', 2, '2026-05-17'),
('BK001', 'DV002', 3, '2026-05-17');

INSERT INTO HoaDon (MaHD, MaBooking, NgayLap, TongTien, TrangThai, PhuongThuc) VALUES
('HD001', 'BK001', CURRENT_TIMESTAMP, 2175000, 'Đã thanh toán', 'TienMat');
