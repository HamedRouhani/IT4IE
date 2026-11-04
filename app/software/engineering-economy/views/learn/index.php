<section class="ee-page" dir="rtl">
    <header class="ee-page-head">
        <div>
            <span class="ee-eyebrow">مرکز آموزش</span>
            <h1><i class="fa-solid fa-graduation-cap"></i> اقتصاد مهندسی را گام‌به‌گام بیاموزید</h1>
            <p>هشت درس کوتاه با فرمول، مثال حل‌شده، خطاهای رایج و تمرین؛ به‌همراه واژه‌نامه و ماشین‌حساب تعاملی.</p>
        </div>
        <div class="ee-head-actions">
            <a class="ee-button ee-button-primary" href="<?= ee_e(ee_url('learn', 'tools')) ?>"><i class="fa-solid fa-calculator"></i> ماشین‌حساب‌ها</a>
            <a class="ee-button" href="<?= ee_e(ee_url('learn', 'glossary')) ?>"><i class="fa-solid fa-book"></i> واژه‌نامه</a>
            <a class="ee-button ee-button-ghost" href="<?= ee_e(ee_url('project', 'index')) ?>">پروژه‌های من</a>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="ee-alert <?= ee_e($flash['type']) ?>" role="status"><?= ee_e($flash['message']) ?></div>
    <?php endif; ?>

    <section class="ee-kpis">
        <article class="ee-kpi is-accent"><span>درس‌ها</span><strong><?= (int) $totalLessons ?></strong><small>از پایه تا پیشرفته</small></article>
        <article class="ee-kpi"><span>واژه‌های کلیدی</span><strong><?= (int) $glossaryCount ?></strong><small>با معادل فارسی</small></article>
        <article class="ee-kpi"><span>ماشین‌حساب</span><strong>۵</strong><small>فاکتور، NPV، استهلاک، تورم، حساسیت</small></article>
        <article class="ee-kpi"><span>مثال حل‌شده</span><strong><?= (int) $totalLessons ?></strong><small>با اعداد قابل بازتولید</small></article>
    </section>

    <section class="ee-card">
        <div class="ee-card-head">
            <div><span class="ee-eyebrow">مسیر یادگیری</span><h2>فهرست درس‌ها</h2></div>
            <div class="ee-head-actions">
                <a class="ee-button <?= $levelFilter === 'all' ? 'ee-button-primary' : '' ?>" href="<?= ee_e(ee_url('learn', 'index')) ?>">همه</a>
                <?php foreach ($levels as $key => $label): ?>
                    <a class="ee-button <?= $levelFilter === $key ? 'ee-button-primary' : '' ?>" href="<?= ee_e(ee_url('learn', 'index', ['level' => $key])) ?>"><?= ee_e($label) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="ee-lessons">
            <?php foreach ($lessons as $lesson): ?>
                <a class="ee-lesson-card" href="<?= ee_e(ee_url('learn', 'show', ['lesson' => $lesson['id']])) ?>">
                    <span class="ee-lesson-icon" aria-hidden="true"><i class="<?= ee_e($lesson['icon']) ?>"></i></span>
                    <h3><?= ee_e($lesson['title']) ?></h3>
                    <p><?= ee_e($lesson['summary']) ?></p>
                    <span class="ee-lesson-meta">
                        <span class="ee-tag"><?= ee_e($levels[$lesson['level']] ?? $lesson['level']) ?></span>
                        <small><i class="fa-regular fa-clock"></i> <?= (int) $lesson['minutes'] ?> دقیقه</small>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if (!$lessons): ?>
            <div class="ee-empty"><i class="fa-solid fa-filter-circle-xmark"></i><strong>درسی در این سطح نیست</strong><span>سطح دیگری را انتخاب کنید.</span></div>
        <?php endif; ?>
    </section>

    <div class="ee-grid-2">
        <a class="ee-card ee-link-card" href="<?= ee_e(ee_url('learn', 'tools')) ?>">
            <div class="ee-card-head"><div><span class="ee-eyebrow">تمرین عملی</span><h2><i class="fa-solid fa-calculator"></i> ماشین‌حساب‌های تعاملی</h2></div></div>
            <p style="margin:0;color:#66756f;line-height:1.9;">فاکتورهای ارزش زمانی، NPV/IRR، جدول استهلاک، تبدیل نرخ با تورم و ارزش تغییر را با اعداد خودتان امتحان کنید.</p>
        </a>
        <a class="ee-card ee-link-card" href="<?= ee_e(ee_url('learn', 'glossary')) ?>">
            <div class="ee-card-head"><div><span class="ee-eyebrow">مرجع سریع</span><h2><i class="fa-solid fa-book"></i> واژه‌نامهٔ تخصصی</h2></div></div>
            <p style="margin:0;color:#66756f;line-height:1.9;"><?= (int) $glossaryCount ?> اصطلاح کلیدی با تعریف کوتاه و معادل انگلیسی؛ مناسب مرور پیش از آزمون یا جلسهٔ تصمیم.</p>
        </a>
    </div>

    <div class="ee-note">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <strong>روش استفاده پیشنهادی</strong>
            <ul>
                <li>هر درس را بخوانید، مثال را خودتان با ماشین‌حساب بازتولید کنید و بعد تمرین را حل کنید.</li>
                <li>پس از یادگیری، همان مفهوم را روی یک پروژهٔ واقعی در بخش «پروژه‌ها» پیاده کنید.</li>
                <li>اعداد مثال‌ها با محاسبات همین سامانه هم‌خوان است؛ اگر عددی متفاوت دیدید، فرض‌ها (نرخ، دوره، علامت جریان) را بازبینی کنید.</li>
            </ul>
        </div>
    </div>
</section>
