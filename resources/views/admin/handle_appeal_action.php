<?php
session_start();
require_once __DIR__ . "/database_functions.php";

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) die("Access denied");

$action = $_GET['action'] ?? '';
$noti_id = (int)($_GET['noti_id'] ?? 0);
$user_id = (int)($_GET['user_id'] ?? 0);

if ($action === 'approve' && $noti_id > 0 && $user_id > 0) {
    // 1. Hoàn lại 15 điểm uy tín
    execute_query("UPDATE users SET Reputation = Reputation + 15 WHERE ID = ?", [$user_id]);
    
    // 2. Kiểm tra nếu điểm > 0 thì tự động mở khóa (Status = 1)
    $user = fetch_one("SELECT Reputation FROM users WHERE ID = ?", [$user_id]);
    if ($user && $user['Reputation'] > 0) {
        execute_query("UPDATE users SET Status = 1 WHERE ID = ?", [$user_id]);
    }
    
    // 3. Xóa thông báo này để nó biến mất khỏi danh sách
    execute_query("DELETE FROM admin_notifications WHERE id = ?", [$noti_id]);
    
    header("Location: manage_appeals.php?msg=approved");
} 
elseif ($action === 'reject' && $noti_id > 0) {
    // Nếu bác bỏ, chỉ đơn giản là đổi trạng thái hoặc xóa thông báo để Admin không phải thấy nữa
    execute_query("UPDATE admin_notifications SET status = 'accepted' WHERE id = ?", [$noti_id]);
    header("Location: manage_appeals.php?msg=rejected");
}
exit();