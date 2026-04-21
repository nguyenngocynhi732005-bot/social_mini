<?php
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/database_functions.php";

// --- XỬ LÝ LOGIC XÓA BÀI VIẾT ---
if (isset($_GET['delete_id'])) {
    $post_id = (int)$_GET['delete_id'];
    
    // Xóa bình luận liên quan trước để tránh lỗi khóa ngoại (nếu có)
    if (table_exists('post_comments')) {
        execute_query("DELETE FROM post_comments WHERE post_id = ?", [$post_id]);
    } elseif (table_exists('comments')) {
        execute_query("DELETE FROM comments WHERE PostID = ?", [$post_id]);
    }

    // Xóa lượt thích liên quan
    if (table_exists('post_reactions')) {
        execute_query("DELETE FROM post_reactions WHERE post_id = ?", [$post_id]);
    } elseif (table_exists('likes')) {
        execute_query("DELETE FROM likes WHERE post_id = ?", [$post_id]);
    }

    // Xóa bài viết
    execute_query("DELETE FROM posts WHERE id = ?", [$post_id]);
    
    header("Location: manage_posts.php?msg=delete_success");
    exit();
}

// --- TRUY VẤN DANH SÁCH BÀI VIẾT (tương thích schema cũ/mới) ---
$sql = "SELECT posts.*, users.*
        FROM posts 
        JOIN users ON posts.user_id = users.ID 
        ORDER BY posts.created_at DESC";
$postList = fetch_all($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Bài viết</title>
    <style>
        .content { flex: 1; padding: 26px; }
        .card { background: #fff; border-radius: 14px; padding: 20px; box-shadow: 0 10px 25px rgba(0,0,0,.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; padding: 12px; color: #64748b; font-size: 13px; border-bottom: 1px solid #eee; }
        td { padding: 15px 12px; border-bottom: 1px solid #f8fafc; font-size: 14px; vertical-align: top; }
        
        .post-author { display: flex; align-items: center; gap: 10px; margin-bottom: 5px; }
        .author-avatar { width: 35px; height: 35px; border-radius: 50%; object-fit: cover; }
        .post-content { max-width: 450px; color: #334155; line-height: 1.5; }
        .post-img { width: 100px; height: 100px; border-radius: 8px; object-fit: cover; margin-top: 8px; border: 1px solid #eee; cursor: pointer; }
        
        .btn-delete { color: #ef4444; text-decoration: none; font-weight: 600; font-size: 13px; padding: 8px 12px; border-radius: 8px; background: #fef2f2; border: 1px solid #fee2e2; transition: 0.2s; }
        .btn-delete:hover { background: #fee2e2; }
        
        .share-badge { font-size: 11px; background: #e0f2fe; color: #0369a1; padding: 2px 8px; border-radius: 99px; font-weight: 600; margin-left: 5px; }
        .status-msg { padding: 15px; margin-bottom: 20px; border-radius: 10px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; font-weight: bold; }
    </style>
</head>
<body>

<div class="app" style="display: flex; min-height: 100vh; width: 100%;">
    <?php require_once "sidebar.php"; ?>

    <main class="content">
        <h1>Quản lý Bài viết</h1>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'delete_success'): ?>
            <div class="status-msg" id="status-msg">✅ Đã xóa bài viết và các tương tác liên quan!</div>
            <script>setTimeout(() => { document.getElementById('status-msg').style.display='none'; window.history.replaceState({}, '', 'manage_posts.php'); }, 3000);</script>
        <?php endif; ?>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th style="width: 25%;">Tác giả & Thời gian</th>
                        <th style="width: 45%;">Nội dung bài viết</th>
                        <th style="width: 15%;">Hình ảnh</th>
                        <th style="width: 15%;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($postList)): ?>
                        <tr><td colspan="4" style="text-align:center; padding:40px; color:#94a3b8;">Chưa có bài viết nào trong hệ thống.</td></tr>
                    <?php else: foreach($postList as $post): ?>
                    <tr>
                        <td>
                            <div class="post-author">
                                <img src="<?= admin_avatar_url($post) ?>" class="author-avatar" alt="avatar">
                                <b><?= htmlspecialchars(admin_user_name($post)) ?></b>
                            </div>
                            <small style="color:#94a3b8;">
                                📅 <?= date('d/m/Y H:i', strtotime($post['created_at'])) ?>
                                <?php if (!empty($post['shared_from']) || !empty($post['shared_from_id'])): ?>
                                    <span class="share-badge">Bài chia sẻ</span>
                                <?php endif; ?>
                            </small>
                        </td>
                        <td>
                            <div class="post-content">
                                <?php 
                                    $content = (string) admin_array_value($post, ['content'], '');
                                    if ($content == "Đã chia sẻ bài viết của người khác") {
                                        echo "<i style='color:#64748b;'> (Nội dung mặc định khi chia sẻ) </i>";
                                    } else {
                                        echo nl2br(htmlspecialchars(mb_strimwidth($content, 0, 250, "...")));
                                    }
                                ?>
                            </div>
                        </td>
                        <td>
                            <?php $postMediaUrl = admin_post_media_url($post); ?>
                            <?php if($postMediaUrl): ?>
                                <img src="<?= htmlspecialchars($postMediaUrl) ?>" class="post-img" onclick="window.open(this.src)">
                            <?php else: ?>
                                <span style="color:#cbd5e1; font-size:12px;">Không có ảnh</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="?delete_id=<?= $post['id'] ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Bạn có chắc chắn muốn XÓA bài viết này vĩnh viễn?')">
                               🗑️ Xóa bài
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

</body>
</html>