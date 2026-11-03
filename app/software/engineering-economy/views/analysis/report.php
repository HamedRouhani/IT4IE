<section class="ee-page" dir="rtl">
    <header class="ee-page-head">
        <div>
            <span class="ee-eyebrow">گزارش اقتصادی · <?= ee_e($analysis['project']['project_no'] ?: '—') ?></span>
            <h1><i class="fa-solid fa-file-signature"></i> <?= ee_e($analysis['project']['title']) ?></h1>
            <p><?= ee_e(ee_kind_label((string) $analysis['project']['study_kind'])) ?> · ارز <?= ee_e($analysis['project']['currency']) ?> · دورهٔ <?= (int) $analysis['studyPeriod'] ?> سال · تاریخ گزارش <?= ee_e(ee_date(date('Y-m-d'), 'Y/m/d')) ?></p>
        </div>
        <div class="ee-head-actions ee-no-print">
            <button class="ee-button ee-button-primary" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> چاپ</button>
            <a class="ee-button" href="<?= ee_e(ee_url('analysis', 'index', ['id' => (int) $analysis['project']['id']])) ?>">بازگشت به تحلیل</a>
        </div>
    </header>

    <section class="ee-card">
        <div class="ee-card-head"><div><span class="ee-eyebrow">فرض‌های کلان</span><h2>مبنای محاسبه</h2></div></div>
        <div class="ee-table-wrap">
            <table class="ee-table">
                <tbody>
                    <tr><th style="width:32%;">نرخ بازدهٔ حداقلی (MARR)</th><td class="ee-num"><?= ee_num($analysis['project']['marr_pct'], 3) ?>٪</td></tr>
                    <tr><th>نرخ تورم سالانه</th><td class="ee-num"><?= ee_num($analysis['project']['inflation_pct'], 3) ?>٪</td></tr>
                    <tr><th>نرخ مالیات</th><td class="ee-num"><?= ee_num($analysis['project']['tax_rate_pct'], 3) ?>٪</td></tr>
                    <tr><th>روش استهلاک</th><td><?= ee_e(ee_method_label((string) $analysis['project']['depreciation_method'])) ?></td></tr>
                    <?php if ($analysis['project']['base_year']): ?><tr><th>سال پایه</th><td class="ee-num"><?= (int) $analysis['project']['base_year'] ?></td></tr><?php endif; ?>
                    <?php if ($analysis['project']['description']): ?><tr><th>دامنهٔ مطالعه</th><td><?= nl2br(ee_e($analysis['project']['description'])) ?></td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php if ($analysis['recommendation']): ?>
        <div class="ee-alert"><?= ee_e($analysis['recommendation']) ?></div>
    <?php endif; ?>

    <section class="ee-card">
        <div class="ee-card-head"><div><span class="ee-eyebrow">نتیجه</span><h2>معیارهای اقتصادی گزینه‌ها</h2></div></div>
        <?php if ($analysis['rows']): ?>
            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead><tr><th>گزینه</th><th>NPV</th><th>AW</th><th>IRR</th><th>ERR</th><th>MIRR</th><th>بازگشت (سال)</th><th>PI</th><th>B/C</th></tr></thead>
                    <tbody>
                        <?php foreach ($analysis['rows'] as $row): ?>
                            <tr>
                                <td><strong><?= ee_e($row['alternative']['name']) ?></strong><small>عمر <?= (int) $row['alternative']['life_years'] ?> سال</small></td>
                                <td class="ee-num"><?= ee_money($row['npv'], $analysis['project']['currency']) ?></td>
                                <td class="ee-num"><?= ee_money($row['aw'], $analysis['project']['currency']) ?></td>
                                <td class="ee-num"><?= ee_pct($row['irr']) ?></td>
                                <td class="ee-num"><?= ee_pct($row['err']) ?></td>
                                <td class="ee-num"><?= ee_pct($row['mirr']) ?></td>
                                <td class="ee-num"><?= $row['payback'] === null ? '—' : ee_num($row['payback'], 2) ?></td>
                                <td class="ee-num"><?= ee_ratio($row['pi']) ?></td>
                                <td class="ee-num"><?= ee_ratio($row['bc']['ratio'] ?? null) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="ee-empty"><strong>گزینه‌ای ثبت نشده است.</strong></div>
        <?php endif; ?>
    </section>

    <?php $reportAfterTax = array_filter($analysis['rows'], static fn(array $row): bool => $row['afterTax'] !== null); ?>
    <?php if ($reportAfterTax): ?>
        <section class="ee-card">
            <div class="ee-card-head"><div><span class="ee-eyebrow">مالیات و استهلاک</span><h2>تحلیل پس از مالیات</h2><p>مبنای استهلاک: سرمایهٔ اولیهٔ هر گزینه · ارزش اسقاط صفر · نرخ مالیات <?= ee_num($analysis['project']['tax_rate_pct'], 1) ?>٪</p></div></div>
            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead><tr><th>گزینه</th><th>NPV پیش از مالیات</th><th>NPV پس از مالیات</th><th>IRR پس از مالیات</th><th>میانگین استهلاک سالانه</th></tr></thead>
                    <tbody>
                        <?php foreach ($reportAfterTax as $row): ?>
                            <?php
                            $scheduleTotal = 0.0;
                            foreach ($row['afterTax']['schedule'] as $depRow) {
                                $scheduleTotal += (float) $depRow['depreciation'];
                            }
                            $scheduleYears = count($row['afterTax']['schedule']);
                            ?>
                            <tr>
                                <td><?= ee_e($row['alternative']['name']) ?></td>
                                <td class="ee-num"><?= ee_money($row['npv'], $analysis['project']['currency']) ?></td>
                                <td class="ee-num"><?= ee_money($row['afterTax']['npv'], $analysis['project']['currency']) ?></td>
                                <td class="ee-num"><?= ee_pct($row['afterTax']['irr']) ?></td>
                                <td class="ee-num"><?= $scheduleYears > 0 ? ee_money($scheduleTotal / $scheduleYears, $analysis['project']['currency']) : '—' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($analysis['incremental']): ?>
        <section class="ee-card">
            <div class="ee-card-head"><div><span class="ee-eyebrow">مقایسه</span><h2>تحلیل افزایشی</h2></div></div>
            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead><tr><th>کم‌سرمایه‌تر</th><th>پرسرمایه‌تر</th><th>ΔNPV</th><th>ΔIRR</th><th>تصمیم</th></tr></thead>
                    <tbody>
                        <?php foreach ($analysis['incremental'] as $pair): ?>
                            <tr>
                                <td><?= ee_e($pair['lower']['name']) ?></td>
                                <td><?= ee_e($pair['higher']['name']) ?></td>
                                <td class="ee-num"><?= ee_money($pair['deltaNpv'], $analysis['project']['currency']) ?></td>
                                <td class="ee-num"><?= ee_pct($pair['deltaIrr']) ?></td>
                                <td><?= ee_e($pair['decision'] ?? '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <?php foreach ($analysis['rows'] as $row): ?>
        <?php if (!$row['rawRows']) continue; ?>
        <section class="ee-card">
            <div class="ee-card-head"><div><span class="ee-eyebrow">دادهٔ خام</span><h2>جریان نقدی «<?= ee_e($row['alternative']['name']) ?>»</h2></div></div>
            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead><tr><th>سال</th><th>ورودی</th><th>خروجی</th><th>خالص</th><th>توضیح</th></tr></thead>
                    <tbody>
                        <?php foreach ($row['rawRows'] as $raw): ?>
                            <tr>
                                <td class="ee-num"><?= (int) $raw['year_no'] ?></td>
                                <td class="ee-num"><?= ee_money($raw['inflow'], $analysis['project']['currency']) ?></td>
                                <td class="ee-num"><?= ee_money($raw['outflow'], $analysis['project']['currency']) ?></td>
                                <td class="ee-num"><?= ee_money((float) $raw['inflow'] - (float) $raw['outflow'], $analysis['project']['currency']) ?></td>
                                <td><?= ee_e($raw['label'] ?: '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endforeach; ?>

    <?php if (!empty($analysis['project']['assumptions'])): ?>
        <section class="ee-card">
            <div class="ee-card-head"><div><span class="ee-eyebrow">ردیابی</span><h2>فرض‌های مستند</h2></div></div>
            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead><tr><th>فرض</th><th>مقدار</th><th>واحد</th><th>منبع</th></tr></thead>
                    <tbody>
                        <?php foreach ($analysis['project']['assumptions'] as $assumption): ?>
                            <tr>
                                <td><?= ee_e($assumption['title']) ?></td>
                                <td class="ee-num"><?= ee_e($assumption['value'] ?: '—') ?></td>
                                <td><?= ee_e($assumption['unit'] ?: '—') ?></td>
                                <td><?= ee_e($assumption['source'] ?: '—') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <section class="ee-card">
        <div class="ee-card-head"><div><span class="ee-eyebrow">محدودیت‌ها</span><h2>تفسیر و قیدهای گزارش</h2></div></div>
        <ul style="margin:0;padding-inline-start:20px;line-height:2;">
            <li>IRR فقط با جریان نقدی متعارف قابل اتکاست؛ در جریان با بیش از یک تغییر علامت، ERR/MIRR ملاک است.</li>
            <li>نتایج به فرض‌های ثبت‌شده (MARR، تورم، مالیات و اعداد جریان نقدی) وابسته‌اند؛ تغییر هرکدام نتیجه را عوض می‌کند.</li>
            <li>تحلیل جایگزینی در این گزارش بدون ارزش اسقاط محاسبه شده است؛ اگر اسقاط قابل توجه است، آن را به‌صورت ورودی سال آخر ثبت کنید.</li>
            <li>این گزارش جایگزین بررسی ایمنی، ارگونومی، اثرات زیست‌محیطی و الزامات قراردادی نیست.</li>
        </ul>
    </section>
</section>
