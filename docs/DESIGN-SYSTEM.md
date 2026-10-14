# سیستم طراحی IT4IE (نرم‌افزارها)

نمونهٔ زنده: `docs/styleguide.html` (در مرورگر باز کنید؛ با انتخاب‌گر ماژول، رنگ همهٔ اجزا عوض می‌شود).

## اصل‌ها
1. **رنگ فقط از توکن‌های ماژول می‌آید**، نه از هگز در CSS اجزا. توکن‌ها در `public/assets/css/software-themes.css` هستند:
   `--mt` (رنگ اصلی)، `--mt-dark`، `--mt-rgb`، `--mt-tint`، `--mt-soft`، `--mt-ink`، `--mt-accent`.
2. **اجزای مشترک در یک فایل:** `public/assets/css/ds/it4ie-ds.css`. فایل هر ماژول (`modules/<m>.css`) فقط چیزهای اختصاصی همان ماژول را دارد.
3. **ورود اختیاری (opt-in):** فقط ماژول‌هایی که در `app/config/design-system.php` ثبت شده‌اند `data-ds` می‌گیرند. بقیه دقیقاً مثل قبل می‌مانند تا بدون بررسی چیزی خراب نشود.
4. **رنگ‌های معنایی ثابت‌اند:** موفق (سبز)، هشدار (کهربایی)، خطا (قرمز). این‌ها هیچ‌وقت رنگ ماژول نمی‌گیرند.
5. **ریسپانسیو و لمسی:** هدف لمسی حداقل ۴۲ پیکسل، فیلدها در موبایل ۱۶ پیکسل (جلوگیری از زوم iOS)، جدول‌ها داخل `.table-responsive` اسکرول می‌خورند، نه صفحه.

## ترتیب بارگذاری CSS (لایهٔ نرم‌افزار)
`style` ← `software.css` ← `ds/it4ie-ds.css` (فقط ماژول‌های DS) ← `modules/<m>.css` ← `software-refinement.css` ← `software-themes.css`

`software-themes.css` همیشه آخر است تا رنگ ماژول بر همه غلبه کند.

## اجزا
| جزء | کلاس | نکته |
|---|---|---|
| هیرو | `.ds-hero` + `.ds-eyebrow` + `.ds-hero-actions` | دکمه‌ها: `.btn-light`، `.btn-ghost` |
| کارت آمار | `.stats-grid > .ds-stat.ds-stat--1…4` | کارت ۱ رنگ ماژول؛ ۲ تا ۴ رنگ‌های تأکیدی ثابت |
| شبکهٔ اصلی | `.main-grid` | دو ستون، در ≤۹۹۱px یک ستون |
| شاخص | `.kpis-grid > .kpi-card` | `.kpi-value` و `.kpi-label` |
| کارت | `.card` > `.card-header` (`.card-title`, `.card-tools`) | بدون `.card-body` هم پدینگ دارد؛ با `.card-body` پدینگ از آن می‌آید |
| دکمه | `.btn` + `-primary/-secondary/-success/-warning/-danger/-info/-outline-primary` | اندازه: `-sm`، `-lg` |
| نشان | `.badge` + `.badge-primary/secondary/success/warning/info/danger` | |
| جدول | `.table-responsive > .table` | هدر به رنگ `--mt-tint` |
| فرم | `.form-group`، `.form-row`، `.form-control`، `.form-select` | فوکوس با رنگ ماژول |
| نوار پیشرفت | `.progress > .progress-bar` | |
| پیام | `.alert-software.success / .error / .warning` | |

## افزودن یک ماژول به سیستم طراحی
1. در `software-themes.css` رنگ‌های ماژول را در بلوک `body.software-mode[data-module="<m>"]` بررسی کنید.
2. صفحه‌های ماژول را با `docs/styleguide.html` مقایسه کنید؛ استایل‌های inline و کلاس‌های خاص ماژول را به کلاس‌های DS ببرید.
3. نام ماژول را به `app/config/design-system.php` اضافه کنید.
4. اگر باندل ساخته‌اید، دوباره `php tools/build-css.php` بزنید (در غیر این صورت خودکار از فایل‌های اصلی استفاده می‌شود).

## ممنوع
- هگز رنگ ماژول (یا هر رنگ برند دیگر) داخل CSS اجزا: از `--mt*` استفاده کنید.
- استایل inline برای رنگ، فاصله یا اندازه‌ای که کلاس DS دارد.
- تعریف دوبارهٔ `.card`، `.btn`، `.table`… در فایل ماژول.

## ابزار ساخت و استقرار
```
php tools/build-css.php          # باندل‌ها را در public/assets/dist می‌سازد
php tools/build-css.php --clean  # باندل‌ها را حذف می‌کند (حالت توسعه)
```
- لایه‌ها فقط وقتی باندل را استفاده می‌کنند که هیچ فایل منبعی بعد از ساخت تغییر نکرده باشد (`dist/manifest.json`). فراموش‌کردن ساخت مجدد هرگز CSS کهنه نمی‌دهد.
- نسخهٔ کش هر فایل (`?v=`) خودکار از زمان تغییر فایل می‌آید؛ دیگر عدد دستی لازم نیست.
