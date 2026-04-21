<?php
// admin/process_warning.php
session_start();
require_once __DIR__ . "/database_functions.php";

// 1. Kiểm tra quyền Admin: Chỉ cho phép người dùng có is_admin = 1 truy cập
if (!isset($_SESSION['is_admin']) || (int)$_SESSION['is_admin'] !== 1) {
    die("Bạn không có quyền thực hiện hành động này.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $message = isset($_POST['admin_message']) ? trim($_POST['admin_message']) : ''; 
    $admin_id = $_SESSION['user_id'];

    if ($user_id > 0 && !empty($message)) {
        // Bước A: Lưu thông báo cảnh báo vào bảng admin_notifications
        $sql = "INSERT INTO admin_notifications (user_id, admin_id, message, is_read, created_at) 
                VALUES (?, ?, ?, 0, NOW())";
        $result = execute_query($sql, [$user_id, $admin_id, $message]);

        if ($result) {
            // Bước B: Kiểm tra thông tin người bị cảnh báo
            $target = fetch_one("SELECT is_admin FROM users WHERE ID = ?", [$user_id]);
            
            // BẢO VỆ ADMIN: Chỉ trừ điểm và khóa nếu đối tượng KHÔNG PHẢI là Admin
            if ($target && (int)$target['is_admin'] === 0) {
                
                // Bước C: Trừ 15 điểm uy tín của người dùng thường
                execute_query("UPDATE users SET Reputation = Reputation - 15 WHERE ID = ?", [$user_id]);

                // Bước D: Kiểm tra điểm số để thực hiện khóa tự động
                $u = fetch_one("SELECT Reputation FROM users WHERE ID = ?", [$user_id]);
                if ($u && (int)$u['Reputation'] <= 0) {
                    execute_query("UPDATE users SET Status = 0 WHERE ID = ?", [$user_id]);
                }
            }
            // Nếu $target['is_admin'] == 1, hệ thống sẽ bỏ qua việc trừ điểm và khóa

            header("Location: manage_users.php?msg=warn_success");
            exit();
        } else {
            die("Lỗi database: Không thể lưu thông báo.");
        }
    } else {
        header("Location: manage_users.php?msg=warn_error");
        exit();
    }
} else {
    header("Location: manage_users.php");
    exit();
}