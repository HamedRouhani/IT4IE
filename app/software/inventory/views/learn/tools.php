<?php
$activeTool = (string) ($result['tool'] ?? '');
$val = static fn(string $key, $default = '') => $values[$key] ?? $default;
$isOpen = static fn(string $tool): string => $activeTool === $tool ? ' open' : '';
$defaultAbc = "پیچ M8 | 50000000\nبلبرینگ 6204 | 20000000\nواشر فنری | 15000000\nروغن هیدرولیک | 10000000\nدستکش کار | 5000000";
?>
<section class="inv-page" dir="rtl">
    <header class="inv-page-head">
        <div>
            <span class="inv-eyebrow">تمرین عملی</span>
            <h1><i class="fa-solid fa-calculator"></i> ماشین‌حساب‌های آموزشی موجودی</h1>
            <p>هر ابزار نتیجه را گام‌به‌گام نشان می‌دهد؛ اعداد را تغییر دهید تا اثر هر پارامتر را ببینید.</p>
        </div>
        <div class="inv-head-actions">
            <a class="inv-btn inv-btn-secondary" href="<?= inv_e(inv_url('learn', 'index')) ?>">درس‌ها</a>
            <a class="inv-btn inv-btn-secondary" href="<?= inv_e(inv_url('learn', 'glossary')) ?>">واژه‌نامه</a>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="inv-alert <?= inv_e($flash['type']) ?>" role="status"><?= inv_e($flash['message']) ?></div>
    <?php endif; ?>

    <?php if ($result): ?>
        <section class="inv-card inv-result-card">
            <div class="inv-card-head"><div><span class="inv-eyebrow">نتیجه</span><h2><?= inv_e($result['title'] ?? 'نتیجهٔ محاسبه') ?></h2></div></div>

            <?php if (!empty($result['error'])): ?>
                <div class="inv-alert danger"><?= inv_e($result['error']) ?></div>
            <?php else: ?>
                <?php if (!empty($result['metrics'])): ?>
                    <div class="inv-result-metrics">
                        <?php foreach ($result['metrics'] as $metric): ?>
                            <div class="inv-result-metric">
                                <span><?= inv_e($metric['label']) ?></span>
                                <strong class="inv-num"><?= inv_e($metric['value']) ?></strong>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($result['rows'])): ?>
                    <div class="inv-table-wrap" style="margin-top:14px;">
                        <table class="inv-table inv-tool-table">
                            <thead>
                                <tr><?php foreach ($result['columns'] as $column): ?><th><?= inv_e($column) ?></th><?php endforeach; ?></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($result['rows'] as $row): ?>
                                    <tr><?php foreach ($row as $cell): ?><td class="inv-num"><?= inv_e($cell) ?></td><?php endforeach; ?></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if (!empty($result['note'])): ?>
                    <div class="inv-note" style="margin-top:14px;"><i class="fa-solid fa-lightbulb"></i><div><?= inv_e($result['note']) ?></div></div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <details class="inv-tool"<?= $isOpen('eoq') ?>>
        <summary><i class="fa-solid fa-calculator"></i> مقدار اقتصادی سفارش (EOQ)</summary>
        <form class="inv-form" method="post" action="<?= inv_e(inv_url('learn', 'tools')) ?>">
            <?= $csrfHtml ?>
            <input type="hidden" name="tool" value="eoq">
            <div class="inv-form-grid">
                <label>تقاضای سالانه (واحد)<input name="demand" inputmode="decimal" required value="<?= inv_e($val('demand', '10000')) ?>"></label>
                <label>هزینهٔ هر سفارش<input name="order_cost" inputmode="decimal" required value="<?= inv_e($val('order_cost', '500000')) ?>"></label>
                <label>بهای واحد<input name="unit_cost" inputmode="decimal" required value="<?= inv_e($val('unit_cost', '100000')) ?>"></label>
                <label>نرخ نگهداری سالانه (٪)<input name="holding_rate" inputmode="decimal" required value="<?= inv_e($val('holding_rate', '25')) ?>"></label>
            </div>
            <div class="inv-form-actions"><button class="inv-btn inv-btn-primary" type="submit">محاسبهٔ EOQ</button></div>
        </form>
    </details>

    <details class="inv-tool"<?= $isOpen('safety') ?>>
        <summary><i class="fa-solid fa-shield-halved"></i> موجودی اطمینان و نقطهٔ سفارش</summary>
        <form class="inv-form" method="post" action="<?= inv_e(inv_url('learn', 'tools')) ?>">
            <?= $csrfHtml ?>
            <input type="hidden" name="tool" value="safety">
            <div class="inv-form-grid">
                <label>میانگین تقاضای روزانه (واحد)<input name="daily_demand" inputmode="decimal" required value="<?= inv_e($val('daily_demand', '120')) ?>"></label>
                <label>انحراف معیار تقاضای روزانه<input name="daily_std" inputmode="decimal" required value="<?= inv_e($val('daily_std', '25')) ?>"></label>
                <label>زمان تأمین (روز)<input name="lead_time" inputmode="decimal" required value="<?= inv_e($val('lead_time', '16')) ?>"></label>
                <label>سطح خدمت
                    <select name="service_level">
                        <?php foreach ($serviceLevels as $key => $label): ?>
                            <option value="<?= inv_e($key) ?>" <?= (string) $val('service_level', '97.5') === (string) $key ? 'selected' : '' ?>><?= inv_e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div class="inv-form-actions"><button class="inv-btn inv-btn-primary" type="submit">محاسبهٔ موجودی اطمینان</button></div>
        </form>
    </details>

    <details class="inv-tool"<?= $isOpen('abc') ?>>
        <summary><i class="fa-solid fa-layer-group"></i> طبقه‌بندی ABC</summary>
        <form class="inv-form" method="post" action="<?= inv_e(inv_url('learn', 'tools')) ?>">
            <?= $csrfHtml ?>
            <input type="hidden" name="tool" value="abc">
            <div class="inv-form-grid">
                <label class="inv-span-2">اقلام و ارزش مصرف سالانه (هر قلم در یک خط: نام | ارزش)
                    <textarea name="items" rows="7" required><?= inv_e($val('items', $defaultAbc)) ?></textarea>
                    <small>نام اختیاری است؛ اگر ننوشتید، «قلم ۱» و «قلم ۲» جایگزین می‌شود. جداکنندهٔ نام و عدد می‌تواند خط عمودی، ویرگول، سمی‌کولون یا دونقطه باشد. ارقام فارسی هم پذیرفته می‌شود.</small>
                </label>
            </div>
            <div class="inv-form-actions"><button class="inv-btn inv-btn-primary" type="submit">طبقه‌بندی ABC</button></div>
        </form>
    </details>

    <details class="inv-tool"<?= $isOpen('policy') ?>>
        <summary><i class="fa-solid fa-sliders"></i> سیاست سفارش یک قلم</summary>
        <form class="inv-form" method="post" action="<?= inv_e(inv_url('learn', 'tools')) ?>">
            <?= $csrfHtml ?>
            <input type="hidden" name="tool" value="policy">
            <div class="inv-form-grid">
                <label>تقاضای سالانه (واحد)<input name="annual_demand" inputmode="decimal" required value="<?= inv_e($val('annual_demand', '18250')) ?>"></label>
                <label>انحراف معیار تقاضای روزانه<input name="daily_std" inputmode="decimal" required value="<?= inv_e($val('daily_std', '12')) ?>"></label>
                <label>زمان تأمین (روز)<input name="lead_time" inputmode="decimal" required value="<?= inv_e($val('lead_time', '10')) ?>"></label>
                <label>سطح خدمت
                    <select name="service_level">
                        <?php foreach ($serviceLevels as $key => $label): ?>
                            <option value="<?= inv_e($key) ?>" <?= (string) $val('service_level', '95') === (string) $key ? 'selected' : '' ?>><?= inv_e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>موجودی در دسترس<input name="on_hand" inputmode="decimal" required value="<?= inv_e($val('on_hand', '480')) ?>"></label>
                <label>سفارش در راه<input name="on_order" inputmode="decimal" value="<?= inv_e($val('on_order', '50')) ?>"></label>
                <label>بهای واحد<input name="unit_cost" inputmode="decimal" required value="<?= inv_e($val('unit_cost', '40000')) ?>"></label>
                <label>هزینهٔ هر سفارش<input name="order_cost" inputmode="decimal" required value="<?= inv_e($val('order_cost', '800000')) ?>"></label>
                <label>نرخ نگهداری سالانه (٪)<input name="holding_rate" inputmode="decimal" required value="<?= inv_e($val('holding_rate', '20')) ?>"></label>
            </div>
            <div class="inv-form-actions"><button class="inv-btn inv-btn-primary" type="submit">محاسبهٔ سیاست سفارش</button></div>
        </form>
    </details>

    <details class="inv-tool"<?= $isOpen('kpis') ?>>
        <summary><i class="fa-solid fa-gauge-high"></i> شاخص‌های عملکرد موجودی</summary>
        <form class="inv-form" method="post" action="<?= inv_e(inv_url('learn', 'tools')) ?>">
            <?= $csrfHtml ?>
            <input type="hidden" name="tool" value="kpis">
            <div class="inv-form-grid">
                <label>هزینهٔ کالای فروش‌رفتهٔ سالانه<input name="cogs" inputmode="decimal" required value="<?= inv_e($val('cogs', '720000000')) ?>"></label>
                <label>موجودی متوسط به بهای تمام‌شده<input name="average_inventory" inputmode="decimal" required value="<?= inv_e($val('average_inventory', '90000000')) ?>"></label>
                <label>واحدهای تأمین‌شده<input name="units_shipped" inputmode="decimal" required value="<?= inv_e($val('units_shipped', '9600')) ?>"></label>
                <label>واحدهای درخواستی<input name="units_demanded" inputmode="decimal" required value="<?= inv_e($val('units_demanded', '10000')) ?>"></label>
            </div>
            <div class="inv-form-actions"><button class="inv-btn inv-btn-primary" type="submit">محاسبهٔ شاخص‌ها</button></div>
        </form>
    </details>

    <div class="inv-note">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <strong>دقت محاسبه</strong>
            <ul>
                <li>همهٔ محاسبات با دقت کامل انجام و فقط برای نمایش گرد می‌شوند.</li>
                <li>فرض‌ها همان فرض‌های بخش «تحلیل و سفارش» است: زمان تأمین ثابت، تقاضای روزانه مستقل و سال ۳۶۵ روزه.</li>
                <li>نتایج این ابزارها آموزشی است؛ برای تصمیم واقعی از بخش «تحلیل و سفارش» استفاده کنید تا پارامترهای هر قلم ثبت و قابل پیگیری بماند.</li>
            </ul>
        </div>
    </div>
</section>
