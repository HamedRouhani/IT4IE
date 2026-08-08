<?php
// داده‌های ارسالی: $knowledgeAreas
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حوزه‌های دانشی BABOK - BABOK Analyzer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* (همان استایل‌های بالا) */
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Vazirmatn', 'Tahoma', sans-serif;
            background-color: #f4f7f9;
            color: var(--dark-text);
            line-height: 1.7;
        }
        .container { max-width: 1300px; margin: 0 auto; padding: 0 20px; }
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
        .logo-area i { color: var(--secondary-color); margin-left: 10px; }
        .logo-area span { font-size: 0.9rem; opacity: 0.8; }
        .header-nav a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            margin-right: 20px;
            transition: 0.3s;
            font-size: 0.95rem;
        }
        .header-nav a:hover { color: #fff; }
        .header-nav a.active {
            color: #fff;
            border-bottom: 2px solid var(--secondary-color);
            padding-bottom: 5px;
        }
        .header-nav i { margin-left: 5px; }
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            margin-bottom: 20px;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-bg);
        }
        .card-title {
            font-size: 1.2rem;
            font-weight: 700;
        }
        .card-title i { color: var(--secondary-color); margin-left: 8px; }
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: var(--secondary-color);
            color: white;
        }
        .btn-primary:hover { background: #2980b9; transform: translateY(-2px); }
        .btn-sm { padding: 4px 10px; font-size: 0.8rem; }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-primary {
            background: var(--secondary-color);
            color: white;
        }
        .badge-secondary {
            background: var(--light-bg);
            color: var(--dark-text);
        }
        .badge-success {
            background: var(--success-color);
            color: white;
        }
        .techniques-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .area-card {
            background: white;
            border-right: 5px solid var(--secondary-color);
            border-radius: 12px;
            padding: 16px;
            box-shadow: var(--shadow);
            transition: 0.3s;
        }
        .area-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }
        .area-card .code {
            font-size: 0.8rem;
            color: var(--secondary-color);
            font-weight: 700;
        }
        .area-card .name {
            font-size: 1.1rem;
            font-weight: 700;
            margin: 5px 0;
        }
        .area-card .desc {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 10px;
        }
        .text-muted { color: #95a5a6; }
        .mt-3 { margin-top: 15px; }
        .d-flex { display: flex; }
        .gap-2 { gap: 10px; }
        .flex-wrap { flex-wrap: wrap; }
        .align-items-center { align-items: center; }
        .justify-content-between { justify-content: space-between; }
        .main-footer {
            background: var(--primary-color);
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 40px;
        }
        .main-footer a {
            color: var(--secondary-color);
            text-decoration: none;
        }
        .main-footer p { opacity: 0.8; }
        @media (max-width: 768px) {
            .header-content { flex-direction: column; text-align: center; }
            .header-nav { margin-top: 10px; }
            .header-nav a { margin: 0 10px; }
            .techniques-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <!-- ===== محتوای اصلی ===== -->
    <main class="container">

        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fas fa-sitemap"></i> حوزه‌های دانشی BABOK
                </div>
                <div>
                    <span class="badge badge-primary"><?= count($knowledgeAreas) ?> حوزه</span>
                </div>
            </div>

            <div class="techniques-grid">
                <?php foreach ($knowledgeAreas as $area): ?>
                <div class="area-card" style="border-right-color: <?= match($area['code']) {
                    'KA1' => '#3498db',
                    'KA2' => '#27ae60',
                    'KA3' => '#f39c12',
                    'KA4' => '#e74c3c',
                    'KA5' => '#9b59b6',
                    'KA6' => '#1abc9c',
                    default => '#3498db'
                } ?>;">
                    <div class="code"><?= htmlspecialchars($area['code']) ?></div>
                    <div class="name"><?= htmlspecialchars($area['name']) ?></div>
                    <div class="desc">
                        <?= htmlspecialchars(substr($area['description'] ?? '', 0, 100)) . (strlen($area['description'] ?? '') > 100 ? '...' : '') ?>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge badge-secondary">
                            <i class="fas fa-tasks"></i> <?= $area['task_count'] ?? 0 ?> وظیفه
                        </span>
                        <a href="/babok/public/?route=knowledge_areas_view&id=<?= $area['id'] ?>" class="btn btn-sm btn-primary">
                            <i class="fas fa-eye"></i> مشاهده
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </main>

</body>
</html>