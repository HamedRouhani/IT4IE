<section class="ee-page" dir="rtl">
    <header class="ee-page-head">
        <div>
            <span class="ee-eyebrow"><?= ee_e($analysis['project']['project_no'] ?: 'پروژه') ?> · <?= ee_e($analysis['project']['currency']) ?></span>
            <h1><i class="fa-solid fa-chart-line"></i> تحلیل اقتصادی</h1>
            <p><?= ee_e($analysis['project']['title']) ?> — MARR <?= ee_num($analysis['project']['marr_pct'], 2) ?>٪ · دورهٔ <?= (int) $analysis['studyPeriod'] ?> سال · تورم <?= ee_num($analysis['project']['inflation_pct'], 2) ?>٪</p>
        </div>
        <div class="ee-head-actions ee-no-print">
            <a class="ee-button" href="<?= ee_e(ee_url('cashflow', 'index', ['id' => (int) $analysis['project']['id']])) ?>">جریان نقدی</a>
            <a class="ee-button" href="<?= ee_e(ee_url('analysis', 'report', ['id' => (int) $analysis['project']['id']])) ?>"><i class="fa-solid fa-print"></i> گزارش</a>
            <a class="ee-button" href="<?= ee_e(ee_url('analysis', 'export', ['id' => (int) $analysis['project']['id'], 'kind' => 'metrics'])) ?>"><i class="fa-solid fa-file-csv"></i> CSV معیارها</a>
            <a class="ee-button" href="<?= ee_e(ee_url('analysis', 'export', ['id' => (int) $analysis['project']['id'], 'kind' => 'flows'])) ?>"><i class="fa-solid fa-file-csv"></i> CSV جریان</a>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="ee-alert <?= ee_e($flash['type']) ?>" role="status"><?= ee_e($flash['message']) ?></div>
    <?php endif; ?>

    <?php if ($analysis['recommendation']): ?>
        <div class="ee-alert <?= count($analysis['ranking']) && $analysis['ranking'][0]['npv'] > 0 ? 'success' : 'warning' ?>">
            <strong>پیشنهاد تحلیل:</strong> <?= ee_e($analysis['recommendation']) ?>
        </div>
    <?php endif; ?>

    <section class="ee-card">
        <div class="ee-card-head">
            <div><span class="ee-eyebrow">معیارهای تصمیم</span><h2>نتایج به تفکیک گزینه</h2><p>NPV و AW ملاک اصلی‌اند؛ IRR فقط برای جریان متعارف و به‌عنوان معیار مکمل.</p></div>
            <span class="ee-tag"><?= count($analysis['rows']) ?> گزینه</span>
        </div>

        <?php if ($analysis['rows']): ?>
            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead>
                        <tr><th>گزینه</th><th>NPV</th><th>AW</th><th>IRR</th><th>ERR</th><th>MIRR</th><th>بازگشت</th><th>بازگشت تنزیل‌شده</th><th>PI</th><th>B/C</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($analysis['rows'] as $row): ?>
                            <tr>
                                <td>
                                    <strong><?= ee_e($row['alternative']['name']) ?></strong>
                                    <small><?= $row['alternative']['is_baseline'] ? 'گزینهٔ پایه' : 'جایگزین' ?> · عمر <?= (int) $row['alternative']['life_years'] ?> سال<?= count($row['irrRoots']) > 1 ? ' · IRR چندریشه‌ای' : '' ?></small>
                                </td>
                                <td class="ee-num"><?= ee_money($row['npv'], $analysis['project']['currency']) ?></td>
                                <td class="ee-num"><?= ee_money($row['aw'], $analysis['project']['currency']) ?></td>
                                <td class="ee-num"><?= ee_pct($row['irr']) ?></td>
                                <td class="ee-num"><?= ee_pct($row['err']) ?></td>
                                <td class="ee-num"><?= ee_pct($row['mirr']) ?></td>
                                <td class="ee-num"><?= $row['payback'] === null ? '—' : ee_num($row['payback'], 2) ?></td>
                                <td class="ee-num"><?= $row['discountedPayback'] === null ? '—' : ee_num($row['discountedPayback'], 2) ?></td>
                                <td class="ee-num"><?= ee_ratio($row['pi']) ?></td>
                                <td class="ee-num"><?= ee_ratio($row['bc']['ratio'] ?? null) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php
            $issues = [];
            foreach ($analysis['rows'] as $row) {
                foreach ($row['issues'] as $issue) {
                    $issues[] = $row['alternative']['name'] . ': ' . $issue;
                }
            }
            ?>
            <?php if ($issues): ?>
                <div class="ee-note" style="margin-top:16px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div><strong>هشدارهای اعتبار داده</strong><ul><?php foreach (array_unique($issues) as $issue): ?><li><?= ee_e($issue) ?></li><?php endforeach; ?></ul></div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="ee-empty"><i class="fa-solid fa-layer-group"></i><strong>گزینه‌ای برای تحلیل وجود ندارد</strong><span>ابتدا در بخش «جریان نقدی» گزینه و مبالغ سال‌به‌سال را ثبت کنید.</span></div>
        <?php endif; ?>
    </section>

    <?php $afterTaxRows = array_filter($analysis['rows'], static fn(array $row): bool => $row['afterTax'] !== null); ?>
    <?php if ($afterTaxRows): ?>
        <section class="ee-card">
            <div class="ee-card-head">
                <div>
                    <span class="ee-eyebrow">مالیات و استهلاک</span>
                    <h2>نتیجه پس از مالیات</h2>
                    <p>استهلاک روی سرمایهٔ اولیهٔ هر گزینه با روش «<?= ee_e(ee_method_label((string) $analysis['project']['depreciation_method'])) ?>» و ارزش اسقاط صفر محاسبه شده است.</p>
                </div>
                <span class="ee-tag">نرخ مالیات <?= ee_num($analysis['project']['tax_rate_pct'], 1) ?>٪</span>
            </div>
            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead><tr><th>گزینه</th><th>NPV پیش از مالیات</th><th>NPV پس از مالیات</th><th>IRR پس از مالیات</th><th>بازگشت پس از مالیات</th><th>اثر مالیات بر NPV</th></tr></thead>
                    <tbody>
                        <?php foreach ($afterTaxRows as $row): ?>
                            <tr>
                                <td><?= ee_e($row['alternative']['name']) ?></td>
                                <td class="ee-num"><?= ee_money($row['npv'], $analysis['project']['currency']) ?></td>
                                <td class="ee-num"><?= ee_money($row['afterTax']['npv'], $analysis['project']['currency']) ?></td>
                                <td class="ee-num"><?= ee_pct($row['afterTax']['irr']) ?></td>
                                <td class="ee-num"><?= $row['afterTax']['payback'] === null ? '—' : ee_num($row['afterTax']['payback'], 2) ?></td>
                                <td class="ee-num"><?= ee_money((float) $row['afterTax']['npv'] - (float) $row['npv'], $analysis['project']['currency']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="ee-note" style="margin-top:14px;">
                <i class="fa-solid fa-circle-info"></i>
                <div>
                    <strong>فرض محاسبه</strong>
                    <ul>
                        <li>مبنای استهلاک، خروجی سال ۰ همان گزینه است و ارزش اسقاط صفر گرفته شده.</li>
                        <li>اگر جریان نقدی واردشده از پیش پس از مالیات است، نرخ مالیات پروژه را صفر کنید تا مالیات دوبار اعمال نشود.</li>
                    </ul>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if (count($analysis['ranking']) > 1): ?>
        <section class="ee-card">
            <div class="ee-card-head"><div><span class="ee-eyebrow">رتبه‌بندی</span><h2>ترتیب بر پایهٔ NPV</h2></div></div>
            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead><tr><th>رتبه</th><th>گزینه</th><th>NPV</th><th>ΔIRR نسبت به رتبهٔ قبل</th><th>تصمیم افزایشی</th></tr></thead>
                    <tbody>
                        <?php foreach ($analysis['ranking'] as $index => $row): ?>
                            <tr>
                                <td class="ee-num"><?= $index + 1 ?></td>
                                <td><strong><?= ee_e($row['alternative']['name']) ?></strong></td>
                                <td class="ee-num"><?= ee_money($row['npv'], $analysis['project']['currency']) ?></td>
                                <td class="ee-num"><?= $row['deltaIrr'] === null ? '—' : ee_pct($row['deltaIrr']) ?></td>
                                <td><?= ee_e($row['deltaDecision']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($analysis['incremental']): ?>
        <section class="ee-card">
            <div class="ee-card-head"><div><span class="ee-eyebrow">تحلیل افزایشی</span><h2>مقایسهٔ گزینه‌ها به ترتیب سرمایهٔ اولیه</h2><p>در محدودیت سرمایه، تصمیم درست بر مبنای جریان تفاضل (Δ) گرفته می‌شود، نه IRR جداگانه.</p></div></div>
            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead><tr><th>گزینهٔ کم‌سرمایه‌تر</th><th>گزینهٔ پرسرمایه‌تر</th><th>ΔNPV</th><th>ΔIRR</th><th>تصمیم</th></tr></thead>
                    <tbody>
                        <?php foreach ($analysis['incremental'] as $pair): ?>
                            <tr>
                                <td><?= ee_e($pair['lower']['name']) ?></td>
                                <td><?= ee_e($pair['higher']['name']) ?></td>
                                <td class="ee-num"><?= ee_money($pair['deltaNpv'], $analysis['project']['currency']) ?></td>
                                <td class="ee-num"><?= ee_pct($pair['deltaIrr']) ?></td>
                                <td><?= ee_e($pair['decision'] ?? 'برای این جفت، جریان تفاضل قابل حل نیست؛ NPV را مبنا بگیرید.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <section class="ee-card">
        <div class="ee-card-head"><div><span class="ee-eyebrow">تحلیل حساسیت</span><h2>اثر نرخ بازدهٔ حداقلی</h2><p>هر ستون NPV را در نرخ‌های مختلف نشان می‌دهد؛ نقطهٔ تغییر تصمیم همان‌جاست که علامت NPV عوض می‌شود.</p></div></div>
        <div class="ee-table-wrap">
            <table class="ee-table">
                <thead><tr><th>MARR</th><?php foreach ($analysis['rows'] as $row): ?><th><?= ee_e($row['alternative']['name']) ?></th><?php endforeach; ?></tr></thead>
                <tbody>
                    <?php foreach ($analysis['sensitivity']['rateMatrix'] as $line): ?>
                        <tr>
                            <td class="ee-num"><?= ee_pct($line['rate']) ?></td>
                            <?php foreach ($line['cells'] as $cell): ?><td class="ee-num"><?= ee_money($cell['npv'], $analysis['project']['currency']) ?></td><?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <?php if ($analysis['sensitivity']['years']): ?>
        <section class="ee-card">
            <div class="ee-card-head"><div><span class="ee-eyebrow">حساسیت سالانه</span><h2>تغییر ±۱۰٪ جریان هر سال و ارزش تغییر</h2><p>«ارزش تغییر» یعنی چند درصد تغییر در جریان آن سال، NPV را صفر می‌کند.</p></div></div>
            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead><tr><th>سال</th><th>گزینه</th><th>NPV با −۱۰٪</th><th>NPV با +۱۰٪</th><th>ارزش تغییر</th></tr></thead>
                    <tbody>
                        <?php foreach ($analysis['sensitivity']['years'] as $yearRow): ?>
                            <?php foreach ($yearRow['cells'] as $cell): ?>
                                <?php if ($cell['down'] === null && $cell['up'] === null) continue; ?>
                                <tr>
                                    <td class="ee-num"><?= (int) $yearRow['year'] ?></td>
                                    <td><?= ee_e($cell['name']) ?></td>
                                    <td class="ee-num"><?= ee_money($cell['down'], $analysis['project']['currency']) ?></td>
                                    <td class="ee-num"><?= ee_money($cell['up'], $analysis['project']['currency']) ?></td>
                                    <td class="ee-num"><?= ee_pct($cell['switching']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($analysis['replacement']): ?>
        <section class="ee-card">
            <div class="ee-card-head"><div><span class="ee-eyebrow">جایگزینی</span><h2>هزینهٔ یکنواخت سالانه و عمر اقتصادی</h2><p>EUAC برای هر افق نگهداری محاسبه شده؛ سالی که کمترین EUAC را دارد، عمر اقتصادی است (بدون ارزش اسقاط).</p></div></div>
            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead><tr><th>گزینه</th><th>سرمایهٔ اولیه</th><th>عمر اعلامی</th><th>عمر اقتصادی</th><th>کمینه EUAC</th></tr></thead>
                    <tbody>
                        <?php foreach ($analysis['replacement'] as $item): ?>
                            <tr>
                                <td><?= ee_e($item['alternative']['name']) ?></td>
                                <td class="ee-num"><?= ee_money($item['firstCost'], $analysis['project']['currency']) ?></td>
                                <td class="ee-num"><?= (int) $item['declaredLife'] ?></td>
                                <td class="ee-num"><?= $item['life'] === null ? '—' : (int) $item['life'] ?></td>
                                <td class="ee-num"><?= ee_money($item['minEuac'], $analysis['project']['currency']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    <?php endif; ?>

    <section class="ee-card ee-no-print">
        <div class="ee-card-head"><div><span class="ee-eyebrow">بازبینی‌پذیری</span><h2>ثبت نسخهٔ تحلیل</h2><p>با ثبت نسخه، اعداد فعلی با همین نرخ‌ها و جریان نقدی ذخیره می‌شوند.</p></div></div>
        <form method="post" action="<?= ee_e(ee_url('analysis', 'saveSnapshot', ['id' => (int) $analysis['project']['id']])) ?>" class="ee-form-actions">
            <?= csrf_field() ?>
            <button class="ee-button ee-button-accent" type="submit"><i class="fa-solid fa-camera"></i> ثبت نسخهٔ تحلیل</button>
        </form>

        <?php if ($analysis['snapshots']): ?>
            <div class="ee-table-wrap" style="margin-top:14px;">
                <table class="ee-table">
                    <thead><tr><th>روش</th><th>توضیح</th><th>زمان ثبت</th></tr></thead>
                    <tbody>
                        <?php foreach ($analysis['snapshots'] as $snapshot): ?>
                            <tr>
                                <td><?= ee_e($snapshot['method']) ?></td>
                                <td><?= ee_e($snapshot['note'] ?: '—') ?></td>
                                <td class="ee-num"><?= ee_e(ee_date($snapshot['computed_at'], 'Y/m/d H:i')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>
