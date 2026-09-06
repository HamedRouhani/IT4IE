# 🏭 IT4IE (Information Technology for Industrial Engineering)

<div align="center">

[![PHP Version](https://img.shields.io/badge/PHP-8.2+-777BB4.svg?logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1.svg?logo=mysql&logoColor=white)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-28a745.svg)](LICENSE)
[![Architecture](https://img.shields.io/badge/Architecture-Modular%20MVC-ff6b35.svg)]()
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3.svg?logo=bootstrap&logoColor=white)]()
[![Vazirmatn](https://img.shields.io/badge/Font-Vazirmatn-198754.svg)](https://github.com/rastikerdar/vazirmatn)
[![Last Commit](https://img.shields.io/badge/Last%20Update-Sep%202026-blue.svg)]()

**پلتفرم جامع و ماژولار برای مهندسی صنایع، مدیریت پروژه و تحلیل کسب‌وکار**

*لنگرگاه دیجیتال برای مشاوره و اجرای پروژه‌های بین‌رشته‌ای*

[مستندات](docs/) • [دموی آنلاین](https://it4ie.ir/software/) • [گزارش باگ](https://github.com/HamedRouhani/IT4IE/issues)

</div>

---

## 📖 معرفی

**IT4IE** یک پلتفرم تخصصی تحت وب است که با هدف ارائه راه‌حل‌های یکپارچه در حوزه‌های **مهندسی صنایع**، **فناوری اطلاعات**، **مدیریت پروژه** و **تحلیل کسب‌وکار** طراحی شده است. این پلتفرم با **معماری ماژولار MVC اختصاصی** پیاده‌سازی شده و امکان افزودن ماژول‌های جدید را بدون تداخل در هسته اصلی سیستم فراهم می‌کند.

### 🎯 مخاطبین هدف
- مهندسین صنایع و تحلیلگران فرآیند
- مدیران پروژه (PMP/PRINCE2)
- تحلیلگران کسب‌وکار (CBAP)
- پژوهشگران تصمیم‌گیری چندمعیاره (MCDM)
- آمارشناسان و تحلیلگران داده
- متخصصان تحقیق در عملیات (OR)

---

## ✨ ویژگی‌های کلیدی

| ویژگی | توضیحات |
|-------|---------|
| 🧩 **معماری ماژولار** | افزودن/حذف نرم‌افزارها بدون تداخل در هسته |
| 🤖 **دستیارهای هوشمند (AI)** | پیشنهاد خودکار روش‌ها بر اساس متن مسئله |
| 📊 **داشبوردهای تحلیلی** | گزارش‌گیری پیشرفته با قابلیت چاپ/PDF |
| 🎨 **رابط کاربری ریسپانسیو** | Bootstrap 5 + فونت وزیرمتن + تم اختصاصی هر ماژول |
| 🔐 **حسابرسی کامل** | Activity Logs + مدیریت مالکیت داده‌ها |
| 🔗 **اشتراک داده بین ماژول‌ها** | پروژه‌ها و داده‌ها بین ماژول‌ها قابل بازیابی هستند |
| 📱 **Mobile-First** | بهینه‌سازی کامل برای موبایل و تبلت |
| 🖨️ **چاپ حرفه‌ای** | استایل‌های اختصاصی `@media print` برای هر ماژول |

---

## 📦 ماژول‌های نرم‌افزاری

### 1. 📊 **StatLab Analyzer** (تحلیلگر آماری) — *جدید در نسخه 8.0*

ماژول جامع تحلیل آماری با ۸ بخش یکپارچه:

- 📈 **آمار توصیفی**: میانگین، میانه، چولگی، کشیدگی، چهارک‌ها، شناسایی Outlier
- 🎲 **توزیع‌های احتمال**: ۱۰ توزیع (Normal, t, χ², F, Exponential, Weibull, Binomial, Poisson, ...)
- ⚖️ **آزمون فرض**: t یک/دو نمونه، Paired t، Z نسبت، χ² نیکویی برازش و استقلال، F واریانس، Mann-Whitney
- 📉 **رگرسیون و همبستگی**: پیرسون/اسپیرمن، خطی ساده، چندگانه (OLS) با نمودار پراکنش
- 🤖 **Smart Statistician**: تشخیص خودکار آزمون مناسب از متن فارسی با درصد اطمینان
- 📑 **گزارش‌های جامع**: تولید خودکار گزارش پروژه‌محور با قابلیت چاپ
- 🧮 **StatEngine**: موتور محاسباتی خالص PHP بدون وابستگی به کتابخانه خارجی
- 🔢 **DistributionLibrary**: پیاده‌سازی کامل توابع گاما، بتا و IBeta برای محاسبه دقیق CDF/Quantile

### 2. 🧠 **BABOK Analyzer** (تحلیل کسب‌وکار)
- مدیریت چرخه حیات نیازمندی‌ها بر اساس **BABOK v3**
- استخراج هوشمند نیازمندی‌ها از متن (NLP)
- بانک جامع تکنیک‌ها و وظایف (Tasks) با نمونه‌های کاربردی
- جستجوی پیشرفته در حوزه‌های دانشی (Knowledge Areas)

### 3. 📅 **PMBOK Analyzer** (مدیریت پروژه)
- مدیریت ۴۹ فرآیند و ۱۰ حوزه دانشی بر اساس **PMBOK Guide**
- مدیریت ریسک، ذی‌نفعان و تحویل‌دادنی‌ها (Deliverables)
- ردیابی پیشرفت پروژه در ۵ فاز (Initiation → Closure)
- ITTOs (Inputs, Tools, Techniques, Outputs) تعاملی

### 4. ⚖️ **MCDM Analyzer** (تصمیم‌گیری چندمعیاره)
- **۱۱ روش پشتیبانی‌شده**: AHP, TOPSIS, VIKOR, SAW, ELECTRE, PROMETHEE, BWM, ANP, COPRAS, ARAS, MOORA
- دستیار هوشمند (Smart Modeler) برای پیشنهاد بهترین روش
- محتوای آموزشی غنی (مبانی ریاضی + گام‌های اجرایی)
- قالب‌های آماده برای صنایع (تولیدی، نفت و گاز، IT، خدماتی، سلامت)
- تحلیل حساسیت و نمودارهای مقایسه‌ای

### 5. 🚚 **OR Analyzer** (تحقیق در عملیات)
- **مدل‌های حمل‌ونقل** (Transportation) با روش‌های Northwest Corner, Vogel, MODI
- **مدل‌های تخصیص** (Assignment) با الگوریتم مجارستانی
- **برنامه‌ریزی خطی** (LP) با Simplex Method
- **تحلیل حساسیت** (Sensitivity Analysis) بر روی ضرایب و منابع
- کتابخانه روش‌های OR با مثال‌های حل‌شده

---

## 🛠️ پشته فناوری (Tech Stack)

### Backend
| فناوری | نسخه | کاربرد |
|---------|------|---------|
| **PHP** | 8.2+ | زبان اصلی (Custom Modular MVC) |
| **MySQL / MariaDB** | 8.0+ | پایگاه داده (UTF8MB4) |
| **PDO** | - | اتصال امن به دیتابیس |
| **SPL** | - | ساختارهای داده و طراحی الگو |

### Frontend
| فناوری | کاربرد |
|---------|---------|
| **HTML5 + CSS3** | ساختار و استایل |
| **Bootstrap 5.3** | فریم‌ورک UI ریسپانسیو |
| **Vanilla JavaScript** | منطق سمت کلاینت (بدون jQuery) |
| **Vazirmatn** | فونت استاندارد فارسی |
| **Font Awesome 6** | آیکون‌ها |
| **Chart.js / Canvas API** | نمودارهای تعاملی |

### ابزارها
- **Git** برای کنترل نسخه
- **PHPStan** برای تحلیل استاتیک کد
- **Responsive Design** با Media Queries پیشرفته

---

## 🚀 راهنمای نصب و راه‌اندازی

### پیش‌نیازها
- PHP ≥ 8.2 (با افزونه‌های `pdo_mysql`, `mbstring`, `json`, `gd`)
- MySQL ≥ 8.0 یا MariaDB ≥ 10.5
- Apache/Nginx با پشتیبانی از `.htaccess` / `rewrite`
- Composer (اختیاری)

### ۱. کلون کردن مخزن
```bash
git clone https://github.com/HamedRouhani/IT4IE.git
cd IT4IE