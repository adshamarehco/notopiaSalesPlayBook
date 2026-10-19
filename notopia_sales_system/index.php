<?php
require_once __DIR__ . '/includes/config.php';
require_login();
$pdo = db();

$active = (int)$pdo->query("SELECT COUNT(*) FROM opportunities WHERE status = 'فعال'")->fetchColumn();
$nurture = (int)$pdo->query("SELECT COUNT(*) FROM opportunities WHERE status = 'در حال پرورش'")->fetchColumn();
$proposalsMonth = (int)$pdo->query("SELECT COUNT(*) FROM activities WHERE activity_type = 'پیشنهاد' AND activity_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetchColumn();
$meetingsWeek = (int)$pdo->query("SELECT COUNT(*) FROM activities WHERE activity_type IN ('جلسه','نمایش محصول') AND activity_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();

$overdue = $pdo->query("
    SELECT id, code, organization, next_step, next_step_date, responsible
    FROM opportunities
    WHERE status IN ('فعال','در حال پرورش')
      AND next_step_date IS NOT NULL
      AND next_step_date < CURDATE()
    ORDER BY next_step_date ASC
    LIMIT 10
")->fetchAll();

$recent = $pdo->query("
    SELECT code, organization, status, pipeline_stage, next_step_date, responsible
    FROM opportunities
    ORDER BY updated_at DESC
    LIMIT 8
")->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<h1>داشبورد</h1>
<p class=\"muted\">خلاصه وضعیت سیستم فروش — بر اساس داده‌های ثبت‌شده</p>

<div class=\"stats\" style=\"margin-bottom:20px;\">
    <div class=\"stat\"><div class=\"num\"><?= $active ?></div><div class=\"label\">فرصت فعال</div></div>
    <div class=\"stat\"><div class=\"num\"><?= $nurture ?></div><div class=\"label\">در حال پرورش</div></div>
    <div class=\"stat\"><div class=\"num\"><?= $proposalsMonth ?></div><div class=\"label\">پیشنهاد این ماه</div></div>
    <div class=\"stat\"><div class=\"num\"><?= $meetingsWeek ?></div><div class=\"label\">جلسه/نمایش ۷ روز</div></div>
</div>

<div class=\"card\">
    <h2>گام‌های بعدی عقب‌افتاده</h2>
    <?php if (!$overdue): ?>
        <p class=\"muted\">موردی نیست.</p>
    <?php else: ?>
        <table>
            <thead>
            <tr>
                <th>کد</th>
                <th>سازمان</th>
                <th>گام بعدی</th>
                <th>تاریخ</th>
                <th>مسئول</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($overdue as $row): ?>
                <tr>
                    <td><a href=\"opportunity_form.php?id=<?= (int)$row['id'] ?>\"><?= e($row['code']) ?></a></td>
                    <td><?= e($row['organization']) ?></td>
                    <td><?= e($row['next_step']) ?></td>
                    <td><?= e($row['next_step_date']) ?></td>
                    <td><?= e($row['responsible']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class=\"card\">
    <h2>آخرین فرصت‌های به‌روزشده</h2>
    <table>
        <thead>
        <tr>
            <th>کد</th>
            <th>سازمان</th>
            <th>وضعیت</th>
            <th>مرحله</th>
            <th>تاریخ گام بعدی</th>
            <th>مسئول</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($recent as $row): ?>
            <tr>
                <td><?= e($row['code']) ?></td>
                <td><?= e($row['organization']) ?></td>
                <td><span class=\"badge\"><?= e($row['status']) ?></span></td>
                <td><?= e($row['pipeline_stage']) ?></td>
                <td><?= e($row['next_step_date']) ?></td>
                <td><?= e($row['responsible']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
