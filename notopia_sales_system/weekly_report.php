<?php
require_once __DIR__ . '/includes/config.php';
require_login();
$pdo = db();

// پیش‌فرض: دوشنبه هفته جاری
$today = new DateTimeImmutable('today');
$weekStartDefault = $today->modify('monday this week')->format('Y-m-d');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $weekStart = to_gregorian($_POST['week_start_date'] ?? '') ?: $weekStartDefault;
    $data = [
        'week_start_date' => $weekStart,
        'new_opportunities' => (int)($_POST['new_opportunities'] ?? 0),
        'meetings_demos' => (int)($_POST['meetings_demos'] ?? 0),
        'proposals_sent' => (int)($_POST['proposals_sent'] ?? 0),
        'progressed_notes' => trim($_POST['progressed_notes'] ?? ''),
        'stopped_notes' => trim($_POST['stopped_notes'] ?? ''),
        'lesson_learned' => trim($_POST['lesson_learned'] ?? ''),
        'created_by' => trim($_POST['created_by'] ?? ''),
    ];

    $exists = $pdo->prepare("SELECT id FROM weekly_reports WHERE week_start_date = ?");
    $exists->execute([$weekStart]);
    $existingId = $exists->fetchColumn();

    if ($existingId) {
        $sql = "UPDATE weekly_reports SET
            new_opportunities=?, meetings_demos=?, proposals_sent=?,
            progressed_notes=?, stopped_notes=?, lesson_learned=?, created_by=?
            WHERE id=?";
        $pdo->prepare($sql)->execute([
            $data['new_opportunities'], $data['meetings_demos'], $data['proposals_sent'],
            $data['progressed_notes'], $data['stopped_notes'], $data['lesson_learned'],
            $data['created_by'], $existingId
        ]);
        flash('success', 'گزارش هفته به‌روزرسانی شد.');
    } else {
        $sql = "INSERT INTO weekly_reports
            (week_start_date, new_opportunities, meetings_demos, proposals_sent,
             progressed_notes, stopped_notes, lesson_learned, created_by)
            VALUES (?,?,?,?,?,?,?,?)";
        $pdo->prepare($sql)->execute(array_values($data));
        flash('success', 'گزارش هفته ثبت شد.');
    }
    header('Location: weekly_report.php?week=' . urlencode($weekStart));
    exit;
}

$week = $_GET['week'] ?? $weekStartDefault;

// پیشنهاد اعداد از داده‌های سیستم
$weekEnd = (new DateTimeImmutable($week))->modify('+6 days')->format('Y-m-d');
$autoNew = (int)$pdo->prepare("SELECT COUNT(*) FROM opportunities WHERE DATE(created_at) BETWEEN ? AND ?")
    ->execute([$week, $weekEnd]) || true;
$st = $pdo->prepare("SELECT COUNT(*) FROM opportunities WHERE DATE(created_at) BETWEEN ? AND ?");
$st->execute([$week, $weekEnd]);
$autoNew = (int)$st->fetchColumn();

$st = $pdo->prepare("SELECT COUNT(*) FROM activities WHERE activity_type IN ('جلسه','نمایش محصول') AND activity_date BETWEEN ? AND ?");
$st->execute([$week, $weekEnd]);
$autoMeetings = (int)$st->fetchColumn();

$st = $pdo->prepare("SELECT COUNT(*) FROM activities WHERE activity_type = 'پیشنهاد' AND activity_date BETWEEN ? AND ?");
$st->execute([$week, $weekEnd]);
$autoProposals = (int)$st->fetchColumn();

$st = $pdo->prepare("SELECT * FROM weekly_reports WHERE week_start_date = ?");
$st->execute([$week]);
$report = $st->fetch() ?: [
    'week_start_date' => $week,
    'new_opportunities' => $autoNew,
    'meetings_demos' => $autoMeetings,
    'proposals_sent' => $autoProposals,
    'progressed_notes' => '',
    'stopped_notes' => '',
    'lesson_learned' => '',
    'created_by' => '',
];

$history = $pdo->query("SELECT * FROM weekly_reports ORDER BY week_start_date DESC LIMIT 8")->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<h1>گزارش هفتگی</h1>
<p class="muted">مطابق فصل ۱۳ و کارت ۵ — تا یکشنبه صبح آماده شود. اعداد پیشنهادی از روی داده‌های ثبت‌شده محاسبه شده‌اند.</p>

<form method="post" class="card">
    <div class="grid-3">
        <div>
            <label>شروع هفته (دوشنبه)</label>
            <input type="text" class="jalali-date" name="week_start_date" placeholder="1405/07/01" value="<?= e(to_jalali($report['week_start_date'])) ?>">
            <div class="date-hint">شروع هفته (شمسی) — مثال: 1405/07/01</div>
        </div>
        <div>
            <label>فرصت جدید</label>
            <input type="number" name="new_opportunities" min="0" value="<?= (int)$report['new_opportunities'] ?>">
            <div class="muted">پیشنهاد سیستم: <?= $autoNew ?></div>
        </div>
        <div>
            <label>جلسه / نمایش محصول</label>
            <input type="number" name="meetings_demos" min="0" value="<?= (int)$report['meetings_demos'] ?>">
            <div class="muted">پیشنهاد سیستم: <?= $autoMeetings ?></div>
        </div>
        <div>
            <label>پیشنهاد ارسال‌شده</label>
            <input type="number" name="proposals_sent" min="0" value="<?= (int)$report['proposals_sent'] ?>">
            <div class="muted">پیشنهاد سیستم: <?= $autoProposals ?></div>
        </div>
        <div>
            <label>ثبت‌کننده</label>
            <input type="text" name="created_by" value="<?= e($report['created_by']) ?>">
        </div>
    </div>

    <label>فرصت‌های جلو رفته (با دلیل کوتاه)</label>
    <textarea name="progressed_notes"><?= e($report['progressed_notes']) ?></textarea>

    <label>فرصت‌های متوقف‌شده (با دلیل)</label>
    <textarea name="stopped_notes"><?= e($report['stopped_notes']) ?></textarea>

    <label>درس هفته</label>
    <textarea name="lesson_learned"><?= e($report['lesson_learned']) ?></textarea>

    <div class="actions">
        <button class="btn" type="submit">ذخیره گزارش هفته</button>
    </div>
</form>

<div class="card">
    <h2>تاریخچه گزارش‌ها</h2>
    <table>
        <thead>
        <tr>
            <th>هفته</th>
            <th>فرصت جدید</th>
            <th>جلسه/نمایش</th>
            <th>پیشنهاد</th>
            <th>درس</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($history as $h): ?>
            <tr>
                <td><?= e(to_jalali($h['week_start_date'])) ?></td>
                <td><?= (int)$h['new_opportunities'] ?></td>
                <td><?= (int)$h['meetings_demos'] ?></td>
                <td><?= (int)$h['proposals_sent'] ?></td>
                <td><?= e(mb_strimwidth((string)$h['lesson_learned'], 0, 40, '…')) ?></td>
                <td><a href="weekly_report.php?week=<?= e($h['week_start_date']) ?>">باز کردن</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
