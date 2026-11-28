<?php
$toolLabels = $toolLabels ?? [];
$difficultyLabels = ['beginner' => 'مقدماتی', 'intermediate' => 'متوسط', 'advanced' => 'پیشرفته'];
$methodLabels = [
    'summary_stats'=>'آمار توصیفی', 'TOPSIS'=>'TOPSIS', 'Requirements Analysis'=>'تحلیل نیازمندی',
    'Project Planning'=>'برنامه‌ریزی پروژه', 'MM1'=>'مدل M/M/1', 'Training Program'=>'دورهٔ آموزشی',
    'Preventive Maintenance'=>'نگهداری پیشگیرانه', 'I-MR'=>'نمودار I-MR', 'EOQ و نقطه سفارش'=>'EOQ و نقطه سفارش',
    'زمان‌سنجی مستقیم'=>'زمان‌سنجی مستقیم', 'NPV و تحلیل هزینه'=>'NPV و تحلیل هزینه',
];
$icons = ['statlab-analyzer'=>'fa-chart-column','babok-analyzer'=>'fa-magnifying-glass-chart','pmbok-analyzer'=>'fa-diagram-project','mcdm-analyzer'=>'fa-scale-balanced','or-analyzer'=>'fa-diagram-project','pdm-analyzer'=>'fa-screwdriver-wrench','inventory-analyzer'=>'fa-boxes-stacked','time-study-analyzer'=>'fa-stopwatch','hr-analyzer'=>'fa-users','quality-analyzer'=>'fa-circle-check','engineering-economy'=>'fa-coins','factory-layout-analyzer'=>'fa-industry'];
?>
<section class="research-library" dir="rtl">
    <header class="research-hero">
        <span class="research-eyebrow"><i class="fa-solid fa-graduation-cap" aria-hidden="true"></i> کتابخانهٔ نمونه‌های پژوهشی مهندسی صنایع</span>
        <h1>نمونهٔ آماده را اجرا و در ماژول تکمیل کنید</h1>
        <p>هر نمونه یک مسئلهٔ کاربردی و داده یا فرض آموزشی دارد. با اجرای آن، یک پروژهٔ مستقل در ماژول مربوط ساخته می‌شود تا محاسبات، ورودی‌ها و نتیجه را همان‌جا بررسی و ویرایش کنید.</p>
        <div class="research-hero-actions"><a class="research-button research-button-primary" href="#research-templates">دیدن نمونه‌ها <i class="fa-solid fa-arrow-down" aria-hidden="true"></i></a><span><i class="fa-solid fa-layer-group" aria-hidden="true"></i> نمونهٔ قابل اجرا برای هر ۱۱ ماژول</span></div>
        <div class="research-hero-mark" aria-hidden="true"><i class="fa-solid fa-flask"></i></div>
    </header>
    <nav class="research-filters" aria-label="دسته‌بندی موضوعی نمونه‌ها">
        <a class="<?= empty($activeCategory) ? 'is-active' : '' ?>" href="/research">همهٔ نمونه‌ها</a>
        <?php foreach (($researchCategories ?? []) as $key => $researchCategory): ?><a class="<?= ($activeCategory ?? '') === $key ? 'is-active' : '' ?>" href="/research?category=<?= rawurlencode((string)$key) ?>"><?= htmlspecialchars((string)($researchCategory['title'] ?? ''), ENT_QUOTES, 'UTF-8') ?></a><?php endforeach; ?>
    </nav>
    <section id="research-templates" class="research-template-section">
        <div class="research-section-heading"><div><p class="research-eyebrow">شروع عملی</p><h2><?= !empty($activeCategory) && isset($researchCategories[$activeCategory]) ? 'نمونه‌های ' . htmlspecialchars((string)$researchCategories[$activeCategory]['title'], ENT_QUOTES, 'UTF-8') : (!empty($activeToolInfo) ? 'نمونه‌های ' . htmlspecialchars($activeToolInfo['title'], ENT_QUOTES, 'UTF-8') : 'نمونه‌های آمادهٔ اجرا') ?></h2></div><span class="research-count"><?= number_format(count($templates ?? [])) ?> نمونه</span></div>
        <?php if (!empty($templates)): ?><div class="research-card-grid">
            <?php foreach ($templates as $template): $payload = is_array($template['input_payload'] ?? null) ? $template['input_payload'] : (json_decode($template['input_payload'] ?? '', true) ?: []); ?>
                <article class="research-card">
                    <div class="research-card-top"><span class="research-card-icon"><i class="fa-solid <?= $icons[$template['tool_slug']] ?? 'fa-flask' ?>" aria-hidden="true"></i></span><span class="research-difficulty difficulty-<?= htmlspecialchars($template['difficulty'] ?? 'beginner', ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($difficultyLabels[$template['difficulty'] ?? 'beginner'] ?? 'مقدماتی', ENT_QUOTES, 'UTF-8') ?></span></div>
                    <div class="research-card-meta"><span><?= htmlspecialchars($toolLabels[$template['tool_slug']] ?? 'ابزار پژوهشی', ENT_QUOTES, 'UTF-8') ?></span><span><i class="fa-regular fa-clock" aria-hidden="true"></i> <?= (int)($template['estimated_minutes'] ?? 15) ?> دقیقه</span></div>
                    <h3><?= htmlspecialchars($template['title'], ENT_QUOTES, 'UTF-8') ?></h3><p><?= htmlspecialchars($template['summary'], ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="research-card-footer"><span><i class="fa-solid fa-flask" aria-hidden="true"></i> <?= htmlspecialchars($methodLabels[$template['method_code'] ?? ''] ?? ($template['method_code'] ?? 'تمرین کاربردی'), ENT_QUOTES, 'UTF-8') ?></span><a href="/research/<?= rawurlencode($template['slug']) ?>">مشاهده و اجرای نمونه <i class="fa-solid fa-arrow-left" aria-hidden="true"></i></a></div>
                </article>
            <?php endforeach; ?>
        </div><?php else: ?><div class="research-empty-state"><h3>نمونه‌ای برای این فیلتر پیدا نشد</h3><p>همهٔ نمونه‌ها را ببینید و حوزهٔ موردنظر را انتخاب کنید.</p><a class="research-button research-button-secondary" href="/research">نمایش همهٔ نمونه‌ها</a></div><?php endif; ?>
    </section>
    <aside class="research-how"><span class="research-how-icon"><i class="fa-solid fa-circle-info" aria-hidden="true"></i></span><div><h2>دربارهٔ اجرای نمونه‌ها</h2><p>اجرای نمونه پس از ورود به حساب، نسخه‌ای مستقل از پروژه را در ماژول مربوط ایجاد می‌کند. داده‌ها و فرض‌ها آموزشی و ساختگی‌اند؛ پیش از استفادهٔ پژوهشی یا تصمیم‌گیری، آن‌ها را با داده‌های معتبر خود جایگزین کنید.</p></div></aside>
</section>
