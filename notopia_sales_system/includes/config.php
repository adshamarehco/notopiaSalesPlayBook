<?php
require_once __DIR__ . '/jdate.php';

/**
 * تنظیمات اتصال به دیتابیس سیستم فروش نوتوپیا
 * این مقادیر را مطابق هاست خود تغییر دهید.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'mynoto_notopia_sales');
define('DB_USER', 'mynoto_notopia_sales');
define('DB_PASS', 'Mhd.123321');      // رمز دیتابیس را اینجا بگذارید
define('DB_CHARSET', 'utf8mb4');

// رمز ورود ساده سیستم (مرحله اول - تک‌کاربره)
define('APP_PASSWORD', 'notopia1405');   // حتماً تغییر دهید

// تنظیمات نمایش
define('APP_TITLE', 'سیستم فروش نوتوپیا');
define('APP_TIMEZONE', 'Asia/Tehran');

date_default_timezone_set(APP_TIMEZONE);

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    return $pdo;
}

function require_login(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (empty($_SESSION['logged_in'])) {
        header('Location: login.php');
        exit;
    }
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function flash(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function next_opportunity_code(PDO $pdo): string
{
    $stmt = $pdo->query("SELECT code FROM opportunities ORDER BY id DESC LIMIT 1");
    $last = $stmt->fetchColumn();
    if (!$last) {
        return 'OPP-001';
    }
    if (preg_match('/OPP-(\d+)/', $last, $m)) {
        $num = (int)$m[1] + 1;
        return 'OPP-' . str_pad((string)$num, 3, '0', STR_PAD_LEFT);
    }
    return 'OPP-001';
}

function lookup(string $table): array
{
    $allowed = [
        'industries', 'pipeline_stages', 'opportunity_statuses',
        'loss_reasons', 'decision_roles', 'activity_types', 'customer_values'
    ];
    if (!in_array($table, $allowed, true)) {
        return [];
    }
    $order = in_array($table, ['pipeline_stages', 'opportunity_statuses'], true)
        ? 'ORDER BY sort_order, id'
        : 'ORDER BY id';
    $stmt = db()->query("SELECT name FROM {$table} {$order}");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}
