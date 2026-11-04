<?php
$activeTool = (string) ($result['tool'] ?? '');
$val = static fn(string $key, $default = '') => $values[$key] ?? $default;
$isOpen = static fn(string $tool): string => $activeTool === $tool ? ' open' : '';
?>
<section class="ee-page" dir="rtl">
    <header class="ee-page-head">
        <div>
            <span class="ee-eyebrow">تمرین عملی</span>
            <h1><i class="fa-solid fa-calculator"></i> ماشین‌حساب‌های آموزشی</h1>
            <p>هر ابزار نتیجه را گام‌به‌گام نشان می‌دهد؛ اعداد را تغییر دهید تا اثر هر پارامتر را ببینید.</p>
        </div>
        <div class="ee-head-actions">
            <a class="ee-button" href="<?= ee_e(ee_url('learn', 'index')) ?>">درس‌ها</a>
            <a class="ee-button" href="<?= ee_e(ee_url('learn', 'glossary')) ?>">واژه‌نامه</a>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="ee-alert <?= ee_e($flash['type']) ?>" role="status"><?= ee_e($flash['message']) ?></div>
    <?php endif; ?>

    <?php if ($result): ?>
        <section class="ee-card ee-result-card">
            <div class="ee-card-head"><div><span class="ee-eyebrow">نتیجه</span><h2><?= ee_e($result['title'] ?? 'نتیجهٔ محاسبه') ?></h2></div></div>

            <?php if (!empty($result['error'])): ?>
                <div class="ee-alert danger"><?= ee_e($result['error']) ?></div>
            <?php else: ?>
                <?php if (!empty($result['metrics'])): ?>
                    <div class="ee-result-metrics">
                        <?php foreach ($result['metrics'] as $metric): ?>
                            <div class="ee-result-metric">
                                <span><?= ee_e($metric['label']) ?></span>
                                <strong class="ee-num"><?= ee_e($metric['value']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($result['rows'])): ?>
                    <div class="ee-table-wrap" style="margin-top:14px;">
                        <table class="ee-table">
                            <thead>
                                <tr><?php foreach ($result['columns'] as $column): ?><th><?= ee_e($column) ?></th><?php endforeach; ?></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($result['rows'] as $row): ?>
                                    <tr><?php foreach ($row as $cell): ?><td class="ee-num"><?= ee_e($cell) ?></td><?php endforeach; ?></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if (!empty($result['note'])): ?>
                    <div class="ee-note" style="margin-top:14px;"><i class="fa-solid fa-lightbulb"></i><div><?= ee_e($result['note']) ?></div></div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <details class="ee-tool"<?= $isOpen('factors') ?>>
        <summary><i class="fa-solid fa-superscript"></i> فاکتورهای ارزش زمانی پول</summary>
        <form class="ee-form" method="post" action="<?= ee_e(ee_url('learn', 'tools')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="tool" value="factors">
            <div class="ee-form-grid">
                <label>نرخ بهره (٪)<input name="rate" inputmode="decimal" required value="<?= ee_e($val('rate', '12')) ?>"></label>
                <label>تعداد دوره<input name="periods" type="number" min="1" max="100" required value="<?= ee_e($val('periods', '5')) ?>"></label>
            </div>
            <div class="ee-form-actions"><button class="ee-button ee-button-primary" type="submit">محاسبه</button></div>
        </form>
    </details>

    <details class="ee-tool"<?= $isOpen('npv') ?>>
        <summary><i class="fa-solid fa-sack-dollar"></i> NPV، IRR و دورهٔ بازگشت</summary>
        <form class="ee-form" method="post" action="<?= ee_e(ee_url('learn', 'tools')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="tool" value="npv">
            <div class="ee-form-grid">
                <label>نرخ تنزیل (٪)<input name="rate" inputmode="decimal" required value="<?= ee_e($val('rate', '10')) ?>"></label>
                <label class="ee-span-2">جریان نقدی (هر عدد یک دوره، از سال ۰)
                    <textarea name="flows" rows="6" dir="ltr" required placeholder="-10000000&#10;3000000&#10;3000000&#10;3000000&#10;3000000&#10;3000000"><?= ee_e($val('flows', "-10000000\n3000000\n3000000\n3000000\n3000000\n3000000")) ?></textarea>
                    <small>خروجی را با منفی یا داخل پرانتز بنویسید. ارقام فارسی هم پذیرفته می‌شود.</small>
                </label>
            </div>
            <div class="ee-form-actions"><button class="ee-button ee-button-primary" type="submit">تحلیل جریان</button></div>
        </form>
    </details>

    <details class="ee-tool"<?= $isOpen('depreciation') ?>>
        <summary><i class="fa-solid fa-building-columns"></i> جدول استهلاک</summary>
        <form class="ee-form" method="post" action="<?= ee_e(ee_url('learn', 'tools')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="tool" value="depreciation">
            <div class="ee-form-grid">
                <label>بهای دارایی<input name="cost" inputmode="decimal" required value="<?= ee_e($val('cost', '100000000')) ?>"></label>
                <label>ارزش اسقاط<input name="salvage" inputmode="decimal" required value="<?= ee_e($val('salvage', '20000000')) ?>"></label>
                <label>عمر (سال)<input name="life" type="number" min="1" max="100" required value="<?= ee_e($val('life', '5')) ?>"></label>
                <label>روش
                    <select name="method">
                        <?php foreach (['straight' => 'خط مستقیم', 'declining' => 'نزولی', 'soyd' => 'مجموع سنوات'] as $key => $label): ?>
                            <option value="<?= ee_e($key) ?>" <?= (string) $val('method', 'straight') === $key ? 'selected' : '' ?>><?= ee_e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>نرخ نزولی (٪، فقط برای روش نزولی)<input name="declining_rate" inputmode="decimal" value="<?= ee_e($val('declining_rate', '20')) ?>"></label>
            </div>
            <div class="ee-form-actions"><button class="ee-button ee-button-primary" type="submit">ساخت جدول</button></div>
        </form>
    </details>

    <details class="ee-tool"<?= $isOpen('inflation') ?>>
        <summary><i class="fa-solid fa-arrow-trend-up"></i> تورم و نرخ اسمی/حقیقی</summary>
        <form class="ee-form" method="post" action="<?= ee_e(ee_url('learn', 'tools')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="tool" value="inflation">
            <div class="ee-form-grid">
                <label>نرخ اسمی (٪)<input name="nominal" inputmode="decimal" value="<?= ee_e($val('nominal', '43')) ?>"></label>
                <label>تورم (٪)<input name="inflation" inputmode="decimal" required value="<?= ee_e($val('inflation', '30')) ?>"></label>
                <label>نرخ حقیقی (٪)<input name="real" inputmode="decimal" value="<?= ee_e($val('real', '10')) ?>"></label>
                <label>تعداد مرکب‌شدن در سال<input name="compoundings" type="number" min="1" max="365" value="<?= ee_e($val('compoundings', '12')) ?>"></label>
            </div>
            <div class="ee-form-actions"><button class="ee-button ee-button-primary" type="submit">تبدیل نرخ</button></div>
        </form>
    </details>

    <details class="ee-tool"<?= $isOpen('switching') ?>>
        <summary><i class="fa-solid fa-sliders"></i> ارزش تغییر (حساسیت یک سال)</summary>
        <form class="ee-form" method="post" action="<?= ee_e(ee_url('learn', 'tools')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="tool" value="switching">
            <div class="ee-form-grid">
                <label>نرخ تنزیل (٪)<input name="rate" inputmode="decimal" required value="<?= ee_e($val('rate', '15')) ?>"></label>
                <label>سال مورد بررسی<input name="year" type="number" min="0" max="100" required value="<?= ee_e($val('year', '1')) ?>"></label>
                <label class="ee-span-2">جریان نقدی (هر عدد یک دوره)
                    <textarea name="flows" rows="6" dir="ltr" required placeholder="-50000000&#10;15000000&#10;15000000"><?= ee_e($val('flows', "-50000000\n15000000\n15000000\n15000000\n15000000\n15000000")) ?></textarea>
                </label>
            </div>
            <div class="ee-form-actions"><button class="ee-button ee-button-primary" type="submit">محاسبهٔ حساسیت</button></div>
        </form>
    </details>

    <div class="ee-note">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <strong>دقت محاسبه</strong>
            <ul>
                <li>همهٔ محاسبات با دقت کامل انجام و فقط برای نمایش گرد می‌شوند.</li>
                <li>نتایج این ابزارها آموزشی است؛ برای تصمیم واقعی از بخش «پروژه‌ها» استفاده کنید تا فرض‌ها و نسخهٔ تحلیل ثبت شود.</li>
            </ul>
        </div>
    </div>
</section>
