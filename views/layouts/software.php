<?php
/**
 * Layout ترکیبی نرم‌افزارهای ماژولار
 * هدر و فوتر IT4IE + سایدبار نرم‌افزار
 */
if (!isset($settings) || empty($settings)) {
    try {
        $settingModel = new \App\Models\Setting();
        $settings = $settingModel->getAll();
    } catch (\Exception $e) {
        $settings = [];
    }
}

$moduleName = $moduleName ?? 'babok';
$softwareName = $softwareName ?? 'BABOK Analyzer';
$title = $title ?? ($softwareName . ' - IT4IE');
$cssVersion = '2.0'; // برای شکستن کش مرورگر
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($settings['site_description'] ?? 'نرم‌افزار تخصصی IT4IE'); ?>">
    <meta name="robots" content="noindex, follow">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Vazirmatn Font -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <!-- IT4IE Base Styles -->
    <link rel="stylesheet" href="/assets/css/style.css">
    <!-- Software Styles (با نسخه برای شکستن کش) -->
    <link rel="stylesheet" href="/assets/css/software.css?v=<?php echo $cssVersion; ?>">
    <!-- Module Styles -->
    <?php if ($moduleName): ?>
    <link rel="stylesheet" href="/assets/css/modules/<?php echo $moduleName; ?>.css?v=<?php echo $cssVersion; ?>">
    <?php endif; ?>
</head>
<body class="software-mode">

    <!-- Header IT4IE -->
    <?php include VIEWS_PATH . '/partials/header.php'; ?>

    <!-- Wrapper اصلی: سایدبار + محتوا -->
    <div class="software-wrapper">

        <!-- سایدبار نرم‌افزار (در موبایل خودکار افقی می‌شود) -->
        <aside class="software-sidebar">
            <?php include VIEWS_PATH . '/partials/software-sidebar.php'; ?>
        </aside>

        <!-- محتوای اصلی -->
        <main class="software-main">
            <div class="software-content">

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert-software success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['message']); ?></span>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert-software error">
                        <i class="fas fa-exclamation-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['error']); ?></span>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['auth_message'])): ?>
                    <div class="alert-software warning">
                        <i class="fas fa-info-circle"></i>
                        <span><?php echo htmlspecialchars($_SESSION['auth_message']); ?></span>
                    </div>
                    <?php unset($_SESSION['auth_message']); ?>
                <?php endif; ?>

                <?php echo $content ?? ''; ?>
            </div>
        </main>
    </div>

    <script src="/assets/js/script.js"></script>
</body>
</html>