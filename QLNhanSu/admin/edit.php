<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Kiểm tra tham số id trên URL
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    $_SESSION['error'] = 'ID người dùng không hợp lệ.';
    header('Location: index.php');
    exit;
}

// Tìm bản ghi theo id
$stmt = $pdo->prepare('SELECT admin_id, admin_email, admin_password, images FROM Users WHERE admin_id = :id');
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = 'Không tìm thấy người dùng với ID #' . $id . '.';
    header('Location: index.php');
    exit;
}

$errors = [];
$email = $user['admin_email'];
$currentImage = $user['images'];

// Xử lý khi submit form cập nhật
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['admin_email'] ?? '');
    $newPassword = $_POST['admin_password'] ?? '';

    // 1. Validate Email
    if ($email === '') {
        $errors[] = 'Vui lòng nhập địa chỉ email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Địa chỉ email không đúng định dạng.';
    } else {
        // Kiểm tra trùng email với người dùng khác
        $checkStmt = $pdo->prepare(
            'SELECT COUNT(*) FROM Users WHERE admin_email = :email AND admin_id != :id'
        );
        $checkStmt->execute([
            ':email' => $email,
            ':id'    => $id,
        ]);
        if ($checkStmt->fetchColumn() > 0) {
            $errors[] = 'Địa chỉ email này đã được sử dụng bởi người dùng khác.';
        }
    }

    // 2. Xử lý mật khẩu: để trống thì giữ mật khẩu cũ, có nhập thì mã hóa mới
    $passwordToSave = $user['admin_password'];
    if ($newPassword !== '') {
        if (trim($newPassword) === '') {
            $errors[] = 'Mật khẩu mới không được chỉ chứa khoảng trắng.';
        } else {
            $passwordToSave = password_hash($newPassword, PASSWORD_DEFAULT);
        }
    }

    // 3. Xử lý ảnh đại diện: không chọn ảnh mới thì giữ ảnh cũ
    $imageToSave = $currentImage;
    if (isset($_FILES['images']) && $_FILES['images']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['images'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Có lỗi xảy ra trong quá trình tải ảnh lên (Mã lỗi: ' . $file['error'] . ').';
        } else {
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $maxFileSize = 2 * 1024 * 1024; // 2MB

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions, true)) {
                $errors[] = 'Định dạng ảnh không hợp lệ. Chỉ chấp nhận: jpg, jpeg, png, gif, webp.';
            }

            if ($file['size'] > $maxFileSize) {
                $errors[] = 'Dung lượng ảnh vượt quá giới hạn tối đa 2MB.';
            }

            if (empty($errors)) {
                $imageInfo = @getimagesize($file['tmp_name']);
                if ($imageInfo === false) {
                    $errors[] = 'Tệp tải lên không phải là hình ảnh hợp lệ.';
                }
            }

            if (empty($errors)) {
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $newFileName = time() . '_' . uniqid() . '.' . $ext;
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    // Xóa file ảnh cũ nếu tồn tại
                    if (!empty($currentImage)) {
                        $oldFilePath = $uploadDir . $currentImage;
                        if (file_exists($oldFilePath)) {
                            @unlink($oldFilePath);
                        }
                    }
                    $imageToSave = $newFileName;
                } else {
                    $errors[] = 'Không thể lưu file ảnh mới vào thư mục uploads.';
                }
            }
        }
    }

    // 4. Cập nhật vào CSDL nếu không có lỗi
    if (empty($errors)) {
        $updateStmt = $pdo->prepare(
            'UPDATE Users SET admin_email = :email, admin_password = :password, images = :images WHERE admin_id = :id'
        );
        $updateStmt->execute([
            ':email'    => $email,
            ':password' => $passwordToSave,
            ':images'   => $imageToSave,
            ':id'       => $id,
        ]);

        $_SESSION['success'] = 'Cập nhật thông tin người dùng #' . $id . ' thành công!';
        header('Location: index.php');
        exit;
    }
}

// Ảnh mặc định hiển thị trong trang sửa
$defaultAvatar = "data:image/svg+xml;utf8," . rawurlencode(
    '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">'
    . '<rect width="100%" height="100%" fill="#e9ecef"/>'
    . '<circle cx="60" cy="45" r="20" fill="#adb5bd"/>'
    . '<path d="M25 100 C25 72, 95 72, 95 100 Z" fill="#adb5bd"/>'
    . '</svg>'
);
$avatarPreview = (!empty($currentImage) && file_exists(__DIR__ . '/../uploads/' . $currentImage))
    ? '../uploads/' . htmlspecialchars($currentImage)
    : $defaultAvatar;
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa người dùng #<?= htmlspecialchars((string)$id) ?> - QLNhanSu</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .current-avatar {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 10px;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <!-- Thanh điều hướng -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php">QLNhanSu</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">Trang chủ (Frontend)</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Quản trị (Admin)</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="h4 fw-bold mb-0">Sửa người dùng #<?= htmlspecialchars((string)$id) ?></h1>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">&larr; Quay lại danh sách</a>
                </div>

                <!-- Hiển thị lỗi nếu có -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm">
                        <div class="fw-semibold mb-1">Vui lòng kiểm tra lại thông tin:</div>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-body p-4">
                        <form action="edit.php?id=<?= urlencode((string)$id) ?>" method="POST" enctype="multipart/form-data" novalidate>
                            <div class="mb-3">
                                <label for="admin_email" class="form-label fw-semibold">
                                    Email <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="email"
                                    class="form-control"
                                    id="admin_email"
                                    name="admin_email"
                                    value="<?= htmlspecialchars($email) ?>"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label for="admin_password" class="form-label fw-semibold">
                                    Mật khẩu mới (để trống nếu giữ nguyên)
                                </label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="admin_password"
                                    name="admin_password"
                                    placeholder="Nhập mật khẩu mới nếu muốn thay đổi..."
                                >
                                <div class="form-text">Nếu không nhập, mật khẩu cũ sẽ được giữ nguyên.</div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold d-block">Ảnh đại diện hiện tại</label>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <img src="<?= $avatarPreview ?>" alt="Current Avatar" class="current-avatar">
                                    <div class="small text-muted">
                                        <?php if (!empty($currentImage)): ?>
                                            Tên file: <code><?= htmlspecialchars($currentImage) ?></code>
                                        <?php else: ?>
                                            Chưa có ảnh tải lên (đang dùng ảnh mặc định)
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <label for="images" class="form-label fw-semibold">Chọn ảnh mới (nếu muốn thay đổi)</label>
                                <input
                                    type="file"
                                    class="form-control"
                                    id="images"
                                    name="images"
                                    accept=".jpg,.jpeg,.png,.gif,.webp,image/*"
                                >
                                <div class="form-text">
                                    Chấp nhận: JPG, JPEG, PNG, GIF, WEBP (tối đa 2MB). Nếu chọn ảnh mới, ảnh cũ sẽ tự động được xóa.
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                                <a href="index.php" class="btn btn-light border px-4">Hủy</a>
                                <button type="submit" class="btn btn-primary px-4 fw-semibold">Cập nhật</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

