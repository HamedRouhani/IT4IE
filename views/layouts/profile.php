<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'پروفایل - IT4IE'; ?></title>
    <meta name="description" content="<?php echo $settings['site_description'] ?? 'لنگرگاه دیجیتال برای مشاوره و اجرای پروژه‌های بین‌رشته‌ای'; ?>">
    <meta name="robots" content="noindex, follow">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Vazirmatn Font -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="profile-page">
    
    <!-- فقط Header سایت (بدون سایدبار و فوتر) -->
    <?php include VIEWS_PATH . '/partials/header.php'; ?>
    
    <!-- محتوای پروفایل با عرض کامل -->
    <main class="profile-main">
        <?php echo $content ?? ''; ?>
    </main>
    
    <!-- Custom JS -->
    <script src="/assets/js/script.js"></script>
    
    <?php if (isset($_SESSION['message'])): ?>
    <script>
        alert('<?php echo addslashes($_SESSION['message']); ?>');
        <?php unset($_SESSION['message']); ?>
    </script>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <script>
        alert('<?php echo addslashes($_SESSION['error']); ?>');
        <?php unset($_SESSION['error']); ?>
    </script>
    <?php endif; ?>
    
</body>
</html>