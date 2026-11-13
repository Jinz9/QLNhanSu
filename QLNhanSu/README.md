# Bài kiểm tra giữa học phần: Quản lý Nhân sự (QLNhanSu)
**Học phần:** Công nghệ và Lập trình web

## 1. Cấu trúc thư mục
```text
QLNhanSu/
├── config/
│   └── db.php            # Kết nối CSDL MySQL bằng PDO
├── database.sql          # File khởi tạo CSDL QLNhanSu và bảng Users
├── uploads/              # Thư mục lưu trữ ảnh đại diện tải lên
├── index.php             # Trang Frontend: Hiển thị danh sách Users dạng Card responsive
├── README.md             # Tài liệu hướng dẫn cài đặt và chạy
└── admin/
    ├── index.php         # Trang Quản trị: Danh sách Users + Nút Thêm/Sửa/Xóa
    ├── add.php           # Trang Thêm mới người dùng vào bảng Users
    ├── edit.php          # Trang Sửa thông tin người dùng
    └── delete.php        # Xử lý Xóa người dùng (POST) và xóa file ảnh
```

## 2. Hướng dẫn cài đặt trên XAMPP (Apache cổng 8080, MySQL cổng 3306)
1. **Copy mã nguồn vào `htdocs`:**
   - Copy toàn bộ thư mục `QLNhanSu` vào thư mục `C:\xampp\htdocs\` (đường dẫn sau khi copy: `C:\xampp\htdocs\QLNhanSu`).
2. **Khởi động XAMPP:**
   - Mở **XAMPP Control Panel** và nhấn **Start** cho 2 dịch vụ **Apache** (Port `8080`) và **MySQL** (Port `3306`).
3. **Import Cơ sở dữ liệu:**
   - Truy cập phpMyAdmin tại: `http://localhost:8080/phpmyadmin/`
   - Chọn tab **Import** (Nhập) trên thanh công cụ -> nhấn **Choose File** (Chọn tệp) -> chọn file `QLNhanSu/database.sql` -> nhấn **Go** (Thực hiện).
   - Cơ sở dữ liệu `QLNhanSu` và bảng `Users` sẽ được tạo tự động với bảng mã `utf8mb4_unicode_ci`.
4. **Cấu hình kết nối CSDL (nếu cần):**
   - File `config/db.php` đã được cấu hình khớp với XAMPP của bạn (`host = localhost`, `port = 3306`, `dbname = QLNhanSu`, `username = root`, `password = ''`).

## 3. Đường dẫn truy cập
- **phpMyAdmin:**
  `http://localhost:8080/phpmyadmin/`
- **Phân hệ Frontend (Trang hiển thị danh sách Users):**
  `http://localhost:8080/QLNhanSu/` (hoặc `http://localhost:8080/QLNhanSu/index.php`)
- **Phân hệ Admin (Trang quản trị Thêm / Sửa / Xóa):**
  `http://localhost:8080/QLNhanSu/admin/` (hoặc `http://localhost:8080/QLNhanSu/admin/index.php`)

