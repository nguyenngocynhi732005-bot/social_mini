<?php
session_start();

// Không cần config_mini trong luồng admin Laravel bridge.

// 2. Kiểm tra ID người dùng (Trong hệ thống Admin của bạn dùng 'user_id' thay vì 'unique_id')
if (isset($_SESSION['user_id'])) {
    $logout_id = $_SESSION['user_id'];
    $status = "Offline now";
    
    // Cập nhật trạng thái vào DB (Nếu bảng users của bạn có cột online_status)
    // mysqli_query($conn, "UPDATE users SET online_status = '{$status}' WHERE ID = '{$logout_id}'");
}

// 3. QUAN TRỌNG: Xóa sạch toàn bộ Session để không bị tự động đăng nhập lại
$_SESSION = array(); // Xóa mảng session
session_unset();     // Giải phóng biến session
session_destroy();   // Hủy session trên server

// 4. Xóa Cookie session trong trình duyệt (để chắc chắn 100%)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 5. Ngăn trình duyệt lưu cache (tránh việc nhấn nút Back quay lại được trang Admin)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// 6. CHUYỂN VỀ TRANG login_admin.php
header("Location: login_admin.php");
exit();