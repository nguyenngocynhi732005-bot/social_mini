<?php
require_once "auth_check.php";

// Kiểm tra file database
$functions_path = __DIR__ . "/database_functions.php";
if (file_exists($functions_path)) {
    require_once $functions_path;
} else {
    die("Lỗi: Không tìm thấy file database_functions.php");
}

// Lấy thông tin admin hiện tại
$current_admin_id = $_SESSION['user_id'];

// --- XỬ LÝ LOGIC (Giai đoạn 2 & 3) ---

// 1. Giai đoạn 2: Xử lý Khóa/Mở khóa người dùng
if (isset($_GET['toggle_status'])) {
    $id = (int)$_GET['toggle_status'];
    $current_status = (int)$_GET['s'];
    $new_status = ($current_status == 1) ? 0 : 1;
    
    // Ngăn admin tự khóa chính mình
    if ($id != $current_admin_id) {
        execute_query("UPDATE users SET Status = ? WHERE ID = ?", [$new_status, $id]);
    }
    header("Location: manage_users.php?msg=status_success");
    exit();
}

// 2. Giai đoạn 3: Xử lý Xóa người dùng vĩnh viễn
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    if ($id != $current_admin_id) {
        execute_query("DELETE FROM users WHERE ID = ?", [$id]);
    }
    header("Location: manage_users.php?msg=delete_success");
    exit();
}

// Lấy danh sách thành viên (Chỉ lấy is_admin = 0 để tách biệt Admin và User)
$userList = fetch_all("SELECT * FROM users WHERE is_admin = 0 ORDER BY ID DESC");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý thành viên</title>
    <style>
        :root{ --sidebar:#23364a; --primary:#2563eb; --bg:#f4f6f8; --danger:#ef4444; --success:#22c55e; }
        body{ margin:0; font-family: system-ui, sans-serif; background:var(--bg); display:flex; }
        .content{ flex:1; padding:26px; }
        .card{ background:#fff; border-radius:14px; padding:20px; box-shadow: 0 10px 25px rgba(0,0,0,.05); }
        table{ width:100%; border-collapse:collapse; }
        th{ text-align:left; padding:12px; color:#64748b; font-size:13px; border-bottom:1px solid #eee; }
        td{ padding:15px 12px; border-bottom:1px solid #f8fafc; font-size:14px; }
        .user-info{ display:flex; align-items:center; gap:10px; }
        .avatar{ width:40px; height:40px; border-radius:50%; object-fit:cover; }
        .btn-warn{ background:#fffbeb; color:#b45309; border:1px solid #fde68a; padding:6px 12px; border-radius:6px; cursor:pointer; font-weight:600; }
        .btn-status{ text-decoration:none; padding:6px 12px; border-radius:8px; font-size:13px; font-weight:600; margin-left:8px; display:inline-block; border:1px solid; }
        
        /* Animation cho Toast */
        @keyframes fadeOut { 0% { opacity: 1; } 80% { opacity: 1; } 100% { opacity: 0; display: none; } }
    </style>
</head>
<body>

<div class="app" style="display: flex; min-height: 100vh; width: 100%;">
    <?php require_once "sidebar.php"; ?>

    <main class="content" style="flex: 1; padding: 26px;">
    <h1>Quản lý thành viên</h1>

    <?php if (isset($_GET['msg'])): ?>
        <div id="status-toast" style="padding: 15px; margin-bottom: 20px; border-radius: 10px; font-weight: bold; border: 2px solid; 
            <?php 
                if($_GET['msg'] == 'delete_success') echo 'background:#fee2e2; color:#991b1b; border-color:#fecaca;'; 
                else echo 'background:#dcfce7; color:#166534; border-color:#bbf7d0;';
            ?>">
            <?php 
                if($_GET['msg'] == 'warn_success') echo "✅ Đã gửi cảnh báo thành công!";
                if($_GET['msg'] == 'status_success') echo "✅ Cập nhật trạng thái thành công!";
                if($_GET['msg'] == 'delete_success') echo "🗑️ Đã xóa người dùng vĩnh viễn!";
            ?>
        </div>
        
        <script>
            // Lệnh xóa tham số trên URL để khi bạn F5 trang, thông báo sẽ biến mất
            setTimeout(() => {
                const toast = document.getElementById('status-toast');
                if(toast) toast.style.display = 'none';
                window.history.replaceState({}, '', 'manage_users.php');
            }, 3000);
        </script>
    <?php endif; ?>

        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Người dùng</th>
                        <th>Email</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($userList as $user): ?>
                    <tr>
                        <td>
                            <div class="user-info">
                                <img src="<?= admin_avatar_url($user) ?>" class="avatar" alt="avatar">
                                <div>
                                    <a href="user_details.php?id=<?= $user['ID'] ?>" style="text-decoration:none; color:inherit;">
                                        <b><?= htmlspecialchars(admin_user_name($user)) ?></b>
                                    </a>
                                    <br><small style="color:#94a3b8">ID: #<?= $user['ID'] ?></small>
                                </div>
                            </div>
                        </td>
                        <td><?= htmlspecialchars((string) admin_array_value($user, ['email', 'Email'], 'N/A')) ?></td>
                        <td>
                            <span style="color:<?= $user['Status'] == 1 ? 'var(--success)' : 'var(--danger)' ?>; font-weight:600;">
                                ● <?= $user['Status'] == 1 ? 'Hoạt động' : 'Bị khóa' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn-warn" onclick="openWarningModal(<?= $user['ID'] ?>, '<?= htmlspecialchars(admin_user_name($user)) ?>')">
                                ⚠️ Cảnh báo
                            </button>

                            <a href="?toggle_status=<?= $user['ID'] ?>&s=<?= $user['Status'] ?>" 
                               class="btn-status"
                               style="background: <?= $user['Status'] == 1 ? '#fff1f0' : '#f6ffed' ?>; 
                                      color: <?= $user['Status'] == 1 ? '#cf1322' : '#389e0d' ?>;
                                      border-color: <?= $user['Status'] == 1 ? '#ffa39e' : '#b7eb8f' ?>;">
                               <?= $user['Status'] == 1 ? '🔒 Khóa' : '🔓 Mở khóa' ?>
                            </a>

                            <a href="?delete_id=<?= $user['ID'] ?>" 
                               onclick="return confirm('XÓA VĨNH VIỄN người dùng này?')" 
                               style="color:#94a3b8; margin-left:12px; text-decoration:none; font-size:13px;">Xóa</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div id="warningModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999;">
    <div style="background:white; width:450px; margin:100px auto; padding:25px; border-radius:15px; box-shadow:0 10px 30px rgba(0,0,0,0.2);">
        <h3 style="margin-top:0;">⚠️ Gửi cảnh báo vi phạm</h3>
        <p style="font-size:14px; color:#666;">Người dùng: <span id="targetUserName" style="font-weight:bold;"></span></p>
        <form action="process_warning.php" method="POST">
            <input type="hidden" name="user_id" id="targetUserId">
            <label style="font-size:13px; font-weight:600;">Lý do vi phạm:</label>
            <select name="reason" id="violationReason" onchange="updateMessage()" style="width:100%; padding:10px; margin:10px 0; border-radius:8px; border:1px solid #ddd;">
                <option value="Spam">Spam/Nội dung rác</option>
                <option value="Ngôn từ gây thù ghét">Ngôn từ gây thù ghét/Xúc phạm</option>
                <option value="Nội dung không phù hợp">Nội dung không phù hợp</option>
                <option value="Tài khoản giả mạo">Tài khoản giả mạo</option>
                <option value="Khác">Lý do khác...</option>
            </select>
            <textarea name="admin_message" id="adminMessage" style="width:100%; height:100px; padding:10px; margin-bottom:15px; border-radius:8px; border:1px solid #ddd;"></textarea>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeModal()" style="padding:10px 20px; border:none; border-radius:8px; cursor:pointer;">Hủy</button>
                <button type="submit" style="padding:10px 20px; background:var(--primary); color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Gửi</button>
            </div>
        </form>
    </div>
</div>

<script>
const templates = {
    "Spam": "Chúng tôi nhận thấy bạn đang đăng quá nhiều nội dung rác. Vui lòng dừng lại.",
    "Ngôn từ gây thù ghét": "Bình luận/Bài viết của bạn vi phạm quy tắc về ngôn từ xúc phạm.",
    "Nội dung không phù hợp": "Nội dung bạn đăng tải không phù hợp với tiêu chuẩn cộng đồng.",
    "Tài khoản giả mạo": "Tài khoản của bạn đang bị nghi ngờ là giả mạo người khác.",
    "Khác": ""
};
function openWarningModal(id, name) {
    document.getElementById('targetUserId').value = id;
    document.getElementById('targetUserName').innerText = name;
    document.getElementById('warningModal').style.display = 'block';
    updateMessage();
}
function updateMessage() {
    const reason = document.getElementById('violationReason').value;
    document.getElementById('adminMessage').value = templates[reason];
}
function closeModal() { document.getElementById('warningModal').style.display = 'none'; }
</script>

</body>
</html>