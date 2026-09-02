<?php
/**
 * OR Analyzer - نمایش جزئیات و آموزش یک روش حل
 * مسیر: app/software/or/views/method/show.php
 * نسخه: 2.4 (هدایت هوشمند و مستقیم به کنترلر اختصاصی مسئله بر اساس problem_type_id)
 */

// ──────────────────────────────────────────────
// ۱. ایمن‌سازی متغیرها در برابر خطاهای PHP 8
// ──────────────────────────────────────────────
if (is_object($method)) {
    $method = (array) $method;
}
$method = is_array($method) ? $method : [];

$safeJson = function($data) {
    if (is_string($data)) {
        $decoded = json_decode($data, true);
        return is_array($decoded) ? $decoded : [];
    }
    return is_array($data) ? $data : [];
};

$steps    = $safeJson($method['steps'] ?? []);
$formulas = $safeJson($method['formulas'] ?? []);
$inputs   = $safeJson($method['inputs'] ?? []);
$outputs  = $safeJson($method['outputs'] ?? []);
$pros     = $safeJson($method['pros'] ?? []);
$cons     = $safeJson($method['cons'] ?? []);

$name_fa   = is_string($method['name_fa'] ?? null) ? $method['name_fa'] : 'روش ناشناخته';
$name_en   = is_string($method['name_en'] ?? null) ? $method['name_en'] : '';
$code      = is_string($method['code'] ?? null) ? $method['code'] : '';
$desc      = is_string($method['description'] ?? null) ? $method['description'] : '';
$category  = is_string($method['category'] ?? null) ? $method['category'] : 'initial';
$prob_name = is_string($method['problem_type_name'] ?? null) ? $method['problem_type_name'] : '';
$prob_id   = (int)($method['problem_type_id'] ?? 0);

// ──────────────────────────────────────────────
// 🛡️ منطق هوشمند: هدایت مستقیم به کنترلر اختصاصی مسئله
// ──────────────────────────────────────────────
$controllerMap = [
    1 => 'transport',   // مسئله حمل و نقل
    2 => 'assignment',  // مسئله تخصیص
    3 => 'transship',   // مسئله ترانشیپمنت
    4 => 'shortest',    // کوتاه‌ترین مسیر
    5 => 'simplex'      // برنامه‌ریزی خطی
];

// تعیین کنترلر مقصد بر اساس problem_type_id. در صورت عدم تطابق، به صفحه انتخاب نوع مسئله هدایت می‌شود.
$targetController = $controllerMap[$prob_id] ?? 'problem_type';
$createUrl = or_url("controller={$targetController}&action=create");

$create_project_label = ($targetController !== 'problem_type') 
    ? 'ایجاد پروژه با این روش' 
    : 'انتخاب نوع مسئله و ایجاد پروژه';

// ──────────────────────────────────────────────
// ۲. تنظیمات دسته‌بندی
// ──────────────────────────────────────────────
$categoryStyles = [
    'initial'      => ['color' => 'info',    'icon' => 'fa-play-circle', 'label' => 'روش اولیه (IBFS)'],
    'optimization' => ['color' => 'success', 'icon' => 'fa-chart-line',  'label' => 'بهینه‌سازی'],
    'exact'        => ['color' => 'danger',  'icon' => 'fa-bullseye',    'label' => 'دقیق (Exact)'],
    'heuristic'    => ['color' => 'warning', 'icon' => 'fa-lightbulb',   'label' => 'فراابتکاری'],
];
$cat = $categoryStyles[$category] ?? ['color' => 'secondary', 'icon' => 'fa-tag', 'label' => $category];

// ──────────────────────────────────────────────
// ۳. توابع کمکی
// ──────────────────────────────────────────────
if (!function_exists('orSimpleMarkdown')) {
    function orSimpleMarkdown($text): string {
        if (empty($text) || !is_string($text)) return '<span class="text-muted">توضیحاتی ثبت نشده است.</span>';
        $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $escaped = preg_replace('/\*\*(.*?)\*\*/', '<strong class="text-dark">$1</strong>', $escaped);
        return nl2br($escaped);
    }
}
?>

<link rel="stylesheet" href="/public/assets/css/modules/or.css">

<div class="container-fluid py-4">

    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="<?= or_url('') ?>"><i class="fas fa-home"></i> OR Analyzer</a>
            </li>
            <li class="breadcrumb-item">
                <a href="<?= or_url('controller=method') ?>">روش‌های حل</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= htmlspecialchars($name_fa, ENT_QUOTES, 'UTF-8') ?>
            </li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm mb-4 or-header-card text-white">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge bg-<?= $cat['color'] ?> fs-6">
                            <i class="fas <?= $cat['icon'] ?> me-1"></i>
                            <?= htmlspecialchars($cat['label'], ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span class="badge bg-light text-dark fs-6">
                            <i class="fas fa-code me-1"></i>
                            <?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <?php if (!empty($prob_name)): ?>
                            <span class="badge bg-white text-primary fs-6">
                                <i class="fas fa-puzzle-piece me-1"></i>
                                <?= htmlspecialchars($prob_name, ENT_QUOTES, 'UTF-8') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <h2 class="mb-1 fw-bold"><?= htmlspecialchars($name_fa, ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="mb-0 opacity-75 font-monospace"><?= htmlspecialchars($name_en, ENT_QUOTES, 'UTF-8') ?></p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <!-- 🔧 دکمه اصلاح شده: هدایت مستقیم به کنترلر اختصاصی (مثلاً transport, assignment, ...) -->
                    <a href="<?= $createUrl ?>" class="btn btn-light btn-lg shadow-sm">
                        <i class="fas fa-plus-circle"></i> 
                        <?= htmlspecialchars($create_project_label, ENT_QUOTES, 'UTF-8') ?>
                    </a>
                    <a href="<?= or_url('controller=method') ?>" class="btn btn-outline-light btn-sm mt-2 d-block">
                        <i class="fas fa-arrow-right"></i> بازگشت به لیست روش‌ها
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <?php if (!empty($desc)): ?>
            <div class="card border-0 shadow-sm mb-4 or-section" id="sec-description">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-book-open text-primary me-2"></i>
                        معرفی و مفهوم روش
                    </h5>
                </div>
                <div class="card-body">
                    <div class="or-description lh-lg fs-6">
                        <?= orSimpleMarkdown($desc) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($steps)): ?>
            <div class="card border-0 shadow-sm mb-4 or-section" id="sec-steps">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="fas fa-shoe-prints text-warning me-2"></i>
                        گام‌های اجرایی الگوریتم
                        <span class="badge bg-warning text-dark ms-2"><?= count($steps) ?> گام</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="or-steps-list">
                        <?php foreach ($steps as $i => $step): ?>
                            <div class="or-step-item d-flex mb-4 <?= $i === count($steps) - 1 ? 'mb-0' : '' ?>">
                                <div class="or-step-number bg-warning bg-opacity-10 text-warning 
                                            d-flex align-items-center justify-content-center 
                                            flex-shrink-0 ms-3 rounded-circle">
                                    <strong><?= $i + 1 ?></strong>
                                </div>
                                <div class="or-step-body flex-grow-1">
                                    <h6 class="fw-bold mb-1 text-dark">
                                        <?= htmlspecialchars(is_string($step['title'] ?? null) ? $step['title'] : 'گام ' . ($i + 1), ENT_QUOTES, 'UTF-8') ?>
                                    </h6>
                                    <p class="text-muted mb-0 small lh-lg">
                                        <?= nl2br(htmlspecialchars(is_string($step['description'] ?? null) ? $step['description'] : '', ENT_QUOTES, 'UTF-8')) ?>
                                    </p>
                                </div>
                            </div>
                            <?php if ($i < count($steps) - 1): ?>
                                <hr class="or-step-divider my-0">
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($formulas)): ?>
            <div class="card border-0 shadow-sm mb-4 or-section" id="sec-formulas">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-superscript text-danger me-2"></i>
                        فرمول‌های کلیدی
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php foreach ($formulas as $formula): ?>
                            <div class="col-md-<?= count($formulas) === 1 ? '12' : '6' ?>">
                                <div class="or-formula-box h-100">
                                    <div class="or-formula-header bg-danger bg-opacity-10 text-danger p-2 px-3 rounded-top">
                                        <i class="fas fa-square-root-alt me-1"></i>
                                        <strong class="small"><?= htmlspecialchars(is_string($formula['name'] ?? null) ? $formula['name'] : 'فرمول', ENT_QUOTES, 'UTF-8') ?></strong>
                                    </div>
                                    <div class="or-formula-body bg-light p-3 text-center">
                                        <code class="fs-5 text-dark user-select-all"><?= htmlspecialchars(is_string($formula['text'] ?? null) ? $formula['text'] : '', ENT_QUOTES, 'UTF-8') ?></code>
                                    </div>
                                    <?php if (!empty($formula['description']) && is_string($formula['description'])): ?>
                                        <div class="or-formula-desc p-2 px-3 border-top small text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            <?= htmlspecialchars($formula['description'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($inputs) || !empty($outputs)): ?>
            <div class="card border-0 shadow-sm mb-4 or-section" id="sec-io">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-exchange-alt text-success me-2"></i>
                        داده‌های ورودی و خروجی
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <?php if (!empty($inputs)): ?>
                        <div class="<?= !empty($outputs) ? 'col-md-6' : 'col-12' ?>">
                            <h6 class="text-primary mb-3 pb-2 border-bottom">
                                <i class="fas fa-sign-in-alt me-1"></i>
                                ورودی‌های مورد نیاز
                            </h6>
                            <div class="list-group list-group-flush">
                                <?php foreach ($inputs as $input): ?>
                                    <div class="list-group-item border-0 ps-0 py-2 d-flex align-items-center">
                                        <span class="or-io-icon or-io-input me-2">
                                            <i class="fas fa-arrow-left"></i>
                                        </span>
                                        <span class="small"><?= htmlspecialchars(is_string($input) ? $input : '', ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($outputs)): ?>
                        <div class="<?= !empty($inputs) ? 'col-md-6' : 'col-12' ?>">
                            <h6 class="text-success mb-3 pb-2 border-bottom">
                                <i class="fas fa-sign-out-alt me-1"></i>
                                خروجی‌های تولیدی
                            </h6>
                            <div class="list-group list-group-flush">
                                <?php foreach ($outputs as $output): ?>
                                    <div class="list-group-item border-0 ps-0 py-2 d-flex align-items-center">
                                        <span class="or-io-icon or-io-output me-2">
                                            <i class="fas fa-arrow-right"></i>
                                        </span>
                                        <span class="small"><?= htmlspecialchars(is_string($output) ? $output : '', ENT_QUOTES, 'UTF-8') ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-4">
            <div class="or-sticky-sidebar">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-2">
                        <h6 class="mb-0">
                            <i class="fas fa-compass text-primary me-2"></i>
                            ناوبری سریع
                        </h6>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php if (!empty($desc)): ?>
                        <a href="#sec-description" class="list-group-item list-group-item-action py-2 small">
                            <i class="fas fa-book-open me-2 text-primary"></i> معرفی روش
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($steps)): ?>
                        <a href="#sec-steps" class="list-group-item list-group-item-action py-2 small">
                            <i class="fas fa-shoe-prints me-2 text-warning"></i>
                            گام‌های اجرایی
                            <span class="badge bg-light text-dark float-end"><?= count($steps) ?></span>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($formulas)): ?>
                        <a href="#sec-formulas" class="list-group-item list-group-item-action py-2 small">
                            <i class="fas fa-superscript me-2 text-danger"></i>
                            فرمول‌ها
                            <span class="badge bg-light text-dark float-end"><?= count($formulas) ?></span>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($inputs) || !empty($outputs)): ?>
                        <a href="#sec-io" class="list-group-item list-group-item-action py-2 small">
                            <i class="fas fa-exchange-alt me-2 text-success"></i>
                            ورودی / خروجی
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-2">
                        <h6 class="mb-0">
                            <i class="fas fa-info-circle text-info me-2"></i>
                            اطلاعات کلیدی
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted small ps-3 py-2" style="width:40%;">کد روش</td>
                                    <td class="small py-2"><code><?= htmlspecialchars($code, ENT_QUOTES, 'UTF-8') ?></code></td>
                                </tr>
                                <tr>
                                    <td class="text-muted small ps-3 py-2">نام انگلیسی</td>
                                    <td class="small py-2"><?= htmlspecialchars($name_en, ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                                <tr>
                                    <td class="text-muted small ps-3 py-2">دسته‌بندی</td>
                                    <td class="py-2">
                                        <span class="badge bg-<?= $cat['color'] ?> small"><?= htmlspecialchars($cat['label'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </td>
                                </tr>
                                <?php if (!empty($prob_name)): ?>
                                <tr>
                                    <td class="text-muted small ps-3 py-2">نوع مسئله</td>
                                    <td class="small py-2"><?= htmlspecialchars($prob_name, ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td class="text-muted small ps-3 py-2">تعداد گام‌ها</td>
                                    <td class="small py-2"><?= count($steps) ?> گام</td>
                                </tr>
                                <tr>
                                    <td class="text-muted small ps-3 py-2">تعداد فرمول‌ها</td>
                                    <td class="small py-2"><?= count($formulas) ?> فرمول</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if (!empty($pros) || !empty($cons)): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-2">
                        <h6 class="mb-0">
                            <i class="fas fa-balance-scale text-warning me-2"></i>
                            نقاط قوت و ضعف
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($pros)): ?>
                            <h6 class="text-success small mb-2 fw-bold">
                                <i class="fas fa-thumbs-up me-1"></i> مزایا
                            </h6>
                            <ul class="list-unstyled mb-3">
                                <?php foreach ($pros as $pro): ?>
                                    <li class="mb-2 d-flex align-items-start">
                                        <i class="fas fa-check-circle text-success mt-1 me-2" style="font-size:.75rem;"></i>
                                        <span class="small flex-grow-1"><?= htmlspecialchars(is_string($pro) ? $pro : '', ENT_QUOTES, 'UTF-8') ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if (!empty($cons)): ?>
                            <h6 class="text-danger small mb-2 fw-bold">
                                <i class="fas fa-exclamation-triangle me-1"></i> محدودیت‌ها
                            </h6>
                            <ul class="list-unstyled mb-0">
                                <?php foreach ($cons as $con): ?>
                                    <li class="mb-2 d-flex align-items-start">
                                        <i class="fas fa-times-circle text-danger mt-1 me-2" style="font-size:.75rem;"></i>
                                        <span class="small flex-grow-1"><?= htmlspecialchars(is_string($con) ? $con : '', ENT_QUOTES, 'UTF-8') ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 🔧 CTA اصلاح شده: هدایت مستقیم به کنترلر اختصاصی -->
                <div class="card border-0 shadow-sm or-cta-card text-white">
                    <div class="card-body text-center py-4 px-3">
                        <div class="mb-3">
                            <i class="fas fa-rocket fa-2x opacity-75"></i>
                        </div>
                        <h6 class="mb-2 fw-bold">آماده حل مسئله هستید؟</h6>
                        <p class="small mb-3 opacity-75">
                            <?php if ($targetController !== 'problem_type'): ?>
                                یک پروژه جدید از نوع <strong><?= htmlspecialchars($prob_name, ENT_QUOTES, 'UTF-8') ?></strong> ایجاد کنید.
                            <?php else: ?>
                                ابتدا نوع مسئله خود را انتخاب کنید، سپس از این روش استفاده نمایید.
                            <?php endif; ?>
                        </p>
                        <a href="<?= $createUrl ?>" class="btn btn-light w-100 fw-bold shadow-sm">
                            <i class="fas fa-plus me-1"></i> شروع پروژه جدید
                        </a>
                        <a href="<?= or_url('controller=method') ?>" class="btn btn-outline-light btn-sm w-100 mt-2">
                            <i class="fas fa-list me-1"></i> مشاهده همه روش‌ها
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>