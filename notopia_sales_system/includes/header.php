<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(APP_TITLE) ?></title>
    <style>
        @font-face {
            font-family: 'Vazirmatn';
            src: url('assets/fonts/Vazirmatn-UI-Thin.woff2') format('woff2');
            font-weight: 100;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Vazirmatn';
            src: url('assets/fonts/Vazirmatn-UI-ExtraLight.woff2') format('woff2');
            font-weight: 200;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Vazirmatn';
            src: url('assets/fonts/Vazirmatn-UI-Light.woff2') format('woff2');
            font-weight: 300;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Vazirmatn';
            src: url('assets/fonts/Vazirmatn-UI-Regular.woff2') format('woff2');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Vazirmatn';
            src: url('assets/fonts/Vazirmatn-UI-Medium.woff2') format('woff2');
            font-weight: 500;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Vazirmatn';
            src: url('assets/fonts/Vazirmatn-UI-SemiBold.woff2') format('woff2');
            font-weight: 600;
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
        @font-face {
            font-family: 'Vazirmatn';
            src: url('assets/fonts/Vazirmatn-UI-ExtraBold.woff2') format('woff2');
            font-weight: 800;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Vazirmatn';
            src: url('assets/fonts/Vazirmatn-UI-Black.woff2') format('woff2');
            font-weight: 900;
            font-style: normal;
            font-display: swap;
        }

        :root {
            --bg: #f4f6f8;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #0f766e;
            --primary-hover: #0d9488;
            --danger: #b91c1c;
            --border: #e5e7eb;
            --success: #047857;
            --warning: #b45309;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Vazirmatn', Tahoma, "Segoe UI", Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.7;
        }
        a { color: var(--primary); text-decoration: none; }
        a:hover { text-decoration: underline; }
        .container { max-width: 1100px; margin: 0 auto; padding: 16px; }
        header.app-header {
            background: var(--card);
            border-bottom: 1px solid var(--border);
            margin-bottom: 20px;
        }
        header.app-header .inner {
            max-width: 1100px;
            margin: 0 auto;
            padding: 12px 16px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
        }
        .brand { font-weight: bold; font-size: 18px; color: var(--primary); }
        nav { display: flex; flex-wrap: wrap; gap: 10px; }
        nav a {
            padding: 6px 10px;
            border-radius: 8px;
            background: #ecfdf5;
            color: var(--primary);
            font-size: 14px;
        }
        nav a:hover { background: #d1fae5; text-decoration: none; }
        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 16px;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
        }
        h1, h2, h3 { margin-top: 0; }
        h1 { font-size: 22px; }
        h2 { font-size: 18px; }
        .muted { color: var(--muted); font-size: 13px; }
        .flash {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .flash.success { background: #ecfdf5; color: var(--success); border: 1px solid #a7f3d0; }
        .flash.error { background: #fef2f2; color: var(--danger); border: 1px solid #fecaca; }
        label { display: block; font-size: 13px; margin-bottom: 4px; color: var(--muted); }
        input[type="text"], input[type="date"], input.jalali-date, input[type="number"], input[type="password"],
        select, textarea {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            margin-bottom: 12px;
            background: #fff;
        }
        textarea { min-height: 80px; resize: vertical; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
        @media (max-width: 768px) {
            .grid-2, .grid-3 { grid-template-columns: 1fr; }
        }
        .btn {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 14px;
            background: var(--primary);
            color: #fff;
        }
        .btn:hover { background: var(--primary-hover); }
        .btn.secondary { background: #e5e7eb; color: #111; }
        .btn.danger { background: var(--danger); }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { padding: 8px 10px; border-bottom: 1px solid var(--border); text-align: right; vertical-align: top; }
        th { background: #f9fafb; font-weight: 600; }
        tr:hover td { background: #fafafa; }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 12px;
            background: #e5e7eb;
        }
        .badge.active { background: #d1fae5; color: #065f46; }
        .badge.nurture { background: #fef3c7; color: #92400e; }
        .badge.paused { background: #e5e7eb; color: #374151; }
        .badge.won { background: #bbf7d0; color: #14532d; }
        .badge.lost { background: #fecaca; color: #7f1d1d; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; }
        .stat {
            background: #ecfdf5;
            border-radius: 10px;
            padding: 14px;
            text-align: center;
        }
        .stat .num { font-size: 24px; font-weight: bold; color: var(--primary); }
        .stat .label { font-size: 12px; color: var(--muted); }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .toolbar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; align-items: center; }
        .toolbar form { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .toolbar select, .toolbar input { margin-bottom: 0; width: auto; min-width: 140px; }
            input.jalali-date { direction: ltr; text-align: center; letter-spacing: 0.5px; }
        .date-hint { font-size: 11px; color: var(--muted); margin-top: -8px; margin-bottom: 10px; }
</style>
</head>
<body>
<header class="app-header">
    <div class="inner">
        <div class="brand"><?= e(APP_TITLE) ?></div>
        <?php if (!empty($_SESSION['logged_in'])): ?>
        <nav>
            <a href="index.php">داشبورد</a>
            <a href="opportunities.php">فرصت‌ها</a>
            <a href="opportunity_form.php">ثبت فرصت</a>
            <a href="activity_form.php">ثبت فعالیت</a>
            <a href="weekly_report.php">گزارش هفتگی</a>
            <a href="logout.php">خروج</a>
        </nav>
        <?php endif; ?>
    </div>
</header>
<main class="container">
<?php if ($flash): ?>
    <div class="flash <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
