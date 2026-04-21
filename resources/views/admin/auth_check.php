<?php
// admin/auth_check.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/database_functions.php"; // Cần file này để truy vấn DB

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// 1. Kiểm tra session cơ bản
if (!isset($_SESSION['is_admin']) || (int)$_SESSION['is_admin'] !== 1 || !isset($_SESSION['user_id'])) {
    header("Location: forbidden.php");
    exit();
}

// 2. KIỂM TRA TRẠNG THÁI THỰC TẾ TRONG DATABASE
$admin_id = $_SESSION['user_id'];
$check_admin = fetch_one("SELECT Status, is_admin FROM users WHERE ID = ?", [$admin_id]);

// Nếu tài khoản không tồn tại, bị tước quyền admin, hoặc bị khóa (Status = 0)
if (!$check_admin || (int)$check_admin['is_admin'] !== 1 || (int)$check_admin['Status'] === 0) {
    // Hủy session để buộc đăng nhập lại
    session_unset();
    session_destroy();
    
    // Chuyển hướng về trang login kèm thông báo lỗi
    $error_msg = urlencode("Tài khoản quản trị của bạn đã bị khóa hoặc không đủ quyền hạn.");
    header("Location: login_admin.php?error=" . $error_msg);
    exit();
}