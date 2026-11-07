<section class="inv-page" dir="rtl">
    <header class="inv-page-head">
        <div>
            <span class="inv-eyebrow">مرکز آموزش</span>
            <h1><i class="fa-solid fa-graduation-cap"></i> کنترل موجودی را گام‌به‌گام بیاموزید</h1>
            <p>هشت درس کوتاه با فرمول، مثال حل‌شده، خطاهای رایج و تمرین؛ به‌همراه واژه‌نامه و ماشین‌حساب تعاملی.</p>
        </div>
        <div class="inv-head-actions">
            <a class="inv-btn inv-btn-primary" href="<?= inv_e(inv_url('learn', 'tools')) ?>"><i class="fa-solid fa-calculator"></i> ماشین‌حساب‌ها</a>
            <a class="inv-btn inv-btn-secondary" href="<?= inv_e(inv_url('learn', 'glossary')) ?>"><i class="fa-solid fa-book"></i> واژه‌نامه</a>
            <a class="inv-btn inv-btn-ghost" href="<?= inv_e(inv_url('dashboard')) ?>">داشبورد موجودی</a>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="inv-alert <?= inv_e($flash['type']) ?>" role="status"><?= inv_e($flash['message']) ?></div>
    <?php endif; ?>

    <section class="inv-kpis">
        <article class="inv-kpi is-accent"><span>درس‌ها</span><strong><?= (int) $totalLessons ?></strong><small>از پایه تا پیشرفته</small></article>
        <article class="inv-kpi"><span>واژه‌های کلیدی</span><strong><?= (int) $glossaryCount ?></strong><small>با معادل فارسی</small></article>
        <article class="inv-kpi"><span>ماشین‌حساب</span><strong>۵</strong><small>EOQ، اطمینان، ABC، سیاست، KPI</small></article>
        <article class="inv-kpi"><span>مثال حل‌شده</span><strong><?= (int) $totalLessons ?></strong><small>با اعداد قابل بازتولید</small></article>
    </section>

    <section class="inv-card">
        <div class="inv-card-head">
            <div><span class="inv-eyebrow">مسیر یادگیری</span><h2>فهرست درس‌ها</h2></div>
            <div class="inv-head-actions">
                <a class="inv-btn <?= $levelFilter === 'all' ? 'inv-btn-primary' : 'inv-btn-secondary' ?>" href="<?= inv_e(inv_url('learn', 'index')) ?>">همه</a>
                <?php foreach ($levels as $key => $label): ?>
                    <a class="inv-btn <?= $levelFilter === $key ? 'inv-btn-primary' : 'inv-btn-secondary' ?>" href="<?= inv_e(inv_url('learn', 'index', ['level' => $key])) ?>"><?= inv_e($label) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="inv-lessons">
            <?php foreach ($lessons as $lesson): ?>
                <a class="inv-lesson-card" href="<?= inv_e(inv_url('learn', 'show', ['lesson' => $lesson['id']])) ?>">
                    <span class="inv-lesson-icon" aria-hidden="true"><i class="<?= inv_e($lesson['icon']) ?>"></i></span>
                    <h3><?= inv_e($lesson['title']) ?></h3>
                    <p><?= inv_e($lesson['summary']) ?></p>
                    <span class="inv-lesson-meta">
                        <span class="inv-tag"><?= inv_e($levels[$lesson['level']] ?? $lesson['level']) ?></span>
                        <small><i class="fa-regular fa-clock"></i> <?= (int) $lesson['minutes'] ?> دقیقه</small>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!$lessons): ?>
            <div class="inv-empty"><i class="fa-solid fa-filter-circle-xmark"></i><strong>درسی در این سطح نیست</strong><span>سطح دیگری را انتخاب کنید.</span></div>
        <?php endif; ?>
    </section>

    <div class="inv-grid-2">
        <a class="inv-card inv-link-card" href="<?= inv_e(inv_url('learn', 'tools')) ?>">
            <div class="inv-card-head"><div><span class="inv-eyebrow">تمرین عملی</span><h2><i class="fa-solid fa-calculator"></i> ماشین‌حساب‌های تعاملی</h2></div></div>
            <p>EOQ، موجودی اطمینان و نقطهٔ سفارش، طبقه‌بندی ABC، سیاست سفارش یک قلم و شاخص‌های عملکرد را با اعداد خودتان امتحان کنید.</p>
        </a>
        <a class="inv-card inv-link-card" href="<?= inv_e(inv_url('learn', 'glossary')) ?>">
            <div class="inv-card-head"><div><span class="inv-eyebrow">مرجع سریع</span><h2><i class="fa-solid fa-book"></i> واژه‌نامهٔ تخصصی</h2></div></div>
            <p><?= (int) $glossaryCount ?> اصطلاح کلیدی با تعریف کوتاه و معادل انگلیسی؛ مناسب مرور پیش از آزمون یا جلسهٔ تصمیم.</p>
        </a>
    </div>

    <div class="inv-note">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <strong>روش استفاده پیشنهادی</strong>
            <ul>
                <li>هر درس را بخوانید، مثال را خودتان با ماشین‌حساب بازتولید کنید و بعد تمرین را حل کنید.</li>
                <li>پس از یادگیری، همان مفهوم را روی یک قلم واقعی در بخش «اقلام موجودی» و «تحلیل و سفارش» پیاده کنید.</li>
                <li>اعداد مثال‌ها با روابط همین سامانه هم‌خوان است؛ اگر عددی متفاوت دیدید، فرض‌ها (تقاضا، زمان تأمین، سطح خدمت و هزینه‌ها) را بازبینی کنید.</li>
            </ul>
        </div>
    </div>
</section>
