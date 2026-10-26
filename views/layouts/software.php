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

require_once VIEWS_PATH . '/partials/asset-helper.php';

$moduleName = $moduleName ?? 'babok';
$softwareName = $softwareName ?? 'BABOK Analyzer';
$title = $title ?? ($softwareName . ' - IT4IE');
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($settings['site_description'] ?? 'نرم‌افزار تخصصی IT4IE'); ?>">
    <meta name="robots" content="noindex, follow">
    <meta name="theme-color" content="#101b30">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Vazirmatn Font -->
    <link href="https://cdn.jsdelivr.net/gh/rastikerdar/vazirmatn@v33.003/Vazirmatn-font-face.css" rel="stylesheet">
    <?php if ($moduleName): ?>
    <!-- Software views share Bootstrap utilities; load it before site and module styles. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.rtl.min.css" rel="stylesheet" integrity="sha384-CfCrinSRH2IR6a4e6fy2q6ioOX7O6Mtm1L9vRvFZ1trBncWmMePhzvafv7oIcWiW" crossorigin="anonymous">
    <?php endif; ?>
    <!-- IT4IE styles: style + software + [design system] + module + refinement + themes (see asset-helper.php) -->
    <?php echo it4ie_css_software($moduleName); ?>
</head>
<body class="software-mode ui2-shell" data-module="<?php echo htmlspecialchars($moduleName, ENT_QUOTES, 'UTF-8'); ?>"<?php echo it4ie_is_ds($moduleName) ? ' data-ds="1"' : ''; ?>>

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

    <?php if ($moduleName): ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <?php endif; ?>
    <?php if (($moduleName ?? '') === 'inventory'): ?>
    <script src="<?php echo htmlspecialchars(it4ie_asset_url('js/software/inventory.js'), ENT_QUOTES, 'UTF-8'); ?>" defer></script>
    <?php endif; ?>
    <script src="/assets/js/script.js"></script>
</body>
</html>

<?php if (isset($_SESSION['user_id']) && ($moduleName ?? '') === 'babok'): ?>
<script>
// به‌روزرسانی خودکار bell icon هر ۶۰ ثانیه
setInterval(function() {
    fetch('?route=notifications_ajax')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateBellIcon(data.unread_count);
            }
        })
        .catch(error => console.error('Notification refresh error:', error));
}, 60000);

function updateBellIcon(count) {
    const badge = document.querySelector('.notification-badge');
    if (!badge) {
        // اگر badge وجود ندارد ولی count > 0 است، آن را بساز
        if (count > 0) {
            const bell = document.querySelector('a[title="اعلان‌ها"] i');
            if (bell && bell.parentElement) {
                const newBadge = document.createElement('span');
                newBadge.className = 'notification-badge';
                newBadge.textContent = count > 99 ? '99+' : count;
                newBadge.style.cssText = 'position:absolute;top:-6px;right:-8px;background:#ef4444;color:white;font-size:0.65rem;font-weight:700;padding:2px 5px;border-radius:99px;min-width:16px;text-align:center;line-height:1;';
                bell.parentElement.style.position = 'relative';
                bell.parentElement.appendChild(newBadge);
            }
        }
        return;
    }
    
    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = 'inline-block';
    } else {
        badge.style.display = 'none';
    }
}
</script>
<?php endif; ?>
