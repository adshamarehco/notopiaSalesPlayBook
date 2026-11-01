# نصب Notopia Design System v4 از Git

## راه سریع (پیشنهادی)

```bash
git fetch origin
git checkout ui/design-v4
# یا اگر می‌خواهی روی main بمانی:
git checkout main
git merge ui/design-v4
```

سپس فقط این دو فایل CSS را روی سرور/لوکال کپی کن:
- `notopia_sales_system/assets/css/design-tokens.css`
- `notopia_sales_system/assets/css/app.css`

و یک خط در header.php (ببین HEADER_CHANGE_v4.md).

## نکته
مخزن main نسخهٔ کامل‌تری از سیستم زنده روی سرور دارد. این branch فقط لایه UI v4 را اضافه می‌کند.
