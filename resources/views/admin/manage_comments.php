<?php
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/database_functions.php";

// --- XỬ LÝ LOGIC XÓA BÌNH LUẬN ---
if (isset($_GET['delete_id'])) {
    $cmt_id = (int)$_GET['delete_id'];
    
    // 1. Xóa các bình luận con (reply) trước
    if (table_exists('post_comments')) {
        execute_query("DELETE FROM post_comments WHERE parent_id = ?", [$cmt_id]);
    } else {
        execute_query("DELETE FROM comments WHERE parent_id = ?", [$cmt_id]);
    }
    
    // 2. Xóa chính bình luận đó
    if (table_exists('post_comments')) {
        execute_query("DELETE FROM post_comments WHERE id = ?", [$cmt_id]);
    } else {
        execute_query("DELETE FROM comments WHERE ID = ?", [$cmt_id]);
    }
    
    header("Location: manage_comments.php?msg=delete_success");
    exit();
}

// --- TRUY VẤN DANH SÁCH BÌNH LUẬN (tương thích schema cũ/mới) ---
if (table_exists('post_comments')) {
    $sql = "SELECT c.*, u.*, p.content as post_content, p.media_path as post_image,
             c.content as comment_content, c.created_at as comment_created_at
         FROM post_comments c
         JOIN users u ON c.user_id = u.ID
         JOIN posts p ON c.post_id = p.id
         ORDER BY c.created_at DESC";
} else {
    $sql = "SELECT c.*, u.*, p.content as post_content, p.image_url as post_image,
             c.Content as comment_content, c.Timestamp as comment_created_at
         FROM comments c
         JOIN users u ON c.UserID = u.ID
         JOIN posts p ON c.PostID = p.id
         ORDER BY c.Timestamp DESC";
}
$commentList = fetch_all($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Bình luận</title>
    <style>
        .content { flex: 1; padding: 26px; }
        .card { background: #fff; border-radius: 14px; padding: 20px; box-shadow: 0 10px 25px rgba(0,0,0,.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; padding: 12px; color: #64748b; font-size: 13px; border-bottom: 1px solid #eee; }
        td { padding: 15px 12px; border-bottom: 1px solid #f8fafc; font-size: 14px; vertical-align: top; }
        
        .user-info { display: flex; align-items: center; gap: 10px; }
        .avatar-sm { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; }
        
        .comment-text { color: #1e293b; font-weight: 500; background: #f1f5f9; padding: 8px 12px; border-radius: 8px; display: inline-block; margin-top: 5px; line-height: 1.4; }
        .post-ref { font-size: 12px; color: #64748b; font-style: italic; display: block; margin-top: 8px; border-left: 2px solid #cbd5e1; padding-left: 8px; }
        
        /* Style cho ảnh bài viết trong bình luận */
        .post-thumbnail { width: 60px; height: 60px; border-radius: 6px; object-fit: cover; border: 1px solid #e2e8f0; cursor: pointer; }
        .no-image { font-size: 11px; color: #cbd5e1; }

        .btn-delete { color: #ef4444; text-decoration: none; font-weight: 600; font-size: 12px; padding: 6px 10px; border-radius: 6px; background: #fef2f2; border: 1px solid #fee2e2; }
        .btn-delete:hover { background: #fee2e2; }
        .reply-badge { font-size: 10px; background: #fef3c7; color: #92400e; padding: 1px 6px; border-radius: 4px; margin-left: 5px; vertical-align: middle; }
    </style>
</head>
<body>

<div class="app" style="display: flex; min-height: 100vh; width: 100%;">
    <?php require_once "sidebar.php"; ?>

    <main class="content">
        <h1>Quản lý Bình luận</h1>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'delete_success'): ?>
            <div style="padding: 15px; margin-bottom: 20px; border-radius: 10px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; font-weight: bold;">
                ✅ Đã xóa bình luận thành công!
            </div>
            <script>setTimeout(() => { window.history.replaceState({}, '', 'manage_comments.php'); }, 3000);</script>
        <?php endif; ?>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th style="width: 18%;">Người bình luận</th>
                        <th style="width: 42%;">Nội dung bình luận</th>
                        <th style="width: 15%;">Ảnh bài viết</th>
                        <th style="width: 13%;">Thời gian</th>
                        <th style="width: 12%;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($commentList)): ?>
                        <tr><td colspan="5" style="text-align:center; padding:30px; color:#94a3b8;">Chưa có bình luận nào.</td></tr>
                    <?php else: foreach($commentList as $cmt): ?>
                    <tr>
                        <td>
                            <div class="user-info">
                                <img src="<?= admin_avatar_url($cmt) ?>" class="avatar-sm" alt="avatar">
                                <b><?= htmlspecialchars(admin_user_name($cmt)) ?></b>
                            </div>
                        </td>
                        <td>
                            <div class="comment-text">
                                <?= htmlspecialchars((string) admin_array_value($cmt, ['comment_content', 'content', 'Content'])) ?>
                                <?php if (!empty($cmt['parent_id'])): ?>
                                    <span class="reply-badge">Phản hồi</span>
                                <?php endif; ?>
                            </div>
                            <div class="post-ref">
                                📄 Gốc: <?= htmlspecialchars(mb_strimwidth($cmt['post_content'], 0, 80, "...")) ?>
                            </div>
                        </td>
                        <td>
                            <?php $commentPostImage = admin_post_media_url(['post_image' => admin_array_value($cmt, ['post_image'])]); ?>
                            <?php if($commentPostImage): ?>
                                <img src="<?= htmlspecialchars($commentPostImage) ?>"
                                     class="post-thumbnail" 
                                     title="Xem bài viết gốc"
                                     onclick="window.open(this.src)">
                            <?php else: ?>
                                <span class="no-image">Bài viết không ảnh</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: #64748b; font-size: 12px; line-height: 1.4;">
                            <?php $commentTime = (string) admin_array_value($cmt, ['comment_created_at', 'created_at', 'Timestamp']); ?>
                            <?= date('d/m/Y', strtotime($commentTime)) ?><br>
                            <?= date('H:i', strtotime($commentTime)) ?>
                        </td>
                        <td>
                            <a href="?delete_id=<?= (int) admin_array_value($cmt, ['id', 'ID'], 0) ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Bạn có chắc muốn xóa bình luận này?')">
                               🗑️ Xóa
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