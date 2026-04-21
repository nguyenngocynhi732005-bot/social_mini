<?php
// 1. Khởi tạo session ngay lập tức để tránh auth_check bị lỗi
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Kiểm tra quyền admin (Đoạn này giúp dừng vòng lặp redirect nếu session lỗi)
if (!isset($_SESSION['is_admin']) || (int)$_SESSION['is_admin'] !== 1) {
    die("Lỗi: Bạn không có quyền truy cập. Hãy đăng nhập lại.");
}

require_once __DIR__ . "/database_functions.php";

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 3. Lấy thông tin user
$user = fetch_one("SELECT * FROM users WHERE ID = ?", [$user_id]);

if (!$user) {
    die("<div style='padding:50px; text-align:center;'><h2>Không tìm thấy người dùng!</h2><a href='manage_users.php'>Quay lại</a></div>");
}

$avatarPath = admin_avatar_url($user);

// 5. Thống kê
$count_posts = fetch_one("SELECT COUNT(*) as total FROM posts WHERE user_id = ?", [$user_id])['total'] ?? 0;
if (table_exists('post_comments')) {
    $count_comments = fetch_one("SELECT COUNT(*) as total FROM post_comments WHERE user_id = ?", [$user_id])['total'] ?? 0;
} else {
    $count_comments = fetch_one("SELECT COUNT(*) as total FROM comments WHERE UserID = ?", [$user_id])['total'] ?? 0;
}

// 6. Lấy hoạt động
$user_posts = fetch_all("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$user_id]);
if (table_exists('post_comments')) {
    $user_comments = fetch_all("SELECT c.content as comment_content, c.created_at as comment_time, p.content as post_content
                                FROM post_comments c
                                JOIN posts p ON c.post_id = p.id
                                WHERE c.user_id = ?
                                ORDER BY c.created_at DESC LIMIT 5", [$user_id]);
} else {
    $user_comments = fetch_all("SELECT c.Content as comment_content, c.Timestamp as comment_time, p.content as post_content
                                FROM comments c
                                JOIN posts p ON c.PostID = p.ID
                                WHERE c.UserID = ?
                                ORDER BY c.Timestamp DESC LIMIT 5", [$user_id]);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hồ sơ: <?= htmlspecialchars(admin_user_name($user)) ?></title>
    <style>
        :root { --primary: #1877f2; --bg: #f0f2f5; }
        body { background: var(--bg); font-family: 'Segoe UI', sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        .btn-back { display: inline-block; text-decoration: none; background: #fff; padding: 8px 15px; border-radius: 8px; color: #333; font-weight: 600; box-shadow: 0 1px 2px rgba(0,0,0,0.1); margin-bottom: 15px; }
        
        /* Header Profile */
        .profile-card { background: white; border-radius: 12px; padding: 25px; display: flex; align-items: center; gap: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .avatar { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary); }
        
        .grid { display: grid; grid-template-columns: 350px 1fr; gap: 20px; margin-top: 20px; }
        .box { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .box h3 { margin-top: 0; color: var(--primary); border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .info-item { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
    </style>
</head>
<body>

<div class="container">
    <a href="manage_users.php" class="btn-back">← Quay lại trang Quản lý</a>

    <div class="profile-card">
        <img src="<?= $avatarPath ?>" class="avatar" alt="Avatar">
        <div>
            <h1 style="margin:0;"><?= htmlspecialchars(admin_user_name($user)) ?></h1>
            <p style="color:#65676b; margin:5px 0;"><?= htmlspecialchars((string) admin_array_value($user, ['email', 'Email'], 'N/A')) ?></p>
            <span style="font-size:12px; padding:3px 8px; background:#e7f3ff; color:var(--primary); border-radius:5px; font-weight:bold;">
                ID: #<?= $user['ID'] ?>
            </span>
        </div>
    </div>

    <div class="grid">
        <div class="sidebar">
            <div class="box">
                <h3>Thông tin chi tiết</h3>
                <div class="info-item"><b>Giới tính:</b> <span><?= htmlspecialchars((string) admin_array_value($user, ['gender', 'Gender'], 'N/A')) ?></span></div>
                <div class="info-item"><b>Ngày sinh:</b> <span><?php $bd = (string) admin_array_value($user, ['birth_date', 'BirthDate']); echo $bd !== '' ? date('d/m/Y', strtotime($bd)) : 'N/A'; ?></span></div>
                <div class="info-item"><b>Số điện thoại:</b> <span><?= htmlspecialchars((string) admin_array_value($user, ['phone', 'Phone'], 'N/A')) ?></span></div>
                <div class="info-item"><b>Uy tín:</b> <span><?= (int) admin_array_value($user, ['Reputation', 'reputation'], 0) ?>/100</span></div>
            </div>
            
            <div class="box" style="margin-top:20px;">
                <h3>Thống kê</h3>
                <p>Bài viết: <b><?= $count_posts ?></b></p>
                <p>Bình luận: <b><?= $count_comments ?></b></p>
            </div>
        </div>

        <div class="content">
            <div class="box">
                <h3>Bài đăng gần đây</h3>
                <?php if(empty($user_posts)): ?>
                    <p>Không có bài đăng nào.</p>
                <?php else: foreach($user_posts as $post): ?>
                    <div style="border-bottom:1px solid #f0f2f5; padding:10px 0;">
                        <p style="margin:0; font-size:14px;"><?= htmlspecialchars(mb_strimwidth($post['content'], 0, 100, "...")) ?></p>
                        <small style="color:#888;"><?= $post['created_at'] ?></small>
                    </div>
                <?php endforeach; endif; ?>
                <h3>Bình luận gần đây</h3>
                <?php if(empty($user_comments)): ?>
                    <p style="color:var(--sub); font-size:14px;">Chưa có bình luận.</p>
                <?php else: foreach($user_comments as $cmt): ?>
                    <div class="activity-item">
                        <p><b>"<?= htmlspecialchars((string) admin_array_value($cmt, ['comment_content', 'Content'])) ?>"</b></p>
                        <small>Tại bài: <?= htmlspecialchars(mb_strimwidth($cmt['post_content'], 0, 60, "...")) ?></small>
                    </div>
                <?php endforeach; endif; ?>
        </div>
    </div>
</div>

</body>
</html>