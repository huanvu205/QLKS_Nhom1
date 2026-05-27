USE QLKS_Nhom1;
GO

IF COL_LENGTH('TaiKhoan', 'Email') IS NULL
    ALTER TABLE TaiKhoan ADD Email NVARCHAR(100) NULL;
GO

IF COL_LENGTH('TaiKhoan', 'MaKH') IS NULL
    ALTER TABLE TaiKhoan ADD MaKH NVARCHAR(20) NULL;
GO

DECLARE @ConstraintName sysname;
SELECT TOP 1 @ConstraintName = cc.name
FROM sys.check_constraints cc
JOIN sys.tables t ON t.object_id = cc.parent_object_id
WHERE t.name = 'TaiKhoan' AND cc.definition LIKE '%VaiTro%';

IF @ConstraintName IS NOT NULL
    EXEC('ALTER TABLE TaiKhoan DROP CONSTRAINT [' + @ConstraintName + ']');
GO

IF NOT EXISTS (SELECT 1 FROM sys.check_constraints WHERE name = 'CK_TaiKhoan_VaiTro')
    ALTER TABLE TaiKhoan ADD CONSTRAINT CK_TaiKhoan_VaiTro
    CHECK (VaiTro IN (N'Admin', N'Lễ tân', N'Kế toán', N'Khách hàng'));
GO

IF NOT EXISTS (SELECT 1 FROM sys.foreign_keys WHERE name = 'FK_TaiKhoan_KhachHang')
    ALTER TABLE TaiKhoan ADD CONSTRAINT FK_TaiKhoan_KhachHang
    FOREIGN KEY (MaKH) REFERENCES KhachHang(MaKH);
GO
