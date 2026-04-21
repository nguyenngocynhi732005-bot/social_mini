<?php
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/database_functions.php";

// Lấy danh sách kháng nghị (status = 'appealed')
$appeals = fetch_all("
    SELECT n.id as noti_id, n.message, n.created_at, u.ID as user_id, u.First_name, u.Last_name, u.Email 
    FROM admin_notifications n
    JOIN users u ON n.user_id = u.ID
    WHERE n.status = 'appealed'
    ORDER BY n.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Kháng nghị</title>
    <style>
        /* Sử dụng lại style từ manage_users.php của bạn */
        :root{ --sidebar:#23364a; --primary:#2563eb; --bg:#f4f6f8; --danger:#ef4444; --success:#22c55e; }
        body{ margin:0; font-family: system-ui, sans-serif; background:var(--bg); display:flex; }
        .content{ flex:1; padding:26px; }
        .card{ background:#fff; border-radius:14px; padding:20px; box-shadow: 0 10px 25px rgba(0,0,0,.05); }
        table{ width:100%; border-collapse:collapse; }
        th{ text-align:left; padding:12px; color:#64748b; font-size:13px; border-bottom:1px solid #eee; }
        td{ padding:15px 12px; border-bottom:1px solid #f8fafc; font-size:14px; }
        .btn-approve { background:#dcfce7; color:#166534; padding:6px 12px; border-radius:6px; text-decoration:none; font-weight:600; font-size:13px; }
        .btn-reject { background:#fee2e2; color:#991b1b; padding:6px 12px; border-radius:6px; text-decoration:none; font-weight:600; font-size:13px; margin-left:5px; }
    </style>
</head>
<body>
    <?php require_once "sidebar.php"; ?>
    <main class="content">
        <h1>⚖️ Danh sách Kháng nghị</h1>
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Người dùng</th>
                        <th>Nội dung vi phạm cũ</th>
                        <th>Ngày gửi</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($appeals)): ?>
                        <tr><td colspan="4" style="text-align:center;">Không có kháng nghị nào cần xử lý.</td></tr>
                    <?php endif; ?>
                    <?php foreach($appeals as $app): ?>
                    <tr>
                        <td>
                            <b><?= htmlspecialchars($app['Last_name'] . ' ' . $app['First_name']) ?></b><br>
                            <small><?= htmlspecialchars($app['Email']) ?></small>
                        </td>
                        <td style="font-style:italic; color:#64748b;">"<?= htmlspecialchars($app['message']) ?>"</td>
                        <td><?= $app['created_at'] ?></td>
                        <td>
                            <a href="handle_appeal_action.php?action=approve&noti_id=<?= $app['noti_id'] ?>&user_id=<?= $app['user_id'] ?>" 
                               class="btn-approve" onclick="return confirm('Chấp nhận kháng nghị và hoàn lại 15 điểm?')">✅ Chấp nhận</a>
                            
                            <a href="handle_appeal_action.php?action=reject&noti_id=<?= $app['noti_id'] ?>" 
                               class="btn-reject" onclick="return confirm('Bác bỏ kháng nghị này?')">❌ Bác bỏ</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>