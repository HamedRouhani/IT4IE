<article class="ee-page" dir="rtl">
    <header class="ee-page-head">
        <div>
            <span class="ee-eyebrow"><a href="<?= ee_e(ee_url('learn', 'index')) ?>" style="color:inherit;text-decoration:none;">مرکز آموزش</a> · <?= ee_e($levelLabel) ?></span>
            <h1><i class="<?= ee_e($lesson['icon']) ?>"></i> <?= ee_e($lesson['title']) ?></h1>
            <p><?= ee_e($lesson['summary']) ?></p>
        </div>
        <div class="ee-head-actions">
            <span class="ee-tag"><i class="fa-regular fa-clock"></i> <?= (int) $lesson['minutes'] ?> دقیقه</span>
            <a class="ee-button" href="<?= ee_e(ee_url('learn', 'tools')) ?>"><i class="fa-solid fa-calculator"></i> ماشین‌حساب</a>
            <a class="ee-button ee-button-ghost" href="<?= ee_e(ee_url('learn', 'index')) ?>">همهٔ درس‌ها</a>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="ee-alert <?= ee_e($flash['type']) ?>" role="status"><?= ee_e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="ee-lesson-layout">
        <aside class="ee-toc ee-no-print">
            <strong>فهرست این درس</strong>
            <ol>
                <?php foreach ($lesson['sections'] as $index => $section): ?>
                    <li><a href="#sec-<?= $index + 1 ?>"><?= ee_e($section['h']) ?></a></li>
                <?php endforeach; ?>
                <li><a href="#formulas">فرمول‌ها</a></li>
                <li><a href="#example">مثال حل‌شده</a></li>
                <li><a href="#pitfalls">خطاهای رایج</a></li>
                <li><a href="#practice">تمرین</a></li>
            </ol>
        </aside>

        <div class="ee-lesson-body">
            <?php foreach ($lesson['sections'] as $index => $section): ?>
                <section class="ee-card" id="sec-<?= $index + 1 ?>">
                    <h2 class="ee-lesson-h2"><?= ee_e($section['h']) ?></h2>
                    <?php foreach ($section['p'] ?? [] as $paragraph): ?>
                        <p class="ee-lesson-p"><?= ee_e($paragraph) ?></p>
                    <?php endforeach; ?>
                    <?php if (!empty($section['list'])): ?>
                        <ul class="ee-lesson-list">
                            <?php foreach ($section['list'] as $item): ?><li><?= ee_e($item) ?></li><?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>

            <section class="ee-card" id="formulas">
                <div class="ee-card-head"><div><span class="ee-eyebrow">مرجع</span><h2 class="ee-lesson-h2">فرمول‌های کلیدی</h2></div></div>
                <div class="ee-formulas">
                    <?php foreach ($lesson['formulas'] as $formula): ?>
                        <div class="ee-formula">
                            <strong><?= ee_e($formula['name']) ?></strong>
                            <code dir="ltr"><?= ee_e($formula['expr']) ?></code>
                            <?php if (!empty($formula['note'])): ?><small><?= ee_e($formula['note']) ?></small><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="ee-card" id="example">
                <div class="ee-card-head"><div><span class="ee-eyebrow">گام‌به‌گام</span><h2 class="ee-lesson-h2"><?= ee_e($lesson['example']['title']) ?></h2></div></div>

                <div class="ee-example-given">
                    <strong>داده‌ها</strong>
                    <ul><?php foreach ($lesson['example']['given'] as $given): ?><li><?= ee_e($given) ?></li><?php endforeach; ?></ul>
                </div>

                <div class="ee-table-wrap">
                    <table class="ee-table">
                        <thead><tr><th>گام</th><th>محاسبه</th><th>نتیجه</th></tr></thead>
                        <tbody>
                            <?php foreach ($lesson['example']['steps'] as $stepIndex => $step): ?>
                                <tr>
                                    <td><?= $stepIndex + 1 ?>. <?= ee_e($step['label']) ?></td>
                                    <td class="ee-num"><?= ee_e($step['math']) ?></td>
                                    <td class="ee-num"><strong><?= ee_e($step['result']) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="ee-example-answer"><i class="fa-solid fa-lightbulb"></i><span><?= ee_e($lesson['example']['answer']) ?></span></div>
            </section>

            <section class="ee-card" id="pitfalls">
                <div class="ee-card-head"><div><span class="ee-eyebrow">هشدار</span><h2 class="ee-lesson-h2">خطاهای رایج</h2></div></div>
                <ul class="ee-lesson-list ee-lesson-list--warn">
                    <?php foreach ($lesson['pitfalls'] as $pitfall): ?><li><?= ee_e($pitfall) ?></li><?php endforeach; ?>
                </ul>
            </section>

            <section class="ee-card" id="practice">
                <div class="ee-card-head"><div><span class="ee-eyebrow">خودآزمایی</span><h2 class="ee-lesson-h2">تمرین</h2></div></div>
                <p class="ee-lesson-p"><?= ee_e($lesson['practice']['q']) ?></p>
                <details class="ee-quiz">
                    <summary>نمایش پاسخ</summary>
                    <p><?= ee_e($lesson['practice']['a']) ?></p>
                </details>
            </section>

            <nav class="ee-lesson-nav ee-no-print">
                <?php if ($previous): ?>
                    <a class="ee-button" href="<?= ee_e(ee_url('learn', 'show', ['lesson' => $previous['id']])) ?>"><i class="fa-solid fa-arrow-right"></i> <?= ee_e($previous['title']) ?></a>
                <?php else: ?><span></span><?php endif; ?>
                <?php if ($next): ?>
                    <a class="ee-button" href="<?= ee_e(ee_url('learn', 'show', ['lesson' => $next['id']])) ?>"><?= ee_e($next['title']) ?> <i class="fa-solid fa-arrow-left"></i></a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</article>
