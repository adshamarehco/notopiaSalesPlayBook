<?php
require_once __DIR__ . '/includes/config.php';
require_login();
$pdo = db();

$status = $_GET['status'] ?? '';
$q = trim($_GET['q'] ?? '');

$sql = "SELECT * FROM opportunities WHERE 1=1";
$params = [];

if ($status !== '') {
    $sql .= " AND status = ?";
    $params[] = $status;
}
if ($q !== '') {
    $sql .= " AND (organization LIKE ? OR code LIKE ? OR contact_name LIKE ?)";
    $params[] = "%$q%";
    $params[] = "%$q%";
    $params[] = "%$q%";
}
$sql .= " ORDER BY 
    CASE status
        WHEN 'فعال' THEN 1
        WHEN 'در حال پرورش' THEN 2
        WHEN 'متوقف موقت' THEN 3
        WHEN 'بسته‌شده-موفق' THEN 4
        ELSE 5
    END,
    next_step_date IS NULL, next_step_date ASC, updated_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();
$statuses = lookup('opportunity_statuses');

function status_class(string $s): string {
    return match ($s) {
        'فعال' => 'active',
        'در حال پرورش' => 'nurture',
        'متوقف موقت' => 'paused',
        'بسته‌شده-موفق' => 'won',
        'بسته‌شده-ناموفق' => 'lost',
        default => '',
    };
}

require __DIR__ . '/includes/header.php';
?>
<h1>فرصت‌ها</h1>

<div class="toolbar">
    <form method="get">
        <input type="text" name="q" placeholder="جستجوی سازمان یا کد..." value="<?= e($q) ?>">
        <select name="status">
            <option value="">همه وضعیت‌ها</option>
            <?php foreach ($statuses as $s): ?>
                <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn" type="submit">فیلتر</button>
        <a class="btn secondary" href="opportunities.php">پاک کردن</a>
    </form>
    <a class="btn" href="opportunity_form.php">+ فرصت جدید</a>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th>کد</th>
            <th>سازمان</th>
            <th>وضعیت</th>
            <th>مرحله</th>
            <th>گام بعدی</th>
            <th>تاریخ گام</th>
            <th>مسئول</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php if (!$rows): ?>
            <tr><td colspan="8" class="muted">فرصتی یافت نشد.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $row): ?>
            <tr>
                <td><?= e($row['code']) ?></td>
                <td><?= e($row['organization']) ?></td>
                <td><span class="badge <?= status_class($row['status']) ?>"><?= e($row['status']) ?></span></td>
                <td><?= e($row['pipeline_stage']) ?></td>
                <td><?= e(mb_strimwidth((string)$row['next_step'], 0, 50, '…')) ?></td>
                <td><?= e($row['next_step_date']) ?></td>
                <td><?= e($row['responsible']) ?></td>
                <td><a href="opportunity_form.php?id=<?= (int)$row['id'] ?>">ویرایش</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
