<main class="fl-page" dir="rtl">
    <header class="fl-hero">
        <span class="fl-eyebrow"><i class="fa-solid fa-industry"></i> Factory Layout Analyzer · نسخهٔ آموزشی اولیه</span>
        <h1>طراحی چیدمان کارخانه را از مبانی یاد بگیرید</h1>
        <p>از شناخت محصول و جریان تا نمودار روابط، نیاز فضا و ساخت گزینه‌های چیدمان؛ با فرمول‌های روشن، مثال حل‌شده و تمرین.</p>
        <div class="fl-actions">
            <a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('learn', 'index')) ?>"><i class="fa-solid fa-graduation-cap"></i> شروع مسیر آموزش</a>
            <?php if ($firstLesson): ?><a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('learn', 'show', ['lesson' => $firstLesson['id']])) ?>">رفتن به درس اول <i class="fa-solid fa-arrow-left"></i></a><?php endif; ?>
        </div>
    </header>

    <section class="fl-stats" aria-label="وضعیت محتوای آموزشی">
        <article><span>درس‌های آماده</span><strong><?= (int) $lessonCount ?></strong><small>مبانی تا کاربردی</small></article>
        <article><span>روش پایه</span><strong>SLP</strong><small>برنامه‌ریزی نظام‌مند چیدمان</small></article>
        <article><span>تمرکز نسخهٔ فعلی</span><strong>آموزش</strong><small>مفهوم، فرمول، مثال و تمرین</small></article>
    </section>

    <section class="fl-card">
        <div class="fl-card-head"><div><span class="fl-eyebrow">نقشهٔ راه</span><h2>ترتیب کار در یک پروژهٔ چیدمان</h2></div></div>
        <ol class="fl-process">
            <li><span>۱</span><div><strong>داده‌های مسئله</strong><small>محصول، مقدار، مسیر، خدمات و زمان‌بندی (P-Q-R-S-T)</small></div></li>
            <li><span>۲</span><div><strong>تحلیل جریان و روابط</strong><small>نمودار از-به و رابطهٔ کیفی فعالیت‌ها، همراه با دلیل</small></div></li>
            <li><span>۳</span><div><strong>نیاز فضا و محدودیت‌ها</strong><small>فضای کاری، دسترسی، تجهیزات و قیود محل</small></div></li>
            <li><span>۴</span><div><strong>گزینه‌های بلوکی و ارزیابی</strong><small>ابتدا رعایت قیود قطعی، سپس مقایسهٔ شفاف گزینه‌های مجاز</small></div></li>
        </ol>
    </section>

    <aside class="fl-note"><i class="fa-solid fa-circle-info"></i><div><strong>وضعیت توسعه</strong><p>این گام، پایهٔ آموزشی و تعاریف را فراهم می‌کند. محیط ثبت پروژه و ترسیم چیدمان در مرحله‌های بعدی ساخته می‌شود؛ بنابراین هنوز خروجی اجرایی یا نقشهٔ قابل ساخت تولید نمی‌کند.</p></div></aside>
</main>
