<main class="fl-page" dir="rtl">
    <header class="fl-page-head">
        <div><span class="fl-eyebrow"><a href="<?= fl_e(fl_url('learn', 'index')) ?>">مرکز آموزش</a> · <?= fl_e($levelLabel) ?> · <?= (int) $lesson['minutes'] ?> دقیقه</span><h1><i class="<?= fl_e($lesson['icon']) ?>"></i> <?= fl_e($lesson['title']) ?></h1><p><?= fl_e($lesson['summary']) ?></p></div>
        <a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('learn', 'index')) ?>">فهرست درس‌ها</a>
    </header>

    <div class="fl-lesson-layout">
        <aside class="fl-card fl-toc"><strong>در این درس</strong><ol>
            <?php foreach ($lesson['sections'] as $i => $section): ?><li><a href="#section-<?= $i + 1 ?>"><?= fl_e($section['h']) ?></a></li><?php endforeach; ?>
            <li><a href="#formulas">فرمول‌ها</a></li><li><a href="#example">مثال حل‌شده</a></li><li><a href="#practice">تمرین</a></li>
        </ol></aside>

        <div class="fl-lesson-content">
            <?php foreach ($lesson['sections'] as $i => $section): ?>
                <section class="fl-card" id="section-<?= $i + 1 ?>"><h2><?= fl_e($section['h']) ?></h2>
                    <?php foreach ($section['p'] ?? [] as $paragraph): ?><p><?= fl_e($paragraph) ?></p><?php endforeach; ?>
                    <?php if (!empty($section['list'])): ?><ul><?php foreach ($section['list'] as $item): ?><li><?= fl_e($item) ?></li><?php endforeach; ?></ul><?php endif; ?>
                </section>
            <?php endforeach; ?>

            <section class="fl-card" id="formulas"><span class="fl-eyebrow">مرجع فرمول</span><h2>فرمول‌ها و تعریف نمادها</h2><div class="fl-formulas">
                <?php foreach ($lesson['formulas'] as $formula): ?><article><strong><?= fl_e($formula['name']) ?></strong><code dir="ltr"><?= fl_e($formula['expr']) ?></code><small><?= fl_e($formula['note']) ?></small></article><?php endforeach; ?>
            </div></section>

            <section class="fl-card" id="example"><span class="fl-eyebrow">گام‌به‌گام</span><h2><?= fl_e($lesson['example']['title']) ?></h2>
                <div class="fl-given"><strong>داده‌ها و فرض‌ها</strong><ul><?php foreach ($lesson['example']['given'] as $given): ?><li><?= fl_e($given) ?></li><?php endforeach; ?></ul></div>
                <div class="fl-table-wrap"><table class="fl-table"><thead><tr><th>گام</th><th>روش محاسبه یا تصمیم</th><th>نتیجه</th></tr></thead><tbody><?php foreach ($lesson['example']['steps'] as $i => $step): ?><tr><td><?= $i + 1 ?>. <?= fl_e($step['label']) ?></td><td dir="auto"><?= fl_e($step['math']) ?></td><td><strong><?= fl_e($step['result']) ?></strong></td></tr><?php endforeach; ?></tbody></table></div>
                <p class="fl-answer"><i class="fa-solid fa-circle-check"></i> <?= fl_e($lesson['example']['answer']) ?></p>
            </section>

            <section class="fl-card"><span class="fl-eyebrow">بازبینی کیفیت</span><h2>خطاهای رایج</h2><ul class="fl-pitfalls"><?php foreach ($lesson['pitfalls'] as $pitfall): ?><li><?= fl_e($pitfall) ?></li><?php endforeach; ?></ul></section>
            <section class="fl-card" id="practice"><span class="fl-eyebrow">خودآزمایی</span><h2>تمرین</h2><p><?= fl_e($lesson['practice']['q']) ?></p><details class="fl-quiz"><summary>نمایش پاسخ پیشنهادی</summary><p><?= fl_e($lesson['practice']['a']) ?></p></details></section>

            <nav class="fl-lesson-nav" aria-label="جابجایی بین درس‌ها">
                <?php if ($previous): ?><a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('learn', 'show', ['lesson' => $previous['id']])) ?>"><i class="fa-solid fa-arrow-right"></i> <?= fl_e($previous['title']) ?></a><?php else: ?><span></span><?php endif; ?>
                <?php if ($next): ?><a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('learn', 'show', ['lesson' => $next['id']])) ?>"><?= fl_e($next['title']) ?> <i class="fa-solid fa-arrow-left"></i></a><?php endif; ?>
            </nav>
        </div>
    </div>
</main>
