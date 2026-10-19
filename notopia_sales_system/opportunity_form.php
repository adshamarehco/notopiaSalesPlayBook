<?php
require_once __DIR__ . '/includes/config.php';
require_login();
$pdo = db();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$row = null;

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM opportunities WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        flash('error', 'فرصت یافت نشد.');
        header('Location: opportunities.php');
        exit;
    }
}

$industries = lookup('industries');
$stages = lookup('pipeline_stages');
$statuses = lookup('opportunity_statuses');
$roles = lookup('decision_roles');
$values = lookup('customer_values');
$lossReasons = lookup('loss_reasons');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'organization' => trim($_POST['organization'] ?? ''),
        'industry' => $_POST['industry'] ?? null,
        'contact_name' => trim($_POST['contact_name'] ?? ''),
        'contact_title' => trim($_POST['contact_title'] ?? ''),
        'decision_role' => $_POST['decision_role'] ?? null,
        'purchase_purpose' => trim($_POST['purchase_purpose'] ?? ''),
        'estimated_quantity' => trim($_POST['estimated_quantity'] ?? ''),
        'estimated_budget' => $_POST['estimated_budget'] ?? null,
        'estimated_buy_time' => trim($_POST['estimated_buy_time'] ?? ''),
        'customer_value' => $_POST['customer_value'] ?? null,
        'main_objection' => trim($_POST['main_objection'] ?? ''),
        'pipeline_stage' => $_POST['pipeline_stage'] ?? 'سرنخ اولیه',
        'status' => $_POST['status'] ?? 'فعال',
        'last_interaction' => trim($_POST['last_interaction'] ?? ''),
        'last_interaction_date' => to_gregorian($_POST['last_interaction_date'] ?? ''),
        'next_step' => trim($_POST['next_step'] ?? ''),
        'next_step_date' => to_gregorian($_POST['next_step_date'] ?? ''),
        'responsible' => trim($_POST['responsible'] ?? ''),
        'loss_reason' => $_POST['loss_reason'] ?? null,
        'final_result' => trim($_POST['final_result'] ?? ''),
        'notes' => trim($_POST['notes'] ?? ''),
    ];

    if ($data['organization'] === '') {
        flash('error', 'نام سازمان الزامی است.');
        header('Location: opportunity_form.php' . ($id ? "?id=$id" : ''));
        exit;
    }

    // برای فرصت فعال، گام بعدی توصیه می‌شود (اجبار نرم)
    if (in_array($data['status'], ['فعال', 'در حال پرورش'], true) && $data['next_step'] === '') {
        flash('error', 'برای فرصت فعال یا در حال پرورش، گام بعدی را وارد کنید.');
        header('Location: opportunity_form.php' . ($id ? "?id=$id" : ''));
        exit;
    }

    if ($id > 0) {
        $sql = "UPDATE opportunities SET
            organization=?, industry=?, contact_name=?, contact_title=?, decision_role=?,
            purchase_purpose=?, estimated_quantity=?, estimated_budget=?, estimated_buy_time=?,
            customer_value=?, main_objection=?, pipeline_stage=?, status=?,
            last_interaction=?, last_interaction_date=?, next_step=?, next_step_date=?,
            responsible=?, loss_reason=?, final_result=?, notes=?
            WHERE id=?";
        $params = array_values($data);
        $params[] = $id;
        $pdo->prepare($sql)->execute($params);
        flash('success', 'فرصت به‌روزرسانی شد.');
        header("Location: opportunity_form.php?id=$id");
        exit;
    }

    $code = next_opportunity_code($pdo);
    $sql = "INSERT INTO opportunities (
        code, organization, industry, contact_name, contact_title, decision_role,
        purchase_purpose, estimated_quantity, estimated_budget, estimated_buy_time,
        customer_value, main_objection, pipeline_stage, status,
        last_interaction, last_interaction_date, next_step, next_step_date,
        responsible, loss_reason, final_result, notes
    ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $params = array_merge([$code], array_values($data));
    $pdo->prepare($sql)->execute($params);
    $newId = (int)$pdo->lastInsertId();
    flash('success', "فرصت با کد $code ثبت شد.");
    header("Location: opportunity_form.php?id=$newId");
    exit;
}

$v = $row ?: [
    'organization' => '', 'industry' => '', 'contact_name' => '', 'contact_title' => '',
    'decision_role' => '', 'purchase_purpose' => '', 'estimated_quantity' => '',
    'estimated_budget' => '', 'estimated_buy_time' => '', 'customer_value' => '',
    'main_objection' => '', 'pipeline_stage' => 'سرنخ اولیه', 'status' => 'فعال',
    'last_interaction' => '', 'last_interaction_date' => '', 'next_step' => '',
    'next_step_date' => '', 'responsible' => '', 'loss_reason' => '',
    'final_result' => '', 'notes' => '', 'code' => ''
];

require __DIR__ . '/includes/header.php';
?>
<h1><?= $id ? 'ویرایش فرصت ' . e($v['code'] ?? '') : 'ثبت فرصت جدید' ?></h1>
<p class="muted">فیلدهای وضعیت، مرحله و گام بعدی برای کنترل فرآیند فروش حیاتی هستند.</p>

<form method="post" class="card">
    <div class="grid-2">
        <div>
            <label>سازمان *</label>
            <input type="text" name="organization" required value="<?= e($v['organization']) ?>">
        </div>
        <div>
            <label>صنعت</label>
            <select name="industry">
                <option value="">— انتخاب —</option>
                <?php foreach ($industries as $item): ?>
                    <option value="<?= e($item) ?>" <?= ($v['industry'] ?? '') === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>فرد تماس</label>
            <input type="text" name="contact_name" value="<?= e($v['contact_name']) ?>">
        </div>
        <div>
            <label>سمت</label>
            <input type="text" name="contact_title" value="<?= e($v['contact_title']) ?>">
        </div>
        <div>
            <label>نقش در تصمیم</label>
            <select name="decision_role">
                <option value="">— انتخاب —</option>
                <?php foreach ($roles as $item): ?>
                    <option value="<?= e($item) ?>" <?= ($v['decision_role'] ?? '') === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>کاربرد خرید</label>
            <input type="text" name="purchase_purpose" value="<?= e($v['purchase_purpose']) ?>">
        </div>
        <div>
            <label>تیراژ احتمالی</label>
            <input type="text" name="estimated_quantity" value="<?= e($v['estimated_quantity']) ?>">
        </div>
        <div>
            <label>بودجه تقریبی</label>
            <select name="estimated_budget">
                <option value="">— انتخاب —</option>
                <?php foreach (['پایین','متوسط','بالا','نامشخص'] as $item): ?>
                    <option value="<?= e($item) ?>" <?= ($v['estimated_budget'] ?? '') === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>زمان خرید احتمالی</label>
            <input type="text" name="estimated_buy_time" value="<?= e($v['estimated_buy_time']) ?>">
        </div>
        <div>
            <label>ارزش مورد توجه مشتری</label>
            <select name="customer_value">
                <option value="">— انتخاب —</option>
                <?php foreach ($values as $item): ?>
                    <option value="<?= e($item) ?>" <?= ($v['customer_value'] ?? '') === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <label>اعتراض اصلی</label>
    <textarea name="main_objection"><?= e($v['main_objection']) ?></textarea>

    <div class="grid-3">
        <div>
            <label>مرحله قیف</label>
            <select name="pipeline_stage">
                <?php foreach ($stages as $item): ?>
                    <option value="<?= e($item) ?>" <?= ($v['pipeline_stage'] ?? '') === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>وضعیت فرصت</label>
            <select name="status">
                <?php foreach ($statuses as $item): ?>
                    <option value="<?= e($item) ?>" <?= ($v['status'] ?? '') === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>دلیل توقف/باخت</label>
            <select name="loss_reason">
                <option value="">— در صورت نیاز —</option>
                <?php foreach ($lossReasons as $item): ?>
                    <option value="<?= e($item) ?>" <?= ($v['loss_reason'] ?? '') === $item ? 'selected' : '' ?>><?= e($item) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="grid-2">
        <div>
            <label>آخرین تعامل</label>
            <textarea name="last_interaction"><?= e($v['last_interaction']) ?></textarea>
        </div>
        <div>
            <label>تاریخ آخرین تعامل</label>
            <input type="text" class="jalali-date" name="last_interaction_date" placeholder="1405/07/27" value="<?= e(to_jalali($v['last_interaction_date'])) ?>">
            <div class="date-hint">تاریخ شمسی — مثال: 1405/07/27</div>
        </div>
        <div>
            <label>گام بعدی *</label>
            <textarea name="next_step" placeholder="چه کاری، توسط چه کسی، با چه هدفی"><?= e($v['next_step']) ?></textarea>
        </div>
        <div>
            <label>تاریخ گام بعدی</label>
            <input type="text" class="jalali-date" name="next_step_date" placeholder="1405/07/27" value="<?= e(to_jalali($v['next_step_date'])) ?>">
            <div class="date-hint">تاریخ شمسی — مثال: 1405/07/27</div>
        </div>
        <div>
            <label>مسئول پیگیری</label>
            <input type="text" name="responsible" value="<?= e($v['responsible']) ?>">
        </div>
        <div>
            <label>نتیجه نهایی</label>
            <textarea name="final_result"><?= e($v['final_result']) ?></textarea>
        </div>
    </div>

    <label>توضیحات</label>
    <textarea name="notes"><?= e($v['notes']) ?></textarea>

    <div class="actions">
        <button class="btn" type="submit"><?= $id ? 'ذخیره تغییرات' : 'ثبت فرصت' ?></button>
        <a class="btn secondary" href="opportunities.php">بازگشت</a>
    </div>
</form>
<?php require __DIR__ . '/includes/footer.php'; ?>
