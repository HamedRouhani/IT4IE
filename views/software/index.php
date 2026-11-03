<?php
/**
 * صفحه لیست نرم‌افزارها — نسخه 4.0 (رنگ‌محور)
 * مسیر: views/software/index.php
 */
$softwareList  = $softwareList  ?? [];
$totalSoftware = $totalSoftware ?? count($softwareList);

// نقشه‌ی تشخیص ماژول بر اساس slug: آیکون + کلید تم (رنگ از software.css خوانده می‌شود)
$slugMap = [
    'babok-analyzer'      => ['icon' => 'fas fa-robot',           'class' => 'babok'],
    'pmbok-analyzer'      => ['icon' => 'fas fa-project-diagram', 'class' => 'pmbok'],
    'mcdm-analyzer'       => ['icon' => 'fas fa-scale-balanced',  'class' => 'mcdm'],
    'or-analyzer'         => ['icon' => 'fas fa-gears',           'class' => 'or'],
    'statlab-analyzer'    => ['icon' => 'fas fa-chart-line',      'class' => 'statlab'],
    'pdm-analyzer'        => ['icon' => 'fas fa-industry',        'class' => 'pdm'],
    'inventory-analyzer'  => ['icon' => 'fas fa-boxes-stacked',   'class' => 'inventory'],
    'time-study-analyzer' => ['icon' => 'fas fa-stopwatch',       'class' => 'time-study-analyzer'],
    'engineering-economy' => ['icon' => 'fas fa-coins',           'class' => 'engineering-economy'],
    'hr-analyzer'         => ['icon' => 'fas fa-users',           'class' => 'hr'],
    'quality-analyzer'    => ['icon' => 'fas fa-clipboard-check', 'class' => 'quality'],
    'itil-analyzer'       => ['icon' => 'fas fa-server',          'class' => 'itil'],
    'togaf-analyzer'      => ['icon' => 'fas fa-network-wired',   'class' => 'togaf'],
];

// برچسب و کلاس وضعیت
$statusMap = [
    'stable'      => ['label' => 'Stable',     'class' => 'stable'],
    'beta'        => ['label' => 'Beta',       'class' => 'beta'],
    'development' => ['label' => 'Dev',        'class' => 'development'],
    'deprecated'  => ['label' => 'Deprecated', 'class' => 'deprecated'],
];

/**
 * ساخت متغیرهای رنگ کارت از یک رنگ هگز.
 * فقط برای ماژول‌هایی استفاده می‌شود که هنوز کلاس تم در software.css ندارند،
 * تا رنگ کارت از config_json همین ماژول گرفته شود.
 */
$themeVars = static function ($color): string {
    if (!is_string($color) || trim($color) === '') return '';

    $hex = ltrim(trim($color), '#');
    if (strlen($hex) === 3) $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) return '';

    $red = (int) hexdec(substr($hex, 0, 2));
    $green = (int) hexdec(substr($hex, 2, 2));
    $blue = (int) hexdec(substr($hex, 4, 2));
    $dark = sprintf('#%02x%02x%02x', (int) round($red * 0.78), (int) round($green * 0.78), (int) round($blue * 0.78));

    return sprintf('--sw-color:#%s;--sw-color-dark:%s;--sw-color-rgb:%d,%d,%d', strtolower($hex), $dark, $red, $green, $blue);
};

// نسخهٔ فایل CSS بر پایهٔ زمان تغییر، تا بعد از هر آپلود کش مرورگر عوض شود.
$publicPath = defined('PUBLIC_PATH') ? PUBLIC_PATH : dirname(__DIR__, 2) . '/public';
$softwareCssVersion = (int) (@filemtime($publicPath . '/assets/css/software.css') ?: 1);
?>

<link rel="stylesheet" href="/assets/css/software.css?v=<?= $softwareCssVersion ?>">

<div class="swl-page">
    <div class="swl-container">

        <!-- Header -->
        <div class="swl-header">
            <h1>
                <i class="fas fa-cubes"></i>
                نرم‌افزارهای تخصصی
                <span>IT4IE</span>
            </h1>
            <p>ابزارها و تحلیلگرهای پیشرفته برای مدیریت کسب‌وکار و پروژه</p>
        </div>

        <!-- Stat -->
        <?php if ($totalSoftware > 0): ?>
            <div class="swl-stat-wrap">
                <div class="swl-stat">
                    <div class="swl-stat-icon">
                        <i class="fas fa-cubes"></i>
                    </div>
                    <div class="swl-stat-info">
                        <h3><?= (int) $totalSoftware ?></h3>
                        <p>نرم‌افزار فعال</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Grid -->
        <?php if (empty($softwareList)): ?>
            <div class="swl-empty">
                <i class="fas fa-folder-open"></i>
                <p>در حال حاضر هیچ نرم‌افزاری برای نمایش وجود ندارد.</p>
            </div>
        <?php else: ?>
            <div class="swl-grid">
                <?php foreach ($softwareList as $software): ?>
                    <?php
                    $slug = $software['slug'] ?? '';

                    // تشخیص آیکون و کلاس بر اساس slug
                    $iconInfo = $slugMap[$slug] ?? ['icon' => 'fas fa-cube', 'class' => 'default'];

                    // fallback: تشخیص بر اساس نام (طولانی‌ترین کلید منطبق، با مرز واژه)
                    if ($iconInfo['class'] === 'default') {
                        $nameLower = strtolower($software['name'] ?? '');
                        $bestMatch = null;
                        $bestLength = 0;
                        foreach ($slugMap as $key => $info) {
                            $keyword = explode('-', $key)[0];
                            if (strlen($keyword) <= $bestLength) continue;
                            if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $nameLower)) {
                                $bestMatch = $info;
                                $bestLength = strlen($keyword);
                            }
                        }
                        if ($bestMatch !== null) $iconInfo = $bestMatch;
                    }

                    // وضعیت
                    $status = $software['status'] ?? 'development';
                    $statusInfo = $statusMap[$status] ?? $statusMap['development'];

                    // ویژگی‌ها
                    $features = json_decode($software['features'] ?? '[]', true);
                    if (!is_array($features)) $features = [];

                    // تکنولوژی‌ها
                    $techStack = json_decode($software['tech_stack'] ?? '[]', true);
                    if (!is_array($techStack)) $techStack = [];

                    // برای ماژول‌هایی که کلاس تم مشخصی ندارند، رنگ از config_json همان ماژول خوانده می‌شود.
                    $themeStyle = '';
                    if ($iconInfo['class'] === 'default') {
                        $config = json_decode($software['config_json'] ?? '[]', true);
                        $themeColor = is_array($config) ? ($config['color'] ?? null) : null;
                        $themeStyle = $themeVars($themeColor);
                    }
                    ?>
                    <div class="swl-card" data-theme="<?= htmlspecialchars($iconInfo['class']) ?>"<?= $themeStyle !== '' ? ' style="' . htmlspecialchars($themeStyle, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>

                        <!-- Badge وضعیت -->
                        <span class="swl-badge <?= htmlspecialchars($statusInfo['class']) ?>">
                            <?= htmlspecialchars($statusInfo['label']) ?>
                        </span>

                        <!-- Head -->
                        <div class="swl-head">
                            <div class="swl-icon">
                                <i class="<?= htmlspecialchars($iconInfo['icon']) ?>"></i>
                            </div>
                            <div class="swl-title">
                                <h3><?= htmlspecialchars($software['name'] ?? '—') ?></h3>
                                <small>
                                    <i class="fas fa-tag"></i>
                                    نسخه <?= htmlspecialchars($software['version'] ?? '1.0') ?>
                                </small>
                            </div>
                        </div>

                        <!-- Description -->
                        <?php if (!empty($software['description'])): ?>
                            <p class="swl-desc">
                                <?= htmlspecialchars($software['description']) ?>
                            </p>
                        <?php endif; ?>

                        <!-- Tech Stack -->
                        <?php if (!empty($techStack)): ?>
                            <div class="swl-tech">
                                <?php foreach (array_slice($techStack, 0, 4) as $tech): ?>
                                    <span><?= htmlspecialchars($tech) ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Features -->
                        <?php if (!empty($features)): ?>
                            <ul class="swl-features">
                                <?php foreach (array_slice($features, 0, 3) as $feature): ?>
                                    <li>
                                        <i class="fas fa-check-circle"></i>
                                        <span><?= htmlspecialchars($feature) ?></span>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (count($features) > 3): ?>
                                    <li class="more">
                                        ... و <?= count($features) - 3 ?> مورد دیگر
                                    </li>
                                <?php endif; ?>
                            </ul>
                        <?php endif; ?>

                        <!-- Footer -->
                        <div class="swl-foot">

                            <form method="POST" action="/software/run/<?= urlencode($slug) ?>">
                                <button type="submit" class="swl-btn-run">
                                    <i class="fas fa-play-circle"></i>
                                    <span>ورود و اجرا</span>
                                </button>
                            </form>

                            <?php if (!empty($software['github_url'])): ?>
                                <a href="<?= htmlspecialchars($software['github_url']) ?>"
                                   target="_blank"
                                   rel="noopener"
                                   class="swl-btn-sec swl-btn-ico"
                                   title="مشاهده در گیت‌هاب">
                                    <i class="fab fa-github"></i>
                                </a>
                            <?php endif; ?>

                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>