<?php
$num = static function ($value): string {
    $formatted = number_format((float) $value, 4, '.', '');
    $trimmed = rtrim(rtrim($formatted, '0'), '.');

    return ee_e($trimmed === '' || $trimmed === '-' ? '0' : $trimmed);
};
$valueFor = static function (string $field, int $year) use ($old, $flowRows): string {
    $posted = $old[$field][$year] ?? null;
    if ($posted !== null) return ee_e((string) $posted);

    return '';
};
?>
<section class="ee-page" dir="rtl">
    <header class="ee-page-head">
        <div>
            <span class="ee-eyebrow"><?= ee_e($alternative['project_title']) ?></span>
            <h1><i class="fa-solid fa-table"></i> جریان نقدی گزینه</h1>
            <p><?= ee_e($alternative['name']) ?> · ارز <?= ee_e($alternative['currency']) ?> · MARR <?= ee_num($alternative['marr_pct'], 2) ?>٪</p>
        </div>
        <div class="ee-head-actions">
            <a class="ee-button" href="<?= ee_e(ee_url('cashflow', 'index', ['id' => (int) $alternative['project_id']])) ?>"><i class="fa-solid fa-arrow-right"></i> گزینه‌ها</a>
            <a class="ee-button ee-button-accent" href="<?= ee_e(ee_url('analysis', 'index', ['id' => (int) $alternative['project_id']])) ?>"><i class="fa-solid fa-chart-line"></i> تحلیل</a>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="ee-alert <?= ee_e($flash['type']) ?>" role="status"><?= ee_e($flash['message']) ?></div>
    <?php endif; ?>

    <form class="ee-form" method="post" action="<?= ee_e(ee_url('cashflow', 'save', ['id' => (int) $alternative['id']])) ?>">
        <?= csrf_field() ?>

        <section class="ee-card">
            <div class="ee-card-head"><div><span class="ee-eyebrow">مشخصات گزینه</span><h2>نام و عمر</h2></div></div>
            <div class="ee-form-grid">
                <label>نام گزینه *<input name="name" maxlength="160" required value="<?= ee_e($old['name'] ?? $alternative['name']) ?>"></label>
                <label>عمر مفید (سال)<input name="life_years" type="number" min="1" max="100" value="<?= ee_e($old['life_years'] ?? $alternative['life_years']) ?>"></label>
                <label class="ee-span-2">توضیح<textarea name="description" rows="2" maxlength="5000"><?= ee_e($old['description'] ?? ($alternative['description'] ?? '')) ?></textarea></label>
            </div>
        </section>

        <section class="ee-card">
            <div class="ee-card-head">
                <div><span class="ee-eyebrow">دادهٔ اصلی</span><h2>جریان نقدی سال‌به‌سال</h2><p>سال ۰ سرمایه‌گذاری اولیه؛ سال‌های بعد ورودی (درآمد/صرفه‌جویی) و خروجی (هزینه) را وارد کنید.</p></div>
                <span class="ee-tag"><?= count($flowRows) ?> سال</span>
            </div>

            <div class="ee-form-grid" style="margin-bottom:14px;">
                <label class="ee-span-2">چسباندن سریع از اکسل/متن
                    <textarea data-ee-paste rows="3" placeholder="هر خط یک سال؛ «ورودی،خروجی» یا یک عدد خالص (منفی = خروجی)&#10;مثال:&#10;-50000000&#10;12000000,3000000"></textarea>
                    <small>با «پخش در جدول» مقادیر به‌ترتیب سال‌ها (از سال ۰) پر می‌شوند.</small>
                </label>
                <div class="ee-form-actions ee-span-2">
                    <button class="ee-button ee-button-ghost" type="button" data-ee-paste-apply><i class="fa-solid fa-wand-magic-sparkles"></i> پخش در جدول</button>
                </div>
            </div>

            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead>
                        <tr><th>سال</th><th>ورودی (<?= ee_e($alternative['currency']) ?>)</th><th>خروجی (<?= ee_e($alternative['currency']) ?>)</th><th>برچسب / توضیح</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($flowRows as $flow): ?>
                            <?php $year = (int) $flow['year_no']; ?>
                            <tr>
                                <td class="ee-num"><strong><?= $year === 0 ? '۰ (سرمایه‌گذاری)' : $year ?></strong></td>
                                <td><input class="ee-num" inputmode="decimal" name="inflow[<?= $year ?>]" value="<?= $valueFor('inflow', $year) !== '' ? $valueFor('inflow', $year) : $num($flow['inflow']) ?>"></td>
                                <td><input class="ee-num" inputmode="decimal" name="outflow[<?= $year ?>]" value="<?= $valueFor('outflow', $year) !== '' ? $valueFor('outflow', $year) : $num($flow['outflow']) ?>"></td>
                                <td><input name="label[<?= $year ?>]" maxlength="120" value="<?= $valueFor('label', $year) !== '' ? $valueFor('label', $year) : ee_e($flow['label'] ?? '') ?>" placeholder="خرید دستگاه، تعمیر اساسی…"></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="ee-form-actions" style="margin-top:16px;">
                <button class="ee-button ee-button-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> ذخیرهٔ جریان نقدی</button>
                <a class="ee-button ee-button-ghost" href="<?= ee_e(ee_url('cashflow', 'index', ['id' => (int) $alternative['project_id']])) ?>">انصراف</a>
            </div>
        </section>
    </form>
</section>

<script>
(function () {
    'use strict';
    var box = document.querySelector('[data-ee-paste]');
    var apply = document.querySelector('[data-ee-paste-apply]');
    if (!box || !apply) return;

    var latin = function (value) {
        return String(value)
            .replace(/[۰-۹]/g, function (digit) { return String('۰۱۲۳۴۵۶۷۸۹'.indexOf(digit)); })
            .replace(/[٠-٩]/g, function (digit) { return String('٠١٢٣٤٥٦٧٨٩'.indexOf(digit)); })
            .replace(/[٫،]/g, '.');
    };

    apply.addEventListener('click', function () {
        var lines = latin(box.value || '').split(/\r?\n/).map(function (line) { return line.trim(); }).filter(Boolean);
        if (!lines.length) return;

        lines.forEach(function (line, index) {
            var inflow = document.querySelector('input[name="inflow[' + index + ']"]');
            var outflow = document.querySelector('input[name="outflow[' + index + ']"]');
            if (!inflow || !outflow) return;

            var parts = line.split(/[,;\t ]+/).filter(Boolean);
            if (parts.length >= 2) {
                inflow.value = parts[0];
                outflow.value = parts[1];
                return;
            }

            var amount = parseFloat(String(parts[0]).replace(/,/g, ''));
            if (isNaN(amount)) return;

            if (amount < 0) {
                outflow.value = String(Math.abs(amount));
                inflow.value = '0';
            } else {
                inflow.value = String(amount);
                outflow.value = '0';
            }
        });

        box.value = '';
    });
})();
</script>
