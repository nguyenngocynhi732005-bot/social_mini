<?php
http_response_code(403);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Không có quyền truy cập</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, sans-serif;
            background: #f3f6fb;
            color: #1f2937;
        }
        .box {
            max-width: 520px;
            background: #fff;
            border-radius: 12px;
            border: 1px solid #dbe3f1;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
            padding: 24px;
            text-align: center;
        }
        a {
            display: inline-block;
            margin-top: 12px;
            text-decoration: none;
            color: #1d4ed8;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="box">
        <h2>Bạn không có quyền truy cập khu vực quản trị.</h2>
        <a href="/admin/login_admin.php">Quay về trang đăng nhập admin</a>
    </div>
</body>
</html>
