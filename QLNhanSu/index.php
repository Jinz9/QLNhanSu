<?php
require_once __DIR__ . '/config/db.php';

// Lấy danh sách toàn bộ người dùng từ bảng Users (không lấy cột mật khẩu để đảm bảo an toàn)
$stmt = $pdo->prepare('SELECT admin_id, admin_email, images FROM Users ORDER BY admin_id DESC');
$stmt->execute();
$users = $stmt->fetchAll();

// Ảnh đại diện mặc định dạng SVG Data URI khi bản ghi không có ảnh hoặc file ảnh không tồn tại
$defaultAvatar = "data:image/svg+xml;utf8," . rawurlencode(
    '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300">'
    . '<rect width="100%" height="100%" fill="#e9ecef"/>'
    . '<circle cx="200" cy="115" r="48" fill="#adb5bd"/>'
    . '<path d="M110 250 C110 190, 290 190, 290 250 Z" fill="#adb5bd"/>'
    . '</svg>'
);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách nhân sự - QLNhanSu</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .user-card {
            border: none;
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .user-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 0.5rem 1.25rem rgba(0, 0, 0, 0.1) !important;
        }
        .user-avatar {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            background-color: #e9ecef;
        }
    </style>
</head>
<body>
    <!-- Thanh điều hướng (Navbar) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">QLNhanSu</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/index.php">Quản trị (Admin)</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Nội dung chính -->
    <main class="container pb-5">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">Danh sách nhân sự (Users)</h1>
                <p class="text-muted mb-0">Hiển thị dữ liệu nhân sự trên hệ thống</p>
            </div>
            <span class="badge bg-primary rounded-pill fs-6 px-3 py-2 mt-2 mt-sm-0">
                Tổng số: <?= count($users) ?> nhân sự
            </span>
        </div>

        <?php if (empty($users)): ?>
            <!-- Thông báo khi chưa có dữ liệu -->
            <div class="alert alert-info text-center py-5 shadow-sm rounded-3" role="alert">
                <h5 class="mb-2 fw-semibold">Chưa có dữ liệu</h5>
                <p class="mb-3 text-muted">Hiện tại bảng Users chưa có bản ghi nào.</p>
                <a href="admin/add.php" class="btn btn-primary btn-sm px-3">+ Thêm nhân sự đầu tiên</a>
            </div>
        <?php else: ?>
            <!-- Grid responsive: 1 cột mobile (col-12), 2 cột tablet (col-sm-6), 3 cột desktop nhỏ (col-md-4), 4 cột desktop lớn (col-lg-3) -->
            <div class="row g-4">
                <?php foreach ($users as $user): ?>
                    <?php
                    // Kiểm tra file ảnh trong thư mục uploads/
                    $imageFile = $user['images'] ?? '';
                    $imagePath = 'uploads/' . $imageFile;
                    if (!empty($imageFile) && file_exists(__DIR__ . '/' . $imagePath)) {
                        $avatarSrc = htmlspecialchars($imagePath);
                    } else {
                        $avatarSrc = $defaultAvatar;
                    }
                    ?>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                        <div class="card user-card h-100 shadow-sm">
                            <img src="<?= $avatarSrc ?>" class="user-avatar" alt="Ảnh đại diện">
                            <div class="card-body">
                                <span class="badge bg-secondary mb-2">
                                    ID: #<?= htmlspecialchars((string)$user['admin_id']) ?>
                                </span>
                                <h6 class="card-title text-break mb-0 fw-semibold text-dark">
                                    <?= htmlspecialchars($user['admin_email']) ?>
                                </h6>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

