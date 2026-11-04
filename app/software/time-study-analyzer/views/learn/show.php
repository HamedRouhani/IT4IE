<article class="ts-page" dir="rtl">
    <header class="ts-page-head">
        <div>
            <span class="ts-eyebrow"><a href="<?= ts_e(ts_url('learn', 'index')) ?>" style="color:inherit;text-decoration:none;">مرکز آموزش</a> · <?= ts_e($levelLabel) ?></span>
            <h1><i class="<?= ts_e($lesson['icon']) ?>"></i> <?= ts_e($lesson['title']) ?></h1>
            <p><?= ts_e($lesson['summary']) ?></p>
        </div>
        <div class="ts-head-actions">
            <span class="ts-status active"><i class="fa-regular fa-clock"></i> <?= (int) $lesson['minutes'] ?> دقیقه</span>
            <a class="ts-button ts-button-outline" href="<?= ts_e(ts_url('learn', 'tools')) ?>"><i class="fa-solid fa-calculator"></i> ماشین‌حساب</a>
            <a class="ts-button ts-button-outline" href="<?= ts_e(ts_url('learn', 'index')) ?>">همهٔ درس‌ها</a>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="ts-alert <?= ts_e($flash['type']) ?>" role="status"><?= ts_e($flash['message']) ?></div>
    <?php endif; ?>

    <div class="ts-lesson-layout">
        <aside class="ts-toc ts-no-print">
            <strong>فهرست این درس</strong>
            <ol>
                <?php foreach ($lesson['sections'] as $index => $section): ?>
                    <li><a href="#sec-<?= $index + 1 ?>"><?= ts_e($section['h']) ?></a></li>
                <?php endforeach; ?>
                <li><a href="#formulas">فرمول‌ها</a></li>
                <li><a href="#example">مثال حل‌شده</a></li>
                <li><a href="#pitfalls">خطاهای رایج</a></li>
                <li><a href="#practice">تمرین</a></li>
            </ol>
        </aside>

        <div class="ts-lesson-body">
            <?php foreach ($lesson['sections'] as $index => $section): ?>
                <section class="ts-card" id="sec-<?= $index + 1 ?>">
                    <h2 class="ts-lesson-h2"><?= ts_e($section['h']) ?></h2>
                    <?php foreach ($section['p'] ?? [] as $paragraph): ?>
                        <p class="ts-lesson-p"><?= ts_e($paragraph) ?></p>
                    <?php endforeach; ?>
                    <?php if (!empty($section['list'])): ?>
                        <ul class="ts-lesson-list">
                            <?php foreach ($section['list'] as $item): ?><li><?= ts_e($item) ?></li><?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>

            <section class="ts-card" id="formulas">
                <div class="ts-card-head"><div><span class="ts-eyebrow">مرجع</span><h2 class="ts-lesson-h2">فرمول‌های کلیدی</h2></div></div>
                <div class="ts-formulas">
                    <?php foreach ($lesson['formulas'] as $formula): ?>
                        <div class="ts-formula">
                            <strong><?= ts_e($formula['name']) ?></strong>
                            <code dir="ltr"><?= ts_e($formula['expr']) ?></code>
                            <?php if (!empty($formula['note'])): ?><small><?= ts_e($formula['note']) ?></small><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="ts-card" id="example">
                <div class="ts-card-head"><div><span class="ts-eyebrow">گام‌به‌گام</span><h2 class="ts-lesson-h2"><?= ts_e($lesson['example']['title']) ?></h2></div></div>

                <div class="ts-example-given">
                    <strong>داده‌ها</strong>
                    <ul><?php foreach ($lesson['example']['given'] as $given): ?><li><?= ts_e($given) ?></li><?php endforeach; ?></ul>
                </div>

                <div class="ts-table-wrap">
                    <table class="ts-table">
                        <thead><tr><th>گام</th><th>محاسبه</th><th>نتیجه</th></tr></thead>
                        <tbody>
                            <?php foreach ($lesson['example']['steps'] as $stepIndex => $step): ?>
                                <tr>
                                    <td><?= $stepIndex + 1 ?>. <?= ts_e($step['label']) ?></td>
                                    <td class="ts-num"><?= ts_e($step['math']) ?></td>
                                    <td class="ts-num"><strong><?= ts_e($step['result']) ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="ts-example-answer"><i class="fa-solid fa-lightbulb"></i><span><?= ts_e($lesson['example']['answer']) ?></span></div>
            </section>

            <section class="ts-card" id="pitfalls">
                <div class="ts-card-head"><div><span class="ts-eyebrow">هشدار</span><h2 class="ts-lesson-h2">خطاهای رایج</h2></div></div>
                <ul class="ts-lesson-list ts-lesson-list--warn">
                    <?php foreach ($lesson['pitfalls'] as $pitfall): ?><li><?= ts_e($pitfall) ?></li><?php endforeach; ?>
                </ul>
            </section>

            <section class="ts-card" id="practice">
                <div class="ts-card-head"><div><span class="ts-eyebrow">خودآزمایی</span><h2 class="ts-lesson-h2">تمرین</h2></div></div>
                <p class="ts-lesson-p"><?= ts_e($lesson['practice']['q']) ?></p>
                <details class="ts-quiz">
                    <summary>نمایش پاسخ</summary>
                    <p><?= ts_e($lesson['practice']['a']) ?></p>
                </details>
            </section>

            <nav class="ts-lesson-nav ts-no-print">
                <?php if ($previous): ?>
                    <a class="ts-button ts-button-outline" href="<?= ts_e(ts_url('learn', 'show', ['lesson' => $previous['id']])) ?>"><i class="fa-solid fa-arrow-right"></i> <?= ts_e($previous['title']) ?></a>
                <?php else: ?><span></span><?php endif; ?>
                <?php if ($next): ?>
                    <a class="ts-button ts-button-outline" href="<?= ts_e(ts_url('learn', 'show', ['lesson' => $next['id']])) ?>"><?= ts_e($next['title']) ?> <i class="fa-solid fa-arrow-left"></i></a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
</article>
