<?php
$currentStatus = (string) $project['status'];
$nextStatus = ['draft' => 'active', 'active' => 'completed', 'completed' => 'archived'][$currentStatus] ?? null;
$nextLabel = ['active' => 'شروع تحلیل', 'completed' => 'نهایی‌سازی', 'archived' => 'انتقال به بایگانی'][$nextStatus] ?? null;
?>
<section class="ee-page" dir="rtl">
    <header class="ee-page-head">
        <div>
            <span class="ee-eyebrow"><?= ee_e($project['project_no'] ?: 'پروژه') ?> · <?= ee_e(ee_kind_label((string) $project['study_kind'])) ?></span>
            <h1><i class="fa-solid fa-chart-line"></i> <?= ee_e($project['title']) ?></h1>
            <p><?= $project['description'] ? ee_e($project['description']) : 'گزارش تحلیلی پروژه' ?></p>
        </div>
        <div class="ee-head-actions">
            <span class="ee-status <?= ee_e($project['status']) ?>"><?= ee_e(ee_status_label((string) $project['status'])) ?></span>
            <a class="ee-button ee-button-primary" href="<?= ee_e(ee_url('cashflow', 'index', ['id' => (int) $project['id']])) ?>"><i class="fa-solid fa-table-columns"></i> گزینه‌ها و جریان نقدی</a>
            <a class="ee-button ee-button-accent" href="<?= ee_e(ee_url('analysis', 'index', ['id' => (int) $project['id']])) ?>"><i class="fa-solid fa-chart-line"></i> تحلیل و مقایسه</a>
            <a class="ee-button" href="<?= ee_e(ee_url('analysis', 'report', ['id' => (int) $project['id']])) ?>"><i class="fa-solid fa-file-signature"></i> گزارش چاپی</a>
            <a class="ee-button ee-button-ghost" href="<?= ee_e(ee_url('project', 'index')) ?>"><i class="fa-solid fa-arrow-right"></i> فهرست</a>
            <?php if ($nextStatus): ?>
                <form method="post" action="<?= ee_e(ee_url('project', 'status', ['id' => (int) $project['id']])) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" value="<?= ee_e($nextStatus) ?>">
                    <button class="ee-button" type="submit"><?= ee_e($nextLabel) ?></button>
                </form>
            <?php endif; ?>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="ee-alert <?= ee_e($flash['type']) ?>" role="status"><?= ee_e($flash['message']) ?></div>
    <?php endif; ?>

    <section class="ee-kpis" aria-label="فرض‌های کلان">
        <article class="ee-kpi is-accent"><span>نرخ بازدهٔ حداقلی</span><strong><?= ee_num($project['marr_pct'], 2) ?>٪</strong><small>مبنای همهٔ محاسبات</small></article>
        <article class="ee-kpi"><span>دورهٔ مطالعه</span><strong><?= (int) $project['study_period'] ?> سال</strong><small>ارز <?= ee_e($project['currency']) ?></small></article>
        <article class="ee-kpi"><span>تورم سالانه</span><strong><?= ee_num($project['inflation_pct'], 2) ?>٪</strong><small>سیاست تبدیل نرخ</small></article>
        <article class="ee-kpi"><span>مالیات / استهلاک</span><strong><?= ee_num($project['tax_rate_pct'], 1) ?>٪</strong><small><?= ee_e(ee_method_label((string) $project['depreciation_method'])) ?></small></article>
    </section>

    <section class="ee-card">
        <div class="ee-card-head">
            <div><span class="ee-eyebrow">معیارهای تصمیم</span><h2>گزینه‌ها و نتایج</h2></div>
            <span class="ee-tag"><?= count($analyses) ?> گزینه</span>
        </div>

        <?php if ($analyses): ?>
            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead>
                        <tr>
                            <th>گزینه</th>
                            <th>NPV</th>
                            <th>IRR</th>
                            <th>ERR</th>
                            <th>MIRR</th>
                            <th>بازگشت</th>
                            <th>بازگشت تنزیل‌شده</th>
                            <th>PI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($analyses as $row): ?>
                            <tr>
                                <td>
                                    <strong><?= ee_e($row['alternative']['name']) ?></strong>
                                    <small><?= $row['alternative']['is_baseline'] ? 'گزینهٔ پایه' : 'گزینهٔ جایگزین' ?> · عمر <?= (int) $row['alternative']['life_years'] ?> سال</small>
                                </td>
                                <td class="ee-num"><?= ee_money($row['npv'], $project['currency']) ?></td>
                                <td class="ee-num"><?= ee_pct($row['irr']) ?></td>
                                <td class="ee-num"><?= ee_pct($row['err']) ?></td>
                                <td class="ee-num"><?= ee_pct($row['mirr']) ?></td>
                                <td class="ee-num"><?= $row['payback'] === null ? '—' : ee_num($row['payback'], 2) . ' سال' ?></td>
                                <td class="ee-num"><?= $row['discountedPayback'] === null ? '—' : ee_num($row['discountedPayback'], 2) . ' سال' ?></td>
                                <td class="ee-num"><?= ee_ratio($row['pi']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php
            $allIssues = [];
            foreach ($analyses as $row) {
                foreach ($row['issues'] as $issue) {
                    $allIssues[] = $row['alternative']['name'] . ': ' . $issue;
                }
            }
            ?>
            <?php if ($allIssues): ?>
                <div class="ee-note" style="margin-top:16px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <strong>بازبینی پیش از تصمیم</strong>
                        <ul><?php foreach (array_unique($allIssues) as $issue): ?><li><?= ee_e($issue) ?></li><?php endforeach; ?></ul>
                    </div>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="ee-empty">
                <i class="fa-solid fa-layer-group"></i>
                <strong>برای این پروژه گزینه‌ای ثبت نشده است</strong>
                <span>هر گزینه یک جریان نقدی سال‌به‌سال دارد؛ پس از ثبت گزینه‌ها، NPV و IRR در همین جدول محاسبه می‌شود.</span>
                <a class="ee-button ee-button-primary" href="<?= ee_e(ee_url('cashflow', 'index', ['id' => (int) $project['id']])) ?>">ساخت گزینه و ثبت جریان نقدی</a>
            </div>
        <?php endif; ?>
    </section>

    <?php if (!empty($project['assumptions'])): ?>
        <section class="ee-card">
            <div class="ee-card-head"><div><span class="ee-eyebrow">ردیابی</span><h2>فرض‌های مستند</h2></div></div>
            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead><tr><th>فرض</th><th>مقدار</th><th>واحد</th><th>منبع</th></tr></thead>
                    <tbody>
                        <?php foreach ($project['assumptions'] as $assumption): ?>
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

    <section class="ee-card ee-no-print">
        <div class="ee-card-head"><div><span class="ee-eyebrow">تفسیر</span><h2>نحوهٔ خواندن نتیجه</h2></div></div>
        <ul style="margin:0;padding-inline-start:20px;line-height:2;">
            <li><strong>NPV:</strong> مثبت یعنی بازده پروژه از MARR بیشتر است؛ بین گزینه‌ها، NPV بزرگ‌تر بهتر است (با فرض سرمایهٔ کافی).</li>
            <li><strong>IRR:</strong> فقط با جریان نقدی متعارف (یک تغییر علامت) قابل اتکاست؛ در جریان غیرمتعارف ممکن است چند ریشه داشته باشد.</li>
            <li><strong>ERR/MIRR:</strong> فرض سرمایه‌گذاری مجدد با نرخ واقع‌بینانه‌تر (MARR) را صریح می‌کنند و برای مقایسه امن‌ترند.</li>
            <li><strong>دورهٔ بازگشت:</strong> معیار نقدینگی است، نه سودآوری؛ بازگشت تنزیل‌شده ارزش زمانی پول را لحاظ می‌کند.</li>
            <li><strong>انتخاب نهایی:</strong> در پروژه‌های محدود سرمایه، تحلیل افزایشی (ΔIRR) معیار درست است، نه مقایسهٔ IRR جداگانه.</li>
        </ul>
    </section>
</section>
