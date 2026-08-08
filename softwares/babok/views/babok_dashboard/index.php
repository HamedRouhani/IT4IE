<?php
// این ویو باید در مسیر views/babok_dashboard/index.php قرار گیرد.
// داده‌های ارسالی از کنترلر: $knowledgeAreas, $totalTasks, $totalTechniques, $recentActivities
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'داشبورد تحلیل کسب و کار - BABOK') ?></title>
    
    <!-- فونت وزیرمتن و فونت‌آسوم (مشابه پروژه اصلی) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* ----- استایل‌های اصلی (برگرفته از پروژه شما) ----- */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #ecf0f1;
            --dark-text: #2c3e50;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* ----- هدر (مشابه پروژه اصلی) ----- */
        .main-header {
            background: linear-gradient(135deg, var(--primary-color), #1a252f);
            color: white;
            padding: 20px 0;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
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
        .logo-area i {
            color: var(--secondary-color);
            margin-left: 10px;
        }
        .logo-area span {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .header-nav a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            margin-right: 20px;
            transition: 0.3s;
            font-size: 0.95rem;
        }
        .header-nav a:hover {
            color: #fff;
        }
        .header-nav i {
            margin-left: 5px;
        }

        /* ----- کارت‌های آمار (دقیقاً مشابه پروژه شما) ----- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            border-right: 5px solid var(--secondary-color);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-3px);
        }
        .stat-card .stat-icon {
            font-size: 2rem;
            color: var(--secondary-color);
            opacity: 0.7;
        }
        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin: 5px 0;
        }
        .stat-card .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }

        /* ----- بخش اصلی (دو ستونه) ----- */
        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .main-grid {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-bg);
        }
        .card-title i {
            color: var(--secondary-color);
            margin-left: 8px;
        }

        /* ----- لیست حوزه‌های دانشی (مشابه منوی درختی پروژه) ----- */
        .knowledge-list {
            list-style: none;
            padding: 0;
        }
        .knowledge-list li {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .knowledge-list li:last-child {
            border-bottom: none;
        }
        .knowledge-list .badge {
            background: var(--secondary-color);
            color: white;
            padding: 2px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
        }

        /* ----- لیست فعالیت‌ها (مشابه پروژه اصلی) ----- */
        .activity-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .activity-item:last-child {
            border-bottom: none;
        }
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--light-bg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 15px;
            color: var(--secondary-color);
        }
        .activity-content {
            flex: 1;
        }
        .activity-content .title {
            font-weight: 500;
        }
        .activity-content .time {
            font-size: 0.8rem;
            color: #7f8c8d;
        }

        .text-muted {
            color: #95a5a6;
        }
        .mt-3 { margin-top: 15px; }
    </style>
</head>
<body>

    <!-- ===== هدر سایت (مشابه پروژه اصلی) ===== -->
    <header class="main-header">
        <div class="container header-content">
            <div class="logo-area">
                <h1>
                    <i class="fas fa-robot"></i> 
                    BABOK Analyzer
                    <span>| هوش مصنوعی و تحلیل کسب و کار</span>
                </h1>
            </div>
            <nav class="header-nav">
                <a href="/babok/public/?route=home"><i class="fas fa-home"></i> خانه</a>
                <a href="/babok/public/?route=projects"><i class="fas fa-folder-open"></i> پروژه‌ها</a>
                <a href="/babok/public/?route=techniques"><i class="fas fa-tools"></i> تکنیک‌ها</a>
                <a href="/babok/public/?route=knowledge_areas"><i class="fas fa-sitemap"></i> حوزه‌های دانشی</a>
            </nav>
        </div>
    </header>

    <!-- ===== محتوای اصلی ===== -->
    <main class="container">

        <!-- کارت‌های آمار -->
        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-sitemap"></i></div>
                <div class="stat-number"><?= count($knowledgeAreas ?? []) ?></div>
                <div class="stat-label">حوزه‌های دانشی</div>
            </div>
            <div class="stat-card" style="border-right-color: var(--success-color);">
                <div class="stat-icon"><i class="fas fa-tasks"></i></div>
                <div class="stat-number"><?= $totalTasks ?? 0 ?></div>
                <div class="stat-label">وظایف BABOK</div>
            </div>
            <div class="stat-card" style="border-right-color: var(--warning-color);">
                <div class="stat-icon"><i class="fas fa-microchip"></i></div>
                <div class="stat-number"><?= $totalTechniques ?? 0 ?></div>
                <div class="stat-label">تکنیک‌های استاندارد</div>
            </div>
            <div class="stat-card" style="border-right-color: var(--danger-color);">
                <div class="stat-icon"><i class="fas fa-rocket"></i></div>
                <div class="stat-number"><?= $activeProjectsCount ?? 0 ?></div>
                <div class="stat-label">پروژه‌های فعال</div>
            </div>
        </section>

        <!-- بخش دو ستونه -->
        <div class="main-grid">

            <!-- ستون راست: حوزه‌های دانشی -->
            <section class="card">
                <h3 class="card-title"><i class="fas fa-diagram-project"></i> حوزه‌های دانشی BABOK</h3>
                <?php if (empty($knowledgeAreas)): ?>
                    <p class="text-muted">هیچ حوزه‌ای یافت نشد.</p>
                <?php else: ?>
                    <ul class="knowledge-list">
                        <?php foreach ($knowledgeAreas as $area): ?>
                            <li>
                                <span>
                                    <i class="fas fa-folder" style="color: var(--secondary-color); margin-left: 8px;"></i>
                                    <?= htmlspecialchars($area['name']) ?>
                                </span>
                                <span class="badge"><?= $area['task_count'] ?? 0 ?> وظیفه</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <div class="mt-3">
                    <a href="/babok/public/?route=knowledge_areas" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-left"></i> مشاهده همه
                    </a>
                </div>
            </section>

            <!-- ستون چپ: فعالیت‌های اخیر -->
            <section class="card">
                <h3 class="card-title"><i class="fas fa-clock"></i> آخرین فعالیت‌ها</h3>
                <?php if (empty($recentActivities)): ?>
                    <p class="text-muted"><i class="fas fa-info-circle"></i> هیچ فعالیتی ثبت نشده است.</p>
                <?php else: ?>
                    <?php foreach ($recentActivities as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="activity-content">
                                <div class="title">
                                    <?= htmlspecialchars($activity['task_name'] ?? 'وظیفه') ?>
                                    <small class="text-muted">(<?= htmlspecialchars($activity['task_code'] ?? '') ?>)</small>
                                </div>
                                <div class="time">
                                    <i class="far fa-calendar-alt"></i> 
                                    <?= date('Y-m-d H:i', strtotime($activity['completed_at'] ?? 'now')) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="mt-3">
                    <a href="/babok/public/?route=tasks" class="btn btn-sm btn-secondary">
                        <i class="fas fa-list"></i> مشاهده همه وظایف
                    </a>
                </div>
            </section>

        </div><!-- /main-grid -->

        <!-- بخش پایین: لینک‌های سریع -->
        <div class="card" style="margin-top: 20px;">
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="/babok/public/?route=projects_create" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> ایجاد پروژه جدید
                </a>
                <a href="/babok/public/?route=techniques" class="btn btn-secondary">
                    <i class="fas fa-search"></i> جستجوی تکنیک‌ها
                </a>
                <a href="/babok/public/?route=recommendations_analyzer" class="btn btn-success">
                    <i class="fas fa-robot"></i> تحلیل نیازمندی با هوش مصنوعی
                </a>
            </div>
        </div>

    </main>

    <!-- ===== فوتر (مشابه پروژه اصلی) ===== -->
    <footer style="background: var(--primary-color); color: white; text-align: center; padding: 20px; margin-top: 40px;">
        <div class="container">
            <p style="opacity: 0.8;">
                <i class="fas fa-copyright"></i> <?= date('Y') ?> - 
                <strong>BABOK Analyzer</strong> | 
                مبتنی بر استاندارد <a href="https://www.iiba.org/babok-guide/" target="_blank" style="color: var(--secondary-color);">BABOK v3</a>
                و توسعه‌یافته با <i class="fas fa-heart" style="color: #e74c3c;"></i> و هوش مصنوعی
            </p>
            <p style="font-size: 0.8rem; opacity: 0.6;">
                <i class="fas fa-code-branch"></i> Inspired by 
                <a href="https://github.com/HamedRouhani/AIProductivitySterategy.ir-WebSite" target="_blank" style="color: var(--secondary-color);">AI Productivity Strategy</a>
            </p>
        </div>
    </footer>

</body>
</html>