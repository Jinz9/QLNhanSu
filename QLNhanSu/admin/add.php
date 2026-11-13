<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$errors = [];
$email = '';

// Xử lý khi submit form thêm mới
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['admin_email'] ?? '');
    $password = $_POST['admin_password'] ?? '';

    // 1. Validate Email
    if ($email === '') {
        $errors[] = 'Vui lòng nhập địa chỉ email.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Địa chỉ email không đúng định dạng.';
    } else {
        // Kiểm tra trùng email trong CSDL
        $checkStmt = $pdo->prepare('SELECT COUNT(*) FROM Users WHERE admin_email = :email');
        $checkStmt->execute([':email' => $email]);
        if ($checkStmt->fetchColumn() > 0) {
            $errors[] = 'Địa chỉ email này đã tồn tại trong hệ thống.';
        }
    }

    // 2. Validate Mật khẩu
    if (trim($password) === '') {
        $errors[] = 'Mật khẩu không được để trống.';
    }

    // 3. Validate và xử lý upload ảnh (nếu người dùng có chọn file)
    $uploadedFileName = null;
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

            // Kiểm tra thêm MIME thực tế của ảnh
            if (empty($errors)) {
                $imageInfo = @getimagesize($file['tmp_name']);
                if ($imageInfo === false) {
                    $errors[] = 'Tệp tải lên không phải là hình ảnh hợp lệ.';
                }
            }

            // Nếu ảnh hợp lệ thì tạo tên file duy nhất và lưu vào thư mục uploads/
            if (empty($errors)) {
                $uploadDir = __DIR__ . '/../uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $newFileName = time() . '_' . uniqid() . '.' . $ext;
                $destination = $uploadDir . $newFileName;

                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $uploadedFileName = $newFileName;
                } else {
                    $errors[] = 'Không thể lưu file ảnh vào thư mục uploads.';
                }
            }
        }
    }

    // 4. Lưu vào CSDL nếu không có lỗi
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $insertStmt = $pdo->prepare(
            'INSERT INTO Users (admin_email, admin_password, images) VALUES (:email, :password, :images)'
        );
        $insertStmt->execute([
            ':email'    => $email,
            ':password' => $hashedPassword,
            ':images'   => $uploadedFileName,
        ]);

        $_SESSION['success'] = 'Thêm người dùng mới thành công!';
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm người dùng - QLNhanSu</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
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
                    <h1 class="h4 fw-bold mb-0">Thêm người dùng mới</h1>
                    <a href="index.php" class="btn btn-outline-secondary btn-sm">&larr; Quay lại danh sách</a>
                </div>

                <!-- Hiển thị danh sách lỗi nếu có -->
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
                        <form action="add.php" method="POST" enctype="multipart/form-data" novalidate>
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
                                    placeholder="nhansu@example.com"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label for="admin_password" class="form-label fw-semibold">
                                    Mật khẩu <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="password"
                                    class="form-control"
                                    id="admin_password"
                                    name="admin_password"
                                    placeholder="Nhập mật khẩu..."
                                    required
                                >
                                <div class="form-text">Mật khẩu sẽ được mã hóa an toàn bằng <code>password_hash()</code>.</div>
                            </div>

                            <div class="mb-4">
                                <label for="images" class="form-label fw-semibold">Ảnh đại diện (images)</label>
                                <input
                                    type="file"
                                    class="form-control"
                                    id="images"
                                    name="images"
                                    accept=".jpg,.jpeg,.png,.gif,.webp,image/*"
                                >
                                <div class="form-text">
                                    Chấp nhận định dạng: JPG, JPEG, PNG, GIF, WEBP. Dung lượng tối đa: 2MB.
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-sm-flex justify-content-sm-end">
                                <a href="index.php" class="btn btn-light border px-4">Hủy</a>
                                <button type="submit" class="btn btn-primary px-4 fw-semibold">Lưu người dùng</button>
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

