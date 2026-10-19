<?php
require_once __DIR__ . '/includes/config.php';
require_login();
$pdo = db();

$types = lookup('activity_types');
$opps = $pdo->query("SELECT id, code, organization FROM opportunities WHERE status IN ('فعال','در حال پرورش','متوقف موقت') ORDER BY organization")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $opportunityId = $_POST['opportunity_id'] !== '' ? (int)$_POST['opportunity_id'] : null;
    $organizationName = trim($_POST['organization_name'] ?? '');
    $activityDate = to_gregorian($_POST['activity_date'] ?? '') ?: date('Y-m-d');
    $activityType = $_POST['activity_type'] ?? '';
    $summary = trim($_POST['summary'] ?? '');
    $result = trim($_POST['result'] ?? '');
    $nextStepDefined = isset($_POST['next_step_defined']) ? 1 : 0;
    $registeredBy = trim($_POST['registered_by'] ?? '');

    if ($activityType === '' || $activityDate === '') {
        flash('error', 'نوع فعالیت و تاریخ الزامی است.');
        header('Location: activity_form.php');
        exit;
    }

    if (!$opportunityId && $organizationName === '') {
        flash('error', 'یا فرصت را انتخاب کنید یا نام سازمان را وارد کنید.');
        header('Location: activity_form.php');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO activities
        (opportunity_id, organization_name, activity_date, activity_type, summary, result, next_step_defined, registered_by)
        VALUES (?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $opportunityId, $organizationName ?: null, $activityDate, $activityType,
        $summary, $result, $nextStepDefined, $registeredBy
    ]);

    // اگر فرصت انتخاب شده، آخرین تعامل را به‌روز کن
    if ($opportunityId) {
        $upd = $pdo->prepare("UPDATE opportunities SET last_interaction = ?, last_interaction_date = ? WHERE id = ?");
        $upd->execute([$summary ?: $activityType, $activityDate, $opportunityId]);
    }

    flash('success', 'فعالیت ثبت شد.');
    header('Location: activity_form.php');
    exit;
}

$recent = $pdo->query("
    SELECT a.*, o.code AS opp_code
    FROM activities a
    LEFT JOIN opportunities o ON o.id = a.opportunity_id
    ORDER BY a.activity_date DESC, a.id DESC
    LIMIT 15
")->fetchAll();

require __DIR__ . '/includes/header.php';
?>
<h1>ثبت فعالیت</h1>
<p class="muted">هر جلسه، نمایش محصول، تماس معنادار یا پیشنهاد باید حداکثر تا پایان همان روز ثبت شود.</p>

<form method="post" class="card">
    <div class="grid-2">
        <div>
            <label>فرصت مرتبط</label>
            <select name="opportunity_id">
                <option value="">— انتخاب فرصت (اختیاری) —</option>
                <?php foreach ($opps as $o): ?>
                    <option value="<?= (int)$o['id'] ?>"><?= e($o['code'] . ' — ' . $o['organization']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>نام سازمان (اگر فرصت هنوز ثبت نشده)</label>
            <input type="text" name="organization_name" placeholder="فقط وقتی فرصت انتخاب نشده">
        </div>
        <div>
            <label>تاریخ فعالیت *</label>
            <input type="text" class="jalali-date" name="activity_date" required placeholder="1405/07/27" value="<?= e(today_jalali()) ?>">
            <div class="date-hint">تاریخ شمسی — مثال: 1405/07/27</div>
        </div>
        <div>
            <label>نوع فعالیت *</label>
            <select name="activity_type" required>
                <option value="">— انتخاب —</option>
                <?php foreach ($types as $item): ?>
                    <option value="<?= e($item) ?>"><?= e($item) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>ثبت‌کننده</label>
            <input type="text" name="registered_by">
        </div>
        <div style="display:flex;align-items:center;gap:8px;padding-top:22px;">
            <input type="checkbox" name="next_step_defined" id="nsd" value="1">
            <label for="nsd" style="margin:0;">گام بعدی تعریف شد</label>
        </div>
    </div>

    <label>خلاصه</label>
    <textarea name="summary" placeholder="چه کاری انجام شد؟"></textarea>

    <label>نتیجه</label>
    <textarea name="result" placeholder="خروجی یا واکنش مشتری"></textarea>

    <div class="actions">
        <button class="btn" type="submit">ثبت فعالیت</button>
    </div>
</form>

<div class="card">
    <h2>آخرین فعالیت‌های ثبت‌شده</h2>
    <table>
        <thead>
        <tr>
            <th>تاریخ</th>
            <th>نوع</th>
            <th>فرصت / سازمان</th>
            <th>خلاصه</th>
            <th>گام بعدی؟</th>
            <th>ثبت‌کننده</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($recent as $r): ?>
            <tr>
                <td><?= e(to_jalali($r['activity_date'])) ?></td>
                <td><?= e($r['activity_type']) ?></td>
                <td><?= e($r['opp_code'] ?: $r['organization_name']) ?></td>
                <td><?= e(mb_strimwidth((string)$r['summary'], 0, 60, '…')) ?></td>
                <td><?= $r['next_step_defined'] ? 'بله' : 'خیر' ?></td>
                <td><?= e($r['registered_by']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
