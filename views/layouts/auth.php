<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'ورود / ثبت‌نام - IT4IE'); ?></title>
    <meta name="description" content="ورود و ثبت‌نام در سایت IT4IE">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Vazirmatn Font -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <!-- IT4IE Styles -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="<?php echo htmlspecialchars($bodyClass ?? 'auth-page'); ?>">
    
    <!-- Header اصلی (مشابه صفحه خانه) -->
    <?php include VIEWS_PATH . '/partials/header.php'; ?>
    
    <!-- Main Content -->
    <main class="auth-main">
        <div class="container auth-container">
            <?php echo $content ?? ''; ?>
        </div>
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