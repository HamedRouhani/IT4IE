<?php
/**
 * صفحه لیست نرم‌افزارها — نسخه 5.0 (فشرده، جستوجوپذیر)
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

        <header class="swl-header">
            <div class="swl-header-main">
                <h1><i class="fas fa-cubes"></i> نرم‌افزارهای تخصصی <span>IT4IE</span></h1>
                <p>فهرست فشردهٔ ابزارها؛ برای شروع، دکمهٔ «اجرا» را بزنید. برای دیدن ویژگی‌ها نشانگر را روی کارت نگه دارید.</p>
            </div>
            <span class="swl-count-chip" data-swl-count><?= (int) $totalSoftware ?> نرم‌افزار</span>
        </header>

        <?php if (empty($softwareList)): ?>
            <div class="swl-empty">
                <i class="fas fa-folder-open"></i>
                <p>در حال حاضر هیچ نرم‌افزاری برای نمایش وجود ندارد.</p>
            </div>
        <?php else: ?>

            <div class="swl-toolbar">
                <label class="swl-search" for="swl-search">
                    <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                    <input id="swl-search" type="search" placeholder="جست‌وجو در نام، توضیح یا ویژگی‌ها…"
                           autocomplete="off" data-swl-search aria-label="جست‌وجوی نرم‌افزار">
                </label>
                <div class="swl-chips" role="group" aria-label="فیلتر وضعیت">
                    <button type="button" class="swl-chip is-active" data-swl-filter="all">همه</button>
                    <button type="button" class="swl-chip" data-swl-filter="stable">Stable</button>
                    <button type="button" class="swl-chip" data-swl-filter="beta">Beta</button>
                    <button type="button" class="swl-chip" data-swl-filter="development">Dev</button>
                </div>
            </div>

            <p class="swl-nomatch" data-swl-empty hidden>نرم‌افزاری با این مشخصات پیدا نشد؛ عبارت دیگری را امتحان کنید.</p>

            <div class="swl-grid" data-swl-grid>
                <?php foreach ($softwareList as $software): ?>
                    <?php
                    $slug = (string) ($software['slug'] ?? '');

                    // تشخیص آیکون و کلاس تم
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

                    // برای ماژول‌های بدون کلاس تم، رنگ از config_json
                    $themeStyle = '';
                    if ($iconInfo['class'] === 'default') {
                        $config = json_decode($software['config_json'] ?? '[]', true);
                        $themeColor = is_array($config) ? ($config['color'] ?? null) : null;
                        $themeStyle = $themeVars($themeColor);
                    }

                    // وضعیت
                    $status = (string) ($software['status'] ?? 'development');
                    $statusInfo = $statusMap[$status] ?? $statusMap['development'];

                    // کلیدواژه‌های جست‌وجو (نام + توضیح + ویژگی‌ها)
                    $features = json_decode($software['features'] ?? '[]', true);
                    if (!is_array($features)) $features = [];

                    $searchText = mb_strtolower(implode(' ', array_filter([
                        $software['name'] ?? '',
                        $software['description'] ?? '',
                        implode(' ', array_slice($features, 0, 6)),
                    ])), 'UTF-8');
                    ?>
                    <article class="swl-card"
                             data-theme="<?= htmlspecialchars($iconInfo['class']) ?>"
                             data-status="<?= htmlspecialchars($status) ?>"
                             data-name="<?= htmlspecialchars($searchText) ?>"<?= $features !== [] ? ' title="' . htmlspecialchars(implode(' • ', array_slice($features, 0, 3)), ENT_QUOTES, 'UTF-8') . '"' : '' ?><?= $themeStyle !== '' ? ' style="' . htmlspecialchars($themeStyle, ENT_QUOTES, 'UTF-8') . '"' : '' ?>>

                        <div class="swl-head">
                            <span class="swl-icon" aria-hidden="true"><i class="<?= htmlspecialchars($iconInfo['icon']) ?>"></i></span>
                            <div class="swl-title">
                                <h3><?= htmlspecialchars($software['name'] ?? '—') ?></h3>
                                <div class="swl-meta">
                                    <span class="swl-badge <?= htmlspecialchars($statusInfo['class']) ?>"><?= htmlspecialchars($statusInfo['label']) ?></span>
                                    <small>v<?= htmlspecialchars($software['version'] ?? '1.0') ?></small>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($software['description'])): ?>
                            <p class="swl-desc"><?= htmlspecialchars($software['description']) ?></p>
                        <?php endif; ?>

                        <div class="swl-foot">
                            <form method="POST" action="/software/run/<?= urlencode($slug) ?>">
                                <button type="submit" class="swl-btn-run">
                                    <i class="fas fa-play-circle" aria-hidden="true"></i>
                                    <span>اجرا</span>
                                </button>
                            </form>

                            <?php if (!empty($software['github_url'])): ?>
                                <a href="<?= htmlspecialchars($software['github_url']) ?>" target="_blank" rel="noopener"
                                   class="swl-btn-sec swl-btn-ico" title="مشاهده در گیت‌هاب" aria-label="گیت‌هاب <?= htmlspecialchars($software['name'] ?? '') ?>">
                                    <i class="fab fa-github" aria-hidden="true"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
(function () {
    'use strict';

    var search = document.querySelector('[data-swl-search]');
    var chips = Array.prototype.slice.call(document.querySelectorAll('[data-swl-filter]'));
    var grid = document.querySelector('[data-swl-grid]');
    var empty = document.querySelector('[data-swl-empty]');
    var counter = document.querySelector('[data-swl-count]');

    if (!grid) return;

    var cards = Array.prototype.slice.call(grid.querySelectorAll('.swl-card'));
    var activeStatus = 'all';

    var applyFilters = function () {
        var term = search && search.value ? search.value.trim().toLowerCase() : '';
        var visible = 0;

        cards.forEach(function (card) {
            var text = (card.getAttribute('data-name') || '').toLowerCase();
            var status = card.getAttribute('data-status') || '';
            var statusOk = activeStatus === 'all' || status === activeStatus;
            var termOk = term === '' || text.indexOf(term) !== -1;
            var show = statusOk && termOk;

            card.hidden = !show;
            if (show) visible++;
        });

        if (empty) empty.hidden = visible !== 0;
        if (counter) counter.textContent = visible + ' نرم‌افزار';
    };

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            chips.forEach(function (item) { item.classList.remove('is-active'); });
            chip.classList.add('is-active');
            activeStatus = chip.getAttribute('data-swl-filter') || 'all';
            applyFilters();
        });
    });

    if (search) {
        search.addEventListener('input', applyFilters);
        search.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                search.value = '';
                applyFilters();
            }
        });
    }

    applyFilters();
})();
</script>
