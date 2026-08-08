<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'BABOK Analyzer' ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ===== استایل‌های پایه ===== */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #ecf0f1;
            --dark-text: #2c3e50;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --border-radius: 12px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
            background-color: #f4f7f9;
            color: var(--dark-text);
            line-height: 1.7;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* ===== هدر ===== */
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), #1a252f);
            color: white;
            padding: 20px 0;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .logo-area h1 {
            font-size: 1.8rem;
            font-weight: 700;
        }
        .logo-area h1 i {
            color: var(--secondary-color);
            margin-left: 10px;
        }
        .logo-area span {
            font-size: 0.85rem;
            opacity: 0.8;
        }

        .header-nav {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .header-nav a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 8px;
            transition: var(--transition);
            font-size: 0.9rem;
        }
        .header-nav a:hover {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        .header-nav a.active {
            color: #fff;
            background: rgba(52, 152, 219, 0.3);
        }
        .header-nav i {
            margin-left: 6px;
        }

        /* ===== کارت‌ها ===== */
        .card {
            background: white;
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            transition: var(--transition);
        }
        .card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-bg);
            flex-wrap: wrap;
            gap: 10px;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 700;
        }
        .card-title i {
            color: var(--secondary-color);
            margin-left: 8px;
        }

        .card-tools {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        /* ===== دکمه‌ها ===== */
        .btn {
            display: inline-block;
            padding: 10px 22px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            text-align: center;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
        }
        .btn-primary {
            background: var(--secondary-color);
            color: white;
        }
        .btn-primary:hover {
            background: #2980b9;
        }
        .btn-success {
            background: var(--success-color);
            color: white;
        }
        .btn-success:hover {
            background: #219a52;
        }
        .btn-warning {
            background: var(--warning-color);
            color: white;
        }
        .btn-warning:hover {
            background: #d68910;
        }
        .btn-danger {
            background: var(--danger-color);
            color: white;
        }
        .btn-danger:hover {
            background: #c0392b;
        }
        .btn-secondary {
            background: var(--light-bg);
            color: var(--dark-text);
        }
        .btn-secondary:hover {
            background: #d5d8dc;
        }
        .btn-outline-primary {
            background: transparent;
            color: var(--secondary-color);
            border: 2px solid var(--secondary-color);
        }
        .btn-outline-primary:hover {
            background: var(--secondary-color);
            color: white;
        }
        .btn-sm {
            padding: 5px 12px;
            font-size: 0.8rem;
        }
        .btn-lg {
            padding: 14px 35px;
            font-size: 1.1rem;
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        /* ===== نشان‌ها ===== */
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-primary { background: var(--secondary-color); color: white; }
        .badge-success { background: var(--success-color); color: white; }
        .badge-warning { background: var(--warning-color); color: white; }
        .badge-danger { background: var(--danger-color); color: white; }
        .badge-secondary { background: var(--light-bg); color: var(--dark-text); }
        .badge-info { background: #17a2b8; color: white; }

        /* ===== جدول ===== */
        .table-responsive {
            overflow-x: auto;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .table th {
            background: var(--primary-color);
            color: white;
            padding: 12px 15px;
            text-align: right;
        }
        .table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        .table tr:hover td {
            background: #f8f9fa;
        }

        /* ===== فرم‌ها ===== */
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 1rem;
            transition: var(--transition);
            font-family: inherit;
        }
        .form-control:focus {
            border-color: var(--secondary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        /* ===== فوتر ===== */
        .main-footer {
            background: var(--primary-color);
            color: white;
            text-align: center;
            padding: 25px 0;
            margin-top: 40px;
        }
        .main-footer a {
            color: var(--secondary-color);
            text-decoration: none;
        }
        .main-footer a:hover {
            text-decoration: underline;
        }
        .main-footer p {
            opacity: 0.8;
            margin: 5px 0;
        }

        /* ===== ابزارهای کمکی ===== */
        .text-muted { color: #95a5a6; }
        .text-center { text-align: center; }
        .text-end { text-align: left; }
        .mt-2 { margin-top: 10px; }
        .mt-3 { margin-top: 15px; }
        .mt-4 { margin-top: 20px; }
        .mb-2 { margin-bottom: 10px; }
        .mb-3 { margin-bottom: 15px; }
        .mb-4 { margin-bottom: 20px; }
        .ms-2 { margin-right: 10px; }
        .me-2 { margin-left: 10px; }
        .d-flex { display: flex; }
        .d-block { display: block; }
        .gap-2 { gap: 10px; }
        .gap-3 { gap: 15px; }
        .flex-wrap { flex-wrap: wrap; }
        .align-items-center { align-items: center; }
        .justify-content-between { justify-content: space-between; }
        .w-100 { width: 100%; }
        .float-left { float: left; }

        /* ===== گرید ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .techniques-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        .row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        /* ===== اسپینر ===== */
        .spinner-border {
            display: inline-block;
            width: 3rem;
            height: 3rem;
            border: 0.25em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner 0.75s linear infinite;
        }
        @keyframes spinner {
            to { transform: rotate(360deg); }
        }
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0,0,0,0);
            border: 0;
        }

        /* ===== پیشرفت ===== */
        .progress {
            height: 5px;
            background: var(--light-bg);
            border-radius: 10px;
            overflow: hidden;
            max-width: 400px;
            margin: 0 auto;
        }
        .progress-bar {
            height: 100%;
            background: var(--secondary-color);
            border-radius: 10px;
            transition: width 0.3s ease;
            color: transparent;
        }
        .progress-bar-striped {
            background-image: linear-gradient(45deg, rgba(255,255,255,0.15) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.15) 50%, rgba(255,255,255,0.15) 75%, transparent 75%, transparent);
            background-size: 1rem 1rem;
        }
        .progress-bar-animated {
            animation: progress-bar-stripes 1s linear infinite;
        }
        @keyframes progress-bar-stripes {
            0% { background-position: 1rem 0; }
            100% { background-position: 0 0; }
        }

        /* ===== پاسخگو ===== */
        @media (max-width: 992px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
            .row {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            .header-nav {
                justify-content: center;
            }
            .header-nav a {
                padding: 6px 10px;
                font-size: 0.8rem;
            }
            .logo-area h1 {
                font-size: 1.4rem;
            }
            .logo-area span {
                display: block;
                font-size: 0.75rem;
                margin-top: 5px;
            }
            .card {
                padding: 15px;
            }
            .card-header {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }
            .card-tools {
                justify-content: flex-start;
            }
            .table {
                font-size: 0.75rem;
            }
            .table th, .table td {
                padding: 8px 10px;
            }
            .stats-grid {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }
            .techniques-grid {
                grid-template-columns: 1fr;
            }
            .btn-lg {
                padding: 10px 20px;
                font-size: 0.95rem;
            }
            .row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .container {
                padding: 0 10px;
            }
            .header-nav {
                flex-wrap: wrap;
                gap: 3px;
            }
            .header-nav a {
                font-size: 0.7rem;
                padding: 4px 8px;
            }
        }
    </style>
</head>
<body>

    <!-- ===== هدر ===== -->
    <header class="main-header">
        <div class="container header-content">
            <div class="logo-area">
                <h1>
                    <i class="fas fa-robot"></i> 
                    BABOK Analyzer
                    <span>| هوش مصنوعی و تحلیل کسب‌و کار</span>
                </h1>
            </div>
            <nav class="header-nav">
                <a href="/babok/public/?route=home" class="<?= ($activePage ?? '') === 'home' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> خانه
                </a>
                <a href="/babok/public/?route=projects" class="<?= ($activePage ?? '') === 'projects' ? 'active' : '' ?>">
                    <i class="fas fa-folder-open"></i> پروژه‌ها
                </a>
                <a href="/babok/public/?route=tasks" class="<?= ($activePage ?? '') === 'tasks' ? 'active' : '' ?>">
                    <i class="fas fa-tasks"></i> وظایف
                </a>
                <a href="/babok/public/?route=techniques" class="<?= ($activePage ?? '') === 'techniques' ? 'active' : '' ?>">
                    <i class="fas fa-tools"></i> تکنیک‌ها
                </a>
                <a href="/babok/public/?route=knowledge_areas" class="<?= ($activePage ?? '') === 'knowledge_areas' ? 'active' : '' ?>">
                    <i class="fas fa-sitemap"></i> حوزه‌های دانشی
                </a>
                <a href="/babok/public/?route=requirement" class="<?= ($activePage ?? '') === 'requirement' ? 'active' : '' ?>">
                    <i class="fas fa-robot"></i> استخراج و تحلیل
                </a>
            </nav>
        </div>
    </header>

    <!-- ===== محتوای اصلی ===== -->
    <main class="container">
        <?= $content ?? '<p>محتوایی برای نمایش وجود ندارد.</p>' ?>
    </main>

    <!-- ===== فوتر ===== -->
    <footer class="main-footer">
        <div class="container">
            <p>
                <i class="fas fa-copyright"></i> <?= date('Y') ?> - 
                <strong>BABOK Analyzer</strong> | 
                مبتنی بر استاندارد <a href="https://www.iiba.org/babok-guide/" target="_blank">BABOK v3</a>
                و توسعه‌یافته با <i class="fas fa-heart" style="color: #e74c3c;"></i> و هوش مصنوعی
            </p>
            <p style="font-size: 0.9rem;">
                <i class="fab fa-github"></i> 
                <a href="https://github.com/HamedRouhani/babok-analyzer/tree/main" target="_blank" style="color: #fff; opacity: 0.9;">
                    مشاهده پروژه در گیت‌هاب
                </a>
            </p>
            <p style="font-size: 0.8rem; opacity: 0.6;">
                <i class="fas fa-code-branch"></i> Inspired by 
                <a href="https://github.com/HamedRouhani/AIProductivitySterategy.ir-WebSite" target="_blank">AI Productivity Strategy</a>
            </p>
        </div>
    </footer>

</body>
</html>