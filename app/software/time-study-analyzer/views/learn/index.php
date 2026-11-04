<section class="ts-page" dir="rtl">
    <header class="ts-page-head">
        <div>
            <span class="ts-eyebrow">مرکز آموزش</span>
            <h1><i class="fa-solid fa-graduation-cap"></i> مطالعهٔ کار را گام‌به‌گام بیاموزید</h1>
            <p>هشت درس کوتاه بر پایهٔ روش‌های استاندارد ILO؛ با فرمول، مثال حل‌شده، خطاهای رایج و تمرین.</p>
        </div>
        <div class="ts-head-actions">
            <a class="ts-button ts-button-primary" href="<?= ts_e(ts_url('learn', 'tools')) ?>"><i class="fa-solid fa-calculator"></i> ماشین‌حساب‌ها</a>
            <a class="ts-button ts-button-outline" href="<?= ts_e(ts_url('learn', 'glossary')) ?>"><i class="fa-solid fa-book"></i> واژه‌نامه</a>
            <a class="ts-button ts-button-outline" href="<?= ts_e(ts_url('study')) ?>">مطالعات من</a>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="ts-alert <?= ts_e($flash['type']) ?>" role="status"><?= ts_e($flash['message']) ?></div>
    <?php endif; ?>

    <section class="ts-stats ts-result-stats">
        <article><span>درس‌ها</span><strong><?= (int) $totalLessons ?></strong><small>پایه تا پیشرفته</small></article>
        <article><span>واژه‌های کلیدی</span><strong><?= (int) $glossaryCount ?></strong><small>با معادل فارسی</small></article>
        <article><span>ماشین‌حساب</span><strong>۵</strong><small>نمونه، زمان استاندارد، ارزیابی…</small></article>
        <article><span>مثال حل‌شده</span><strong><?= (int) $totalLessons ?></strong><small>با اعداد قابل بازتولید</small></article>
    </section>

    <section class="ts-card">
        <div class="ts-card-head">
            <div><span class="ts-eyebrow">مسیر یادگیری</span><h2>فهرست درس‌ها</h2></div>
            <div class="ts-head-actions">
                <a class="ts-button <?= $levelFilter === 'all' ? 'ts-button-primary' : 'ts-button-outline' ?>" href="<?= ts_e(ts_url('learn', 'index')) ?>">همه</a>
                <?php foreach ($levels as $key => $label): ?>
                    <a class="ts-button <?= $levelFilter === $key ? 'ts-button-primary' : 'ts-button-outline' ?>" href="<?= ts_e(ts_url('learn', 'index', ['level' => $key])) ?>"><?= ts_e($label) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($lessons): ?>
            <div class="ts-lessons">
                <?php foreach ($lessons as $lesson): ?>
                    <a class="ts-lesson-card" href="<?= ts_e(ts_url('learn', 'show', ['lesson' => $lesson['id']])) ?>">
                        <span class="ts-lesson-icon" aria-hidden="true"><i class="<?= ts_e($lesson['icon']) ?>"></i></span>
                        <h3><?= ts_e($lesson['title']) ?></h3>
                        <p><?= ts_e($lesson['summary']) ?></p>
                        <span class="ts-lesson-meta">
                            <span class="ts-status active"><?= ts_e($levels[$lesson['level']] ?? $lesson['level']) ?></span>
                            <small><i class="fa-regular fa-clock"></i> <?= (int) $lesson['minutes'] ?> دقیقه</small>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="ts-empty compact">درسی در این سطح نیست؛ سطح دیگری را انتخاب کنید.</div>
        <?php endif; ?>
    </section>

    <div class="ts-learn-links">
        <a class="ts-card ts-link-card" href="<?= ts_e(ts_url('learn', 'tools')) ?>">
            <span class="ts-eyebrow">تمرین عملی</span>
            <h2><i class="fa-solid fa-calculator"></i> ماشین‌حساب‌های تعاملی</h2>
            <p>تعداد چرخهٔ لازم، زمان استاندارد، ارزیابی وستینگهاوس، بازهٔ اطمینان و نمونه‌برداری کار را با اعداد خودتان امتحان کنید.</p>
        </a>
        <a class="ts-card ts-link-card" href="<?= ts_e(ts_url('learn', 'glossary')) ?>">
            <span class="ts-eyebrow">مرجع سریع</span>
            <h2><i class="fa-solid fa-book"></i> واژه‌نامهٔ مطالعهٔ کار</h2>
            <p><?= (int) $glossaryCount ?> اصطلاح کلیدی با تعریف کوتاه و معادل انگلیسی؛ مناسب مرور پیش از مطالعهٔ میدانی یا آزمون.</p>
        </a>
    </div>

    <div class="ts-note ts-note-compact">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <strong>روش استفاده پیشنهادی</strong>
            <ul>
                <li>هر درس را بخوانید و مثال را با ماشین‌حساب بازتولید کنید.</li>
                <li>سپس همان مفهوم را روی یک مطالعهٔ واقعی در بخش «مطالعات زمان‌سنجی» پیاده کنید.</li>
                <li>اعداد مثال‌ها با محاسبات همین سامانه هم‌خوان است؛ اختلاف یعنی یکی از فرض‌ها (نرخ، نمونه، علامت) متفاوت است.</li>
            </ul>
        </div>
    </div>
</section>
