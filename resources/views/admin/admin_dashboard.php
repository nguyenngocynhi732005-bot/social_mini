<?php
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/database_functions.php";

$stats = fetch_all("SELECT COUNT(*) AS total FROM users");
$totalUsers = $stats[0]['total'];

$userList = fetch_all("
  SELECT * 
    FROM users 
    ORDER BY ID DESC 
    LIMIT 10
");?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - Quản trị hệ thống</title>
  <style>
    /* CSS NỘI DUNG GỐC */
    .content{ flex:1; padding:26px; }
    .header{ display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:16px; }
    .header h1{ margin:0; font-size:32px; line-height:1.15; }
    .sub{ margin:6px 0 0; color:var(--muted); }
    .grid{ display:grid; grid-template-columns: 360px 1fr; gap:18px; align-items:start; margin-top:16px; }
    .card{ background:var(--card); border:1px solid var(--line); border-radius:var(--radius); box-shadow:var(--shadow); padding:16px; }
    .stat{ display:flex; align-items:center; justify-content:space-between; gap:16px; padding:14px; border:1px dashed var(--line); border-radius:12px; background:#fbfdff; }
    .stat .label{ color:var(--muted); font-size:14px; }
    .stat .value{ font-size:28px; font-weight:800; color:var(--primary); }
    .card h3{ margin:0 0 12px; font-size:18px; }
    
    /* TABLE GỐC */
    table{ width:100%; border-collapse:separate; border-spacing:0; overflow:hidden; border-radius:12px; border:1px solid var(--line); background:#fff; }
    thead th{ text-align:left; font-size:13px; letter-spacing:.2px; color:#334155; background:#f8fafc; padding:12px 12px; border-bottom:1px solid var(--line); }
    tbody td{ padding:12px 12px; border-bottom:1px solid var(--line); color:#0f172a; font-size:14px; }
    tbody tr:hover td{ background:#f8fafc; }
    tbody tr:last-child td{ border-bottom:none; }
    .role-badge{ display:inline-flex; align-items:center; padding:6px 10px; border-radius:999px; font-size:12px; font-weight:700; border:1px solid var(--line); background:#f1f5f9; color:#334155; }
    .role-badge.admin{ background:rgba(37,99,235,.10); border-color:rgba(37,99,235,.20); color:#1d4ed8; }
    @media (max-width: 980px){ .grid{ grid-template-columns: 1fr; } }
  </style>
</head>
<body>
  <div class="app">
    <?php include "sidebar.php"; ?>
    <main class="content">
      <div class="header">
        <div>
          <h1>Bảng điều khiển Quản trị</h1>
          <p class="sub">Tổng quan</p>
        </div>
      </div>

      <div class="grid">
        <section class="card">
          <h3>Thống kê</h3>
          <div class="stat">
            <div>
              <div class="label">Người dùng</div>
              <div class="sub" style="margin:6px 0 0;">Tổng số tài khoản</div>
            </div>
            <div class="value"><?php echo (int)$totalUsers; ?></div>
          </div>
        </section>

        <section class="card">
          <h3>Danh sách người dùng</h3>
          <table>
            <thead>
              <tr>
                <th style="width:80px;">ID</th>
                <th style="width:70px;">Avatar</th>
                <th>Email</th>
                <th>SĐT</th>
                <th>Họ</th>
                <th>Tên</th>
                <th style="width:160px;">Vai trò</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($userList as $user): ?>
                <tr>
                  <td><?php echo (int)$user['ID']; ?></td>
                  <td><img src="<?= admin_avatar_url($user) ?>" alt="avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;"></td>
                  <td><?php echo htmlspecialchars((string) admin_array_value($user, ['email', 'Email'], 'N/A')); ?></td>
                  <td><?php echo htmlspecialchars((string) admin_array_value($user, ['phone', 'Phone'], 'N/A')); ?></td>
                  <td><?php echo htmlspecialchars((string) admin_array_value($user, ['last_name', 'Last_name'], '')); ?></td>
                  <td><?php echo htmlspecialchars((string) admin_array_value($user, ['first_name', 'First_name'], '')); ?></td>
                  <td>
                    <?php if ((int)$user['is_admin'] === 1): ?>
                      <span class="role-badge admin">Quản trị viên</span>
                    <?php else: ?>
                      <span class="role-badge">Thành viên</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </section>
      </div>
    </main>
  </div>
</body>
</html>