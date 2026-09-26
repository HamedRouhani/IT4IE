# 🏭 IT4IE (Information Technology for Industrial Engineering)

<div align="center">

[![PHP Version](https://img.shields.io/badge/PHP-8.2+-777BB4.svg?logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1.svg?logo=mysql&logoColor=white)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-28a745.svg)](LICENSE)
[![Architecture](https://img.shields.io/badge/Architecture-Modular%20MVC-ff6b35.svg)]()
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3.svg?logo=bootstrap&logoColor=white)]()
[![Last Update](https://img.shields.io/badge/Last%20Update-Sep%202026-blue.svg)]()

**پلتفرم جامع و ماژولار برای مهندسی صنایع، مدیریت پروژه و تحلیل کسب‌وکار**

[مستندات](docs/) • [دموی آنلاین](https://it4ie.ir/software/) • [گزارش باگ](https://github.com/HamedRouhani/IT4IE/issues)

</div>

---

## 📖 معرفی

**IT4IE** یک پلتفرم تخصصی تحت وب با **معماری ماژولار MVC اختصاصی** است که راه‌حل‌های یکپارچه در حوزه‌های **مهندسی صنایع**، **مدیریت پروژه**، **تحلیل کسب‌وکار**، **کنترل کیفیت (SPC)**، **منابع انسانی** و **نگهداری و تعمیرات** ارائه می‌دهد. افزودن ماژول جدید بدون تداخل در هسته اصلی امکان‌پذیر است.

### 🎯 مخاطبین هدف
مهندسین صنایع • مدیران پروژه • تحلیلگران کسب‌وکار • متخصصان SPC/کیفیت • پژوهشگران MCDM • متخصصان OR • مدیران HR • کارشناسان PdM

---

## 📦 ماژول‌های نرم‌افزاری (۸ ماژول)

| # | ماژول | پیشوند جدول | نسخه | وضعیت | رنگ |
|---|-------|-------------|-------|--------|-----|
| ۱ | 📊 **StatLab Analyzer** | `stat_` | 8.0 | Beta | `#198754` |
| ۲ | 🧠 **BABOK Analyzer** | `babok_` | 1.0 | Beta | `#0891B2` |
| ۳ | 📅 **PMBOK Analyzer** | `pmbok_` | 1.0 | Beta | `#7C3AED` |
| ۴ | ⚖️ **MCDM Analyzer** | `mcdm_` | 1.0 | Beta | `#059669` |
| ۵ | 🚚 **OR Analyzer** | `or_` | 1.0 | Beta | `#EA580C` |
| ۶ | 🏭 **PdM Analyzer** | `pm_` | 9.0 | Stable | `#0F766E` |
| ۷ | 👥 **HR Analyzer** | `hr_` | 10.0 | Beta | `#1E40AF` |
| ۸ | ✅ **Quality Analyzer** | `qc_` | 1.0 | Beta | `#059669` |

---

## ✅ Quality Analyzer (SPC + MSA) — *جدید*

ماژول جامع **کنترل کیفیت آماری** بر اساس استانداردهای **AIAG SPC** و **AIAG MSA 4th Edition**.

### 🎯 قابلیت‌های اصلی

**۱. نمودارهای کنترل — متغیر**
- X̄-R (میانگین-دامنه)
- X̄-S (میانگین-انحراف)
- I-MR (تک‌مقدار-دامنه متحرک)

**۲. نمودارهای کنترل — صفتی**
- p (نسبت معیوب) • np (تعداد معیوب) • c (تعداد نقص) • u (نقص در واحد)

**۳. تشخیص الگو — ۸ قانون نلسون**
تشخیص خودکار نقض قوانین با نمایش نقاط مشکل‌دار

**۴. تحلیل قابلیت فرآیند**
- Cp • Cpk • Pp • Ppk • Cpm • CPU • CPL
- تخمین PPM خارج از مشخصات
- ارزیابی خودکار (عالی/قابل قبول/مرزی/ناتوان)

**۵. تحلیل MSA — Gage R&R**
- روش ANOVA
- محاسبه EV, AV, GRR, PV, TV
- %GRR, ndc, ارزیابی خودکار
- ماتریس داده داینامیک (تعداد قطعه/اپراتور/تکرار)

**۶. نمونه‌گیری پذیرش (Acceptance Sampling)**
- منحنی OC (Operating Characteristic)
- محاسبه α و β
- AOQL و ATI
- پیشنهاد خودکار طرح (n, c)
- پشتیبانی از MIL-STD-105E

**۷. گزارش‌های تحلیلی**
گزارش تجمیعی از تمام بخش‌ها با قابلیت چاپ

### 🏗️ معماری فنی
- **Namespace:** `App\Software\Quality\`
- **۷ جدول** با پیشوند `qc_` (systems, projects, datasets, measurements, control_charts, capability_studies, msa_studies, sampling_plans, sampling_inspections)
- **۴ Service محاسباتی:** ControlChart, NelsonRules, Capability, Msa, Sampling
- **۷ Model + ۸ Controller + ۲۵+ View**
- **Chart.js** برای نمودارهای تعاملی
- **تم Emerald:** `#059669`

### 📊 آمار پیاده‌سازی
- **۸ کنترلر** کامل با CRUD
- **۷ مدل** با متدهای تخصصی
- **۵ سرویس** محاسباتی خالص PHP
- **۲۵+ View** ریسپانسیو
- **بیش از ۵٬۰۰۰ خط کد**

### 🎯 ویژگی‌های شاخص
- ✅ موتور محاسباتی دقیق بر اساس ثابت‌های AIAG (A2, D3, D4, B3, B4, C4, D2)
- ✅ تشخیص ۸ قانون نلسون با نمایش نقاط دقیق
- ✅ توزیع دو جمله‌ای با `logGamma` (بدون overflow)
- ✅ ماتریس MSA داینامیک با حفظ داده‌ها
- ✅ منحنی OC تعاملی
- ✅ ریسپانسیو کامل (موبایل/تبلت/دسکتاپ)

---

## 🛠️ پشته فناوری

| لایه | فناوری |
|------|--------|
| **Backend** | PHP 8.2+ • MySQL 8.0+ • PDO |
| **Frontend** | Bootstrap 5.3 • Vanilla JS • Vazirmatn • Font Awesome 6 |
| **نمودار** | Chart.js |
| **معماری** | Modular MVC • Multi-Tenant • PSR-4 Compatible |

---

## ✨ ویژگی‌های کلیدی پلتفرم

| ویژگی | توضیحات |
|-------|---------|
| 🧩 **معماری ماژولار** | افزودن/حذف ماژول بدون تداخل در هسته |
| 🔐 **CSRF Protection** | محافظت از تمام فرم‌ها |
| 📅 **تقویم شمسی** | تبدیل خودکار تاریخ بدون وابستگی خارجی |
| 🌐 **Multi-Tenant** | پشتیبانی از چند سازمان در یک نصب |
| 🔗 **اشتراک داده** | پروژه‌ها بین ماژول‌ها قابل بازیابی |
| 📱 **Mobile-First** | بهینه‌سازی کامل برای موبایل |
| 🖨️ **چاپ حرفه‌ای** | `@media print` اختصاصی هر ماژول |
| 🎨 **تم اختصاصی** | هر ماژول رنگ و هویت بصری خود را دارد |
| 📝 **Activity Logs** | حسابرسی کامل کاربران |
| 🤖 **دستیار هوشمند** | پیشنهاد خودکار روش‌ها |

---

## 🚀 نصب سریع

### پیش‌نیازها
- PHP ≥ 8.2 با `pdo_mysql`, `mbstring`, `json`, `gd`
- MySQL ≥ 8.0 یا MariaDB ≥ 10.5
- Apache/Nginx با پشتیبانی از rewrite

### نصب
```bash
git clone https://github.com/HamedRouhani/IT4IE.git
cd IT4IE