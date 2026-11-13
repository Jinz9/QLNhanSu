<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Lấy danh sách Users từ CSDL
$stmt = $pdo->prepare('SELECT admin_id, admin_email, images FROM Users ORDER BY admin_id DESC');
$stmt->execute();
$users = $stmt->fetchAll();

// Ảnh mặc định khi bản ghi chưa có ảnh
$defaultAvatar = "data:image/svg+xml;utf8," . rawurlencode(
    '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">'
    . '<rect width="100%" height="100%" fill="#e9ecef"/>'
    . '<circle cx="60" cy="45" r="20" fill="#adb5bd"/>'
    . '<path d="M25 100 C25 72, 95 72, 95 100 Z" fill="#adb5bd"/>'
    . '</svg>'
);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản trị nhân sự - QLNhanSu</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .table-avatar {
            width: 56px;
            height: 56px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <!-- Thanh điều hướng (Navbar) -->
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
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">Quản lý người dùng (Users)</h1>
                <p class="text-muted mb-0">Phân hệ quản trị thêm, sửa, xóa dữ liệu nhân sự</p>
            </div>
            <a href="add.php" class="btn btn-primary fw-semibold mt-2 mt-sm-0">
                + Thêm người dùng mới
            </a>
        </div>

        <!-- Thông báo thành công / lỗi từ Session -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <?= htmlspecialchars($_SESSION['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Đóng"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Bảng danh sách Users -->
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4" style="width: 100px;">Ảnh</th>
                                <th style="width: 100px;">ID</th>
                                <th>Email</th>
                                <th class="text-end pe-4" style="width: 180px;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        Chưa có dữ liệu người dùng. Hãy nhấn <strong>"+ Thêm người dùng mới"</strong> để bắt đầu.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $user): ?>
                                    <?php
                                    $imageFile = $user['images'] ?? '';
                                    $imagePath = '../uploads/' . $imageFile;
                                    if (!empty($imageFile) && file_exists(__DIR__ . '/../uploads/' . $imageFile)) {
                                        $avatarSrc = htmlspecialchars($imagePath);
                                    } else {
                                        $avatarSrc = $defaultAvatar;
                                    }
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <img src="<?= $avatarSrc ?>" class="table-avatar" alt="Avatar">
                                        </td>
                                        <td class="fw-semibold text-secondary">
                                            #<?= htmlspecialchars((string)$user['admin_id']) ?>
                                        </td>
                                        <td class="text-break fw-medium">
                                            <?= htmlspecialchars($user['admin_email']) ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="edit.php?id=<?= urlencode((string)$user['admin_id']) ?>" class="btn btn-sm btn-outline-primary me-1">
                                                Sửa
                                            </a>
                                            <form action="delete.php" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng #<?= htmlspecialchars((string)$user['admin_id']) ?> (<?= htmlspecialchars(addslashes($user['admin_email'])) ?>) không?');">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars((string)$user['admin_id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    Xóa
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

