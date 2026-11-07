<article class="inv-page" dir="rtl">
    <header class="inv-page-head">
        <div>
            <span class="inv-eyebrow"><a href="<?= inv_e(inv_url('learn', 'index')) ?>" class="inv-breadcrumb">مرکز آموزش</a> · <?= inv_e($levelLabel) ?></span>
            <h1><i class="<?= inv_e($lesson['icon']) ?>"></i> <?= inv_e($lesson['title']) ?></h1>
            <p><?= inv_e($lesson['summary']) ?></p>
        </div>
        <div class="inv-head-actions">
            <span class="inv-tag"><i class="fa-regular fa-clock"></i> <?= (int) $lesson['minutes'] ?> دقیقه</span>
            <a class="inv-btn inv-btn-secondary" href="<?= inv_e(inv_url('learn', 'tools')) ?>"><i class="fa-solid fa-calculator"></i> ماشین‌حساب</a>
            <a class="inv-btn inv-btn-ghost" href="<?= inv_e(inv_url('learn', 'index')) ?>">همهٔ درس‌ها</a>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="inv-alert <?= inv_e($flash['type']) ?>" role="status"><?= inv_e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="inv-lesson-layout">
        <aside class="inv-toc inv-no-print">
            <strong>فهرست این درس</strong>
            <ol>
                <?php foreach ($lesson['sections'] as $index => $section): ?>
                    <li><a href="#sec-<?= $index + 1 ?>"><?= inv_e($section['h']) ?></a></li>
                <?php endforeach; ?>
                <li><a href="#formulas">فرمول‌ها</a></li>
                <li><a href="#example">مثال حل‌شده</a></li>
                <li><a href="#pitfalls">خطاهای رایج</a></li>
                <li><a href="#practice">تمرین</a></li>
            </ol>
        </aside>

        <div class="inv-lesson-body">
            <?php foreach ($lesson['sections'] as $index => $section): ?>
                <section class="inv-card" id="sec-<?= $index + 1 ?>">
                    <h2 class="inv-lesson-h2"><?= inv_e($section['h']) ?></h2>
                    <?php foreach ($section['p'] ?? [] as $paragraph): ?>
                        <p class="inv-lesson-p"><?= inv_e($paragraph) ?></p>
                    <?php endforeach; ?>
                    <?php if (!empty($section['list'])): ?>
                        <ul class="inv-lesson-list">
                            <?php foreach ($section['list'] as $item): ?><li><?= inv_e($item) ?></li><?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>

            <section class="inv-card" id="formulas">
                <div class="inv-card-head"><div><span class="inv-eyebrow">مرجع</span><h2 class="inv-lesson-h2">فرمول‌های کلیدی</h2></div></div>
                <div class="inv-formulas">
                    <?php foreach ($lesson['formulas'] as $formula): ?>
                        <div class="inv-formula">
                            <strong><?= inv_e($formula['name']) ?></strong>
                            <code dir="ltr"><?= inv_e($formula['expr']) ?></code>
                            <?php if (!empty($formula['note'])): ?><small><?= inv_e($formula['note']) ?></small><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="inv-card" id="example">
                <div class="inv-card-head"><div><span class="inv-eyebrow">گام‌به‌گام</span><h2 class="inv-lesson-h2"><?= inv_e($lesson['example']['title']) ?></h2></div></div>

                <div class="inv-example-given">
                    <strong>داده‌ها</strong>
                    <ul><?php foreach ($lesson['example']['given'] as $given): ?><li><?= inv_e($given) ?></li><?php endforeach; ?></ul>
                </div>

                <div class="inv-table-wrap">
                    <table class="inv-table inv-lesson-table">
                        <thead><tr><th>گام</th><th>محاسبه</th><th>نتیجه</th></tr></thead>
                        <tbody>
                            <?php foreach ($lesson['example']['steps'] as $stepIndex => $step): ?>
                                <tr>
                                    <td><?= $stepIndex + 1 ?>. <?= inv_e($step['label']) ?></td>
                                    <td class="inv-num"><?= inv_e($step['math']) ?></td>
                                    <td class="inv-num"><strong><?= inv_e($step['result']) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="inv-example-answer"><i class="fa-solid fa-lightbulb"></i><span><?= inv_e($lesson['example']['answer']) ?></span></div>
            </section>

            <section class="inv-card" id="pitfalls">
                <div class="inv-card-head"><div><span class="inv-eyebrow">هشدار</span><h2 class="inv-lesson-h2">خطاهای رایج</h2></div></div>
                <ul class="inv-lesson-list inv-lesson-list--warn">
                    <?php foreach ($lesson['pitfalls'] as $pitfall): ?><li><?= inv_e($pitfall) ?></li><?php endforeach; ?>
                </ul>
            </section>

            <section class="inv-card" id="practice">
                <div class="inv-card-head"><div><span class="inv-eyebrow">خودآزمایی</span><h2 class="inv-lesson-h2">تمرین</h2></div></div>
                <p class="inv-lesson-p"><?= inv_e($lesson['practice']['q']) ?></p>
                <details class="inv-quiz">
                    <summary>نمایش پاسخ</summary>
                    <p><?= inv_e($lesson['practice']['a']) ?></p>
                </details>
            </section>

            <nav class="inv-lesson-nav inv-no-print">
                <?php if ($previous): ?>
                    <a class="inv-btn inv-btn-secondary" href="<?= inv_e(inv_url('learn', 'show', ['lesson' => $previous['id']])) ?>"><i class="fa-solid fa-arrow-right"></i> <?= inv_e($previous['title']) ?></a>
                <?php else: ?><span></span><?php endif; ?>
                <?php if ($next): ?>
                    <a class="inv-btn inv-btn-secondary" href="<?= inv_e(inv_url('learn', 'show', ['lesson' => $next['id']])) ?>"><?= inv_e($next['title']) ?> <i class="fa-solid fa-arrow-left"></i></a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</article>
