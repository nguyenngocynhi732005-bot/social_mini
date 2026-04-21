<?php
session_start();
if (isset($_SESSION['is_admin']) && (int)$_SESSION['is_admin'] === 1) {
    header("Location: admin_dashboard.php");
    exit();
}
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="vi">
<!-- phần HTML như bạn đã có -->

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng nhập Quản trị</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        :root{
            --bg:#0f172a;
            --card:#ffffff;
            --primary:#2563eb;
            --primary-soft:#dbeafe;
            --text:#0f172a;
            --muted:#64748b;
            --danger:#ef4444;
            --radius:14px;
            --shadow:0 18px 45px rgba(15,23,42,.30);
        }

        *{box-sizing:border-box;margin:0;padding:0;}

        body{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            font-family: system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;
            background: radial-gradient(circle at top, #1e293b, #020617);
            color:var(--text);
        }

        .wrapper{
            width:100%;
            max-width:420px;
            padding:16px;
        }

        .card{
            background:var(--card);
            border-radius:var(--radius);
            padding:26px 26px 24px;
            box-shadow:var(--shadow);
            position:relative;
            overflow:hidden;
        }

        .badge{
            position:absolute;
            top:18px;
            right:20px;
            font-size:11px;
            text-transform:uppercase;
            letter-spacing:.08em;
            color:#1d4ed8;
            background:var(--primary-soft);
            padding:4px 10px;
            border-radius:999px;
            font-weight:600;
        }

        h1{
            font-size:22px;
            margin-bottom:6px;
        }

        .subtitle{
            font-size:13px;
            color:var(--muted);
            margin-bottom:18px;
        }

        .field{
            margin-bottom:14px;
        }

        label{
            display:block;
            font-size:13px;
            font-weight:600;
            margin-bottom:6px;
            color:#0f172a;
        }

        input[type="email"],
        input[type="password"]{
            width:100%;
            padding:9px 11px;
            border-radius:10px;
            border:1px solid #cbd5e1;
            font-size:14px;
            outline:none;
            transition:.15s;
        }

        input:focus{
            border-color:var(--primary);
            box-shadow:0 0 0 1px rgba(37,99,235,.25);
        }

        .error{
            background: #fef2f2;
            border:1px solid #fecaca;
            color:var(--danger);
            padding:8px 10px;
            border-radius:10px;
            font-size:13px;
            margin-bottom:12px;
        }

        .actions{
            margin-top:6px;
        }

        button{
            width:100%;
            padding:10px 0;
            border:none;
            border-radius:999px;
            background:var(--primary);
            color:#fff;
            font-weight:600;
            cursor:pointer;
            font-size:14px;
            box-shadow:0 10px 30px rgba(37,99,235,.35);
            transition:.15s;
        }

        button:hover{
            background:#1d4ed8;
            transform:translateY(-1px);
        }

        .footer{
            margin-top:14px;
            font-size:12px;
            color:var(--muted);
            text-align:center;
        }
        .footer a{
            color:#2563eb;
            text-decoration:none;
            font-weight:500;
        }
        .footer a:hover{
            text-decoration:underline;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="card">
        <div class="badge">Admin</div>
        <h1>Đăng nhập Quản trị</h1>
        <p class="subtitle">Vui lòng sử dụng tài khoản có quyền quản trị để truy cập bảng điều khiển.</p>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="post" action="login_admin_process.php">
            <div class="field">
                <label for="email">Email quản trị</label>
                <input type="email" id="email" name="email" placeholder="admin@gmail.com" required>
            </div>

            <div class="field">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
            </div>

            <div class="actions">
                <button type="submit">Đăng nhập</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
