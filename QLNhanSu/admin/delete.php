<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Chỉ cho phép xóa bằng phương thức POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Phương thức truy cập không hợp lệ.';
    header('Location: index.php');
    exit;
}

// Kiểm tra id hợp lệ
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    $_SESSION['error'] = 'ID người dùng không hợp lệ.';
    header('Location: index.php');
    exit;
}

// Kiểm tra bản ghi có tồn tại trong CSDL hay không
$stmt = $pdo->prepare('SELECT admin_id, images FROM Users WHERE admin_id = :id');
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = 'Không tìm thấy người dùng cần xóa (ID #' . $id . ').';
    header('Location: index.php');
    exit;
}

// Thực hiện xóa bản ghi trong CSDL
$deleteStmt = $pdo->prepare('DELETE FROM Users WHERE admin_id = :id');
$deleteStmt->execute([':id' => $id]);

// Xóa luôn file ảnh đại diện trong thư mục uploads/ (nếu có)
if (!empty($user['images'])) {
    $filePath = __DIR__ . '/../uploads/' . $user['images'];
    if (file_exists($filePath)) {
        @unlink($filePath);
    }
}

$_SESSION['success'] = 'Đã xóa người dùng #' . $id . ' thành công!';
header('Location: index.php');
exit;

