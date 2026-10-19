<?php
require_once __DIR__ . '/includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!empty($_SESSION['logged_in'])) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if (hash_equals(APP_PASSWORD, $password)) {
        $_SESSION['logged_in'] = true;
        header('Location: index.php');
        exit;
    }
    $error = 'رمز ورود نادرست است.';
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ورود | <?= e(APP_TITLE) ?></title>
    <style>
        @font-face {
            font-family: 'Vazirmatn';
            src: url('assets/fonts/Vazirmatn-UI-Regular.woff2') format('woff2');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Vazirmatn';
            src: url('assets/fonts/Vazirmatn-UI-Bold.woff2') format('woff2');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }

        body { font-family: 'Vazirmatn', Tahoma, Arial, sans-serif; background:#f4f6f8; display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; }
        .box { background:#fff; padding:28px; border-radius:12px; width:100%; max-width:360px; border:1px solid #e5e7eb; }
        h1 { margin:0 0 8px; font-size:20px; text-align:center; color:#0f766e; }
        p { text-align:center; color:#6b7280; font-size:13px; }
        label { display:block; margin-bottom:4px; font-size:13px; color:#6b7280; }
        input { width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:8px; margin-bottom:14px; font-family:inherit; }
        button { width:100%; padding:10px; border:none; border-radius:8px; background:#0f766e; color:#fff; font-family:inherit; cursor:pointer; }
        .err { background:#fef2f2; color:#b91c1c; padding:10px; border-radius:8px; margin-bottom:12px; font-size:13px; }
    </style>
</head>
<body>
<div class="box">
    <h1><?= e(APP_TITLE) ?></h1>
    <p>برای ورود رمز تعیین‌شده را وارد کنید.</p>
    <?php if ($error): ?><div class="err"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <label>رمز ورود</label>
        <input type="password" name="password" required autofocus>
        <button type="submit">ورود</button>
    </form>
</div>
</body>
</html>
