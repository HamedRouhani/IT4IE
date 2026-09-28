<?php
$toolLabels = [
    'statlab-analyzer' => 'آمار و تحلیل داده',
    'mcdm-analyzer' => 'تصمیم‌گیری چندمعیاره',
    'babok-analyzer' => 'تحلیل کسب‌وکار BABOK',
    'pmbok-analyzer' => 'مدیریت پروژه PMBOK',
    'or-analyzer' => 'تحقیق در عملیات',
    'hr-analyzer' => 'مدیریت منابع انسانی',
    'pdm-analyzer' => 'نگهداری و تعمیرات',
    'quality-analyzer' => 'مدیریت کیفیت',
];
$difficultyLabels = ['beginner' => 'مقدماتی', 'intermediate' => 'متوسط', 'advanced' => 'پیشرفته'];
$methodLabels = ['summary_stats' => 'آمار توصیفی', 'one_sample_t' => 'آزمون t تک‌نمونه‌ای', 'TOPSIS' => 'TOPSIS', 'AHP' => 'AHP'];
?>
<section class="research-library" dir="rtl">
    <header class="research-hero">
        <span class="research-eyebrow"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> کتابخانهٔ تمرین مهندسی صنایع</span>
        <h1>از یک مسئلهٔ واقعی‌نما شروع کن</h1>
        <p>نمونه را ببین، دادهٔ آماده را بررسی کن و با یک انتخاب، نسخهٔ مستقل آن را در پروژه‌های خودت بساز.</p>
        <div class="research-hero-actions"><a class="research-button research-button-primary" href="#research-templates">دیدن نمونه‌ها <i class="fa-solid fa-arrow-down" aria-hidden="true"></i></a><span><i class="fa-solid fa-circle-check" aria-hidden="true"></i> بدون ورود دستی دادهٔ نمونه</span></div>
        <div class="research-hero-mark" aria-hidden="true"><i class="fa-solid fa-chart-line"></i></div>
    </header>
    <nav class="research-filters" aria-label="دسته‌بندی نمونه‌ها">
        <a class="<?= empty($activeTool) ? 'is-active' : '' ?>" href="/research">همهٔ نمونه‌ها</a>
        <?php foreach ($toolLabels as $slug => $label): ?><a class="<?= ($activeTool ?? '') === $slug ? 'is-active' : '' ?>" href="/research?tool=<?= rawurlencode($slug) ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a><?php endforeach; ?>
    </nav>
    <section id="research-templates" class="research-template-section">
        <div class="research-section-heading"><div><p class="research-eyebrow">شروع خودخدمت</p><h2>نمونه‌های آمادهٔ اجرا</h2></div><span class="research-count"><?= number_format(count($templates ?? [])) ?> تمرین</span></div>
        <?php if (!empty($templates)): ?><div class="research-card-grid">
            <?php foreach ($templates as $template): $payload=json_decode($template['input_payload'] ?? '', true) ?: []; $icon=($template['tool_slug'] === 'mcdm-analyzer') ? 'fa-scale-balanced' : 'fa-chart-column'; ?>
                <article class="research-card"><div class="research-card-top"><span class="research-card-icon"><i class="fa-solid <?= $icon ?>" aria-hidden="true"></i></span><span class="research-difficulty difficulty-<?= htmlspecialchars($template['difficulty'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($difficultyLabels[$template['difficulty']] ?? 'مقدماتی', ENT_QUOTES, 'UTF-8') ?></span></div>
                    <div class="research-card-meta"><span><?= htmlspecialchars($toolLabels[$template['tool_slug']] ?? 'ابزار پژوهشی', ENT_QUOTES, 'UTF-8') ?></span><span><i class="fa-regular fa-clock" aria-hidden="true"></i> <?= (int)$template['estimated_minutes'] ?> دقیقه</span></div>
                    <h3><?= htmlspecialchars($template['title'], ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars($template['summary'], ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="research-card-footer"><span><i class="fa-solid fa-flask" aria-hidden="true"></i> <?= htmlspecialchars($methodLabels[$template['method_code'] ?? ''] ?? 'تمرین کاربردی', ENT_QUOTES, 'UTF-8') ?></span><a href="/research/<?= rawurlencode($template['slug']) ?>">مشاهده و اجرا <i class="fa-solid fa-arrow-left" aria-hidden="true"></i></a></div>
                </article>
            <?php endforeach; ?>
        </div><?php else: ?><div class="research-empty-state"><h3>هنوز نمونه‌ای در این دسته منتشر نشده است</h3><p>دستهٔ دیگری را انتخاب کنید.</p><a class="research-button research-button-secondary" href="/research">نمایش همه</a></div><?php endif; ?>
    </section>
    <aside class="research-how"><span class="research-how-icon"><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i></span><div><h2>از نمونه تا پروژهٔ شخصی</h2><p>نمونه را انتخاب کنید؛ داده‌ها کپی می‌شوند و یک پروژهٔ تازه به حساب شما اضافه می‌شود. نسخهٔ اصلی نمونه برای همه ثابت می‌ماند.</p></div><span class="research-how-steps">۱. انتخاب نمونه <i class="fa-solid fa-arrow-left"></i> ۲. ساخت پروژه <i class="fa-solid fa-arrow-left"></i> ۳. ادامهٔ تحلیل</span></aside>
</section>