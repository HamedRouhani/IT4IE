<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'IT4IE - مشاوره بین‌رشته‌ای'; ?></title>
    <meta name="description" content="<?php echo $settings['site_description'] ?? 'لنگرگاه دیجیتال برای مشاوره و اجرای پروژه‌های بین‌رشته‌ای'; ?>">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Vazirmatn Font -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    
    <!-- Header -->
    <?php include VIEWS_PATH . '/partials/header.php'; ?>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            <!-- محتوا با سایدبار -->
            <div class="content-wrapper">
                <!-- Sidebar - در سمت راست (RTL) -->
                <?php if (!isset($hideSidebar) || !$hideSidebar): ?>
                    <?php include VIEWS_PATH . '/partials/sidebar.php'; ?>
                <?php endif; ?>
                
                <!-- Main Content Area -->
                <div class="content-main">
                    <?php echo $content ?? ''; ?>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Footer -->
    <?php include VIEWS_PATH . '/partials/footer.php'; ?>
    
    <!-- Custom JS -->
    <script src="/assets/js/script.js"></script>
    
    <?php if (isset($_SESSION['message'])): ?>
    <script>
        alert('<?php echo $_SESSION['message']; ?>');
        <?php unset($_SESSION['message']); ?>
    </script>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <script>
        alert('<?php echo $_SESSION['error']; ?>');
        <?php unset($_SESSION['error']); ?>
    </script>
    <?php endif; ?>
    
</body>
</html>