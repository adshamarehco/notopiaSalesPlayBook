# هدر — تغییر یک خطی برای v4

در includes/header.php این دو خط را جایگزین لینک فعلی app.css کنید:

```php
<link rel="stylesheet" href="<?= e(asset_url('css/design-tokens.css')) ?>?v=4">
<link rel="stylesheet" href="<?= e(asset_url('css/app.css')) ?>?v=23">
<link rel="stylesheet" href="<?= e(asset_url('css/jalali-datepicker.css')) ?>?v=5">
```

اگر design-tokens لود نشود، app.css همچنان کار می‌کند (توکن‌های legacy داخل خودش دارد).
