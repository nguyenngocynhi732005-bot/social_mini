<?php
// login_admin_process.php
session_start();
require_once __DIR__ . "/database_functions.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login_admin.php");
    exit();
}

$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    header("Location: login_admin.php?error=Vui lòng nhập đầy đủ Email và Mật khẩu");
    exit();
}

$user = fetch_one("SELECT ID, Email, Password, is_admin, Status FROM users WHERE Email = ? LIMIT 1", [$email]);

if (!$user) {
    header("Location: login_admin.php?error=Email hoặc mật khẩu không đúng");
    exit();
}

// Kiểm tra mật khẩu
if ($password !== $user['Password']) {
    header("Location: login_admin.php?error=Email hoặc mật khẩu không đúng");
    exit();
}

// --- BÂY GIỜ KIỂM TRA STATUS MỚI CHÍNH XÁC ---
if ((int)$user['Status'] === 0) {
    header("Location: login_admin.php?error=Tài khoản của bạn đã bị khóa do vi phạm tiêu chuẩn cộng đồng.");
    exit();
}

// Kiểm tra quyền admin
if ((int)$user['is_admin'] === 1) {
    $_SESSION['user_id']  = (int)$user['ID'];
    $_SESSION['is_admin'] = 1;
    $_SESSION['email']    = $user['Email'];
    
    header("Location: manage_users.php"); 
    exit();
} else {
    header("Location: login_admin.php?error=Tài khoản của bạn không có quyền quản trị");
    exit();
}