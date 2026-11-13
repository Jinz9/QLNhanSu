<?php
// Cấu hình thông tin kết nối cơ sở dữ liệu MySQL (XAMPP: MySQL port 3306)
$host = 'localhost';
$port = 3306;
$dbname = 'QLNhanSu';
$username = 'root';
$password = '';

try {
    // Khởi tạo kết nối PDO với bảng mã utf8mb4
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Bật ngoại lệ khi có lỗi SQL
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Trả về mảng kết hợp mặc định
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Dùng Prepared Statements thật
    ]);
} catch (PDOException $e) {
    die('Lỗi kết nối cơ sở dữ liệu: ' . htmlspecialchars($e->getMessage()));
}

