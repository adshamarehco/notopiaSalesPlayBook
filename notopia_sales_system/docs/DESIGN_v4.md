# Notopia Design System v4

Branch: `ui/design-v4`

## Files on this branch
- `assets/css/design-tokens.css` — token layer
- `assets/css/app-v4-overlay.css` — safe polish (load after current app.css)
- Full replacement `app.css` is in project artifact: `notopia_ui_design_v4.zip` (too large for incremental Git history of this repo snapshot)

## Why overlay not full replace on Git?
The Git repo snapshot of notopia_sales_system is older than the live server (many UI patches landed as zips, not always committed). Overlay is the safe path that works on both old Git tree and live system.

## Fastest path to see changes on live server
1. Copy from zip OR from this branch:
   - design-tokens.css
   - app-v4-overlay.css
2. In header.php add before existing app.css:
```html
<link rel="stylesheet" href=".../design-tokens.css?v=4">
```
And after app.css:
```html
<link rel="stylesheet" href=".../app-v4-overlay.css?v=4">
```

No other steps.
