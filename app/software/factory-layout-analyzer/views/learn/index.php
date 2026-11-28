<main class="fl-page" dir="rtl">
    <header class="fl-page-head">
        <div><span class="fl-eyebrow">مسیر یادگیری</span><h1><i class="fa-solid fa-graduation-cap"></i> مرکز آموزش چیدمان کارخانه</h1><p>هر درس شامل توضیح، فرمول با تعریف نمادها، مثال حل‌شده، خطاهای رایج و تمرین است.</p></div>
        <a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('dashboard')) ?>"><i class="fa-solid fa-house"></i> صفحهٔ اصلی</a>
    </header>

    <section class="fl-stats">
        <article><span>کل درس‌ها</span><strong><?= (int) $totalLessons ?></strong><small>مبانی تا کاربردی</small></article>
        <article><span>درس‌های این فهرست</span><strong><?= count($lessons) ?></strong><small><?= $level === 'all' ? 'همهٔ سطوح' : fl_e($levels[$level]) ?></small></article>
        <article><span>روش محوری</span><strong>SLP</strong><small>با ملاحظات جریان، فضا و روابط</small></article>
    </section>

    <section class="fl-card">
        <div class="fl-card-head"><div><span class="fl-eyebrow">محتوای مرحلهٔ اول</span><h2>درس‌ها</h2></div>
            <nav class="fl-filters" aria-label="فیلتر سطح درس">
                <a class="<?= $level === 'all' ? 'is-active' : '' ?>" href="<?= fl_e(fl_url('learn', 'index')) ?>">همه</a>
                <?php foreach ($levels as $key => $label): ?><a class="<?= $level === $key ? 'is-active' : '' ?>" href="<?= fl_e(fl_url('learn', 'index', ['level' => $key])) ?>"><?= fl_e($label) ?></a><?php endforeach; ?>
            </nav>
        </div>
        <?php if ($lessons): ?>
            <div class="fl-lessons">
                <?php foreach ($lessons as $index => $lesson): ?>
                    <a class="fl-lesson" href="<?= fl_e(fl_url('learn', 'show', ['lesson' => $lesson['id']])) ?>">
                        <span class="fl-lesson-number"><?= $index + 1 ?></span><span class="fl-lesson-icon"><i class="<?= fl_e($lesson['icon']) ?>"></i></span>
                        <span class="fl-lesson-copy"><strong><?= fl_e($lesson['title']) ?></strong><small><?= fl_e($lesson['summary']) ?></small><span class="fl-lesson-meta"><?= fl_e($levels[$lesson['level']] ?? $lesson['level']) ?> · <?= (int) $lesson['minutes'] ?> دقیقه</span></span>
                        <i class="fa-solid fa-chevron-left fl-lesson-arrow" aria-hidden="true"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?><p class="fl-empty">در این سطح درسی موجود نیست.</p><?php endif; ?>
    </section>
    <aside class="fl-note"><i class="fa-solid fa-lightbulb"></i><div><strong>روش مطالعه</strong><p>مثال را با واحدها دوباره محاسبه کنید، سپس تمرین را پاسخ دهید. اگر فرض یا تعریف فاصله عوض شود، نتیجه هم باید با همان فرض جدید بازبینی شود.</p></div></aside>
</main>
