<?php
require_once __DIR__ . "/auth_check.php";
require_once __DIR__ . "/database_functions.php";

// 1. Lấy các con số tổng quát
$total_users = fetch_one("SELECT COUNT(*) as total FROM users WHERE is_admin = 0")['total'] ?? 0;
$total_posts = fetch_one("SELECT COUNT(*) as total FROM posts")['total'] ?? 0;
$total_comments = fetch_one("SELECT COUNT(*) as total FROM comments")['total'] ?? 0;
$total_likes = fetch_one("SELECT COUNT(*) as total FROM likes")['total'] ?? 0;

// 2. Thống kê trạng thái tài khoản
$locked_users = fetch_one("SELECT COUNT(*) as total FROM users WHERE Status = 0 AND is_admin = 0")['total'] ?? 0;
$active_users = $total_users - $locked_users;

// 3. Người dùng tích cực nhất
$top_posters = fetch_all("SELECT u.First_name, u.Last_name, COUNT(p.id) as post_count 
                          FROM users u 
                          JOIN posts p ON u.ID = p.user_id 
                          GROUP BY u.ID 
                          ORDER BY post_count DESC LIMIT 5") ?? [];

// 4. Thống kê giới tính
$gender_stats = fetch_all("SELECT Gender, COUNT(*) as count FROM users WHERE is_admin = 0 GROUP BY Gender") ?? [];

// --- PHẦN LOGIC MỚI ĐỂ LẤY DỮ LIỆU BIỂU ĐỒ ---
// Phân loại độ tuổi
$ages = fetch_all("SELECT BirthDate FROM users WHERE is_admin = 0 AND BirthDate IS NOT NULL") ?? [];
$age_groups = ['Dưới 18' => 0, '18-25' => 0, '26-35' => 0, '36-50' => 0, 'Trên 50' => 0];
foreach ($ages as $row) {
    if(!$row['BirthDate']) continue;
    $age = date_diff(date_create($row['BirthDate']), date_create('today'))->y;
    if ($age < 18) $age_groups['Dưới 18']++;
    elseif ($age <= 25) $age_groups['18-25']++;
    elseif ($age <= 35) $age_groups['26-35']++;
    elseif ($age <= 50) $age_groups['36-50']++;
    else $age_groups['Trên 50']++;
}

// Tần suất hoạt động 7 ngày
$activity_labels = []; $post_counts = []; $comment_counts = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $activity_labels[] = date('d/m', strtotime($date));
    $post_counts[] = fetch_one("SELECT COUNT(*) as total FROM posts WHERE DATE(created_at) = ?", [$date])['total'] ?? 0;
    $comment_counts[] = fetch_one("SELECT COUNT(*) as total FROM comments WHERE DATE(Timestamp) = ?", [$date])['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thống kê hệ thống</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* GIỮ NGUYÊN CSS CỦA BẠN */
        .content { flex: 1; padding: 26px; }
        .grid-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: #fff; padding: 20px; border-radius: 14px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center; border-bottom: 4px solid var(--primary); }
        .stat-card h3 { margin: 0; color: #64748b; font-size: 14px; text-transform: uppercase; }
        .stat-card p { margin: 10px 0 0; font-size: 28px; font-weight: 800; color: #1e293b; }
        .charts-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px; }
        .chart-box { background: #fff; padding: 20px; border-radius: 14px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .chart-box h2 { font-size: 18px; margin-bottom: 20px; color: #334155; border-left: 4px solid var(--primary); padding-left: 10px; }
        .list-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
        .list-item:last-child { border: none; }
        .progress-bar { height: 8px; background: #e2e8f0; border-radius: 10px; overflow: hidden; margin-top: 5px; }
        .progress-fill { height: 100%; background: var(--primary); }
        
        /* CSS CHO PHẦN BIỂU ĐỒ MỚI */
        .visual-charts { display: grid; grid-template-columns: 1.5fr 1fr; gap: 25px; margin-top: 25px; }
    </style>
</head>
<body>

<div class="app" style="display: flex; min-height: 100vh; width: 100%;">
    <?php require_once "sidebar.php"; ?>

    <main class="content">

        <div class="grid-stats">
            <div class="stat-card"><h3>Thành viên</h3><p><?= number_format($total_users) ?></p></div>
            <div class="stat-card"><h3>Bài viết</h3><p><?= number_format($total_posts) ?></p></div>
            <div class="stat-card"><h3>Bình luận</h3><p><?= number_format($total_comments) ?></p></div>
            <div class="stat-card" style="border-color: #f59e0b;"><h3>Tương tác</h3><p><?= number_format($total_likes) ?></p></div>
        </div>

        <div class="charts-layout">
            <div class="chart-box">
                <h2>🏆 Top người dùng tích cực</h2>
                <?php foreach($top_posters as $up): ?>
                <div class="list-item">
                    <span><?= htmlspecialchars($up['Last_name'] . ' ' . $up['First_name']) ?></span>
                    <span style="font-weight: bold;"><?= $up['post_count'] ?> bài viết</span>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="chart-box">
                <h2>👥 Phân bố cộng đồng</h2>
                <div class="list-item">
                    <span>Tài khoản đang hoạt động</span>
                    <span style="color: #22c55e; font-weight: bold;"><?= ($total_users > 0) ? round(($active_users/$total_users)*100, 1) : 0 ?>%</span>
                </div>
                <div class="list-item">
                    <span>Tài khoản bị khóa</span>
                    <span style="color: #ef4444; font-weight: bold;"><?= ($total_users > 0) ? round(($locked_users/$total_users)*100, 1) : 0 ?>%</span>
                </div>
                <hr style="border: none; border-top: 1px solid #eee; margin: 15px 0;">
                <?php foreach($gender_stats as $gs): ?>
                <div style="margin-bottom: 15px;">
                    <div style="display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 5px;">
                        <span>Giới tính: <?= $gs['Gender'] ?></span>
                        <span><?= $gs['count'] ?> người</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= ($total_users > 0) ? ($gs['count']/$total_users)*100 : 0 ?>%;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="visual-charts">
            <div class="chart-box">
                <h2>📈 Tần suất hoạt động (7 ngày qua)</h2>
                <canvas id="activityChart"></canvas>
            </div>
            <div class="chart-box">
                <h2>🎂 Độ tuổi thành viên</h2>
                <canvas id="ageChart"></canvas>
            </div>
        </div>
    </main>
</div>

<script>
// Vẽ biểu đồ Hoạt động
new Chart(document.getElementById('activityChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($activity_labels) ?>,
        datasets: [{
            label: 'Bài viết', data: <?= json_encode($post_counts) ?>,
            borderColor: '#1877f2', backgroundColor: 'rgba(24, 119, 242, 0.1)', fill: true, tension: 0.4
        }, {
            label: 'Bình luận', data: <?= json_encode($comment_counts) ?>,
            borderColor: '#42b72a', backgroundColor: 'rgba(66, 183, 42, 0.1)', fill: true, tension: 0.4
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

// Vẽ biểu đồ Độ tuổi
new Chart(document.getElementById('ageChart'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_keys($age_groups)) ?>,
        datasets: [{
            data: <?= json_encode(array_values($age_groups)) ?>,
            backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#4bc0c0', '#9966ff']
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'right' } } }
});
</script>

</body>
</html>