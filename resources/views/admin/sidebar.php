<?php
$current_page = basename(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '');
?>
<style>
    /* CSS GỐC TỪ ADMIN_DASHBOARD */
    :root {
        --bg: #f4f6f8; --card: #ffffff; --sidebar: #23364a; --sidebar2: #1c2b3b;
        --text: #0f172a; --muted: #64748b; --line: #e5e7eb; --primary: #2563eb;
        --danger: #ef4444; --shadow: 0 10px 25px rgba(15,23,42,.08); --radius: 14px;
    }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
        background: var(--bg);
        color: var(--text);
    }
    .app { min-height: 100vh; display: flex; }
    
    .sidebar {
        width: 260px;
        background: linear-gradient(180deg, var(--sidebar), var(--sidebar2));
        color: #fff;
        padding: 22px 18px;
        position: sticky;
        top: 0;
        height: 100vh;
        display: flex;
        flex-direction: column;
    }
    .brand { font-size: 26px; font-weight: 800; letter-spacing: .5px; margin: 4px 6px 16px; }
    .divider { border: 0; border-top: 1px solid rgba(255,255,255,.12); margin: 12px 0 14px; }
    .nav a {
        display: flex; gap: 10px; align-items: center; padding: 12px 12px;
        margin: 6px 0; border-radius: 12px; text-decoration: none;
        color: rgba(255,255,255,.92); transition: .15s;
    }
    .nav a:hover, .nav a.active { background: rgba(255,255,255,.10); transform: translateX(2px); }
    .nav a.logout { color: #fff; background: rgba(239,68,68,.12); margin-top: auto; }
    .nav a.logout:hover { background: rgba(239,68,68,.20); }

    @media (max-width: 980px) { .sidebar { width: 220px; } }
    @media (max-width: 720px) { 
        .app { flex-direction: column; }
        .sidebar { width: 100%; height: auto; position: relative; }
    }
</style>

<aside class="sidebar">
    <div class="brand">ADMIN</div>
    <hr class="divider"/>
    <nav class="nav" style="display: flex; flex-direction: column; height: 100%;">
        <a href="/admin/admin_dashboard.php" class="<?= ($current_page == 'admin_dashboard.php') ? 'active' : '' ?>">🏠 <span>Tổng quan</span></a>
        <a href="/admin/manage_users.php" class="<?= ($current_page == 'manage_users.php') ? 'active' : '' ?>">👥 <span>Quản lý Người dùng</span></a>
        <a href="/admin/manage_appeals.php" class="<?= ($current_page == 'manage_appeals.php') ? 'active' : '' ?>">⚖️ <span>Danh sách Kháng nghị</span></a>
        <a href="/admin/manage_posts.php" class="<?= ($current_page == 'manage_posts.php') ? 'active' : '' ?>">📝 <span>Quản lý Bài viết</span></a>
        <a href="/admin/manage_comments.php" class="<?= ($current_page == 'manage_comments.php') ? 'active' : '' ?>">💬 <span>Quản lý Bình luận</span></a>
        <a href="/admin/statistics.php" class="<?= ($current_page == 'statistics.php') ? 'active' : '' ?>">📊 <span>Thống kê</span></a>
        <a class="logout" href="/admin/logout.php">🚪 <span>Đăng xuất</span></a>
    </nav>
</aside>