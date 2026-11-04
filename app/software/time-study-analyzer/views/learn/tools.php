<?php
$activeTool = (string) ($result['tool'] ?? '');
$val = static fn(string $key, $default = '') => $values[$key] ?? $default;
$isOpen = static fn(string $tool): string => $activeTool === $tool ? ' open' : '';
$confidences = ['90' => '۹۰٪', '95' => '۹۵٪', '99' => '۹۹٪'];
$levels = [
    'skill' => ['A1' => 'A1 — عالی (+۱۵)', 'A2' => 'A2 (+۱۳)', 'B1' => 'B1 — خوب (+۱۱)', 'B2' => 'B2 (+۸)', 'C1' => 'C1 — متوسط (+۶)', 'C2' => 'C2 (+۳)', 'D' => 'D — نرمال (۰)', 'E1' => 'E1 — ضعیف (−۵)', 'E2' => 'E2 (−۱۰)', 'F1' => 'F1 — خیلی ضعیف (−۱۶)', 'F2' => 'F2 (−۲۲)'],
    'effort' => ['A1' => 'A1 — عالی (+۱۳)', 'A2' => 'A2 (+۱۲)', 'B1' => 'B1 — خوب (+۱۰)', 'B2' => 'B2 (+۸)', 'C1' => 'C1 — متوسط (+۵)', 'C2' => 'C2 (+۲)', 'D' => 'D — نرمال (۰)', 'E1' => 'E1 — ضعیف (−۴)', 'E2' => 'E2 (−۸)', 'F1' => 'F1 — خیلی ضعیف (−۱۲)', 'F2' => 'F2 (−۱۷)'],
    'conditions' => ['A' => 'A — عالی (+۶)', 'B' => 'B — خوب (+۴)', 'C' => 'C — متوسط (+۲)', 'D' => 'D — نرمال (۰)', 'E' => 'E — ضعیف (−۳)', 'F' => 'F — خیلی ضعیف (−۷)'],
    'consistency' => ['A' => 'A — کاملاً یکنواخت (+۴)', 'B' => 'B — خوب (+۳)', 'C' => 'C — متوسط (+۱)', 'D' => 'D — نرمال (۰)', 'E' => 'E — کم (−۲)', 'F' => 'F — خیلی کم (−۴)'],
];
$factorLabels = ['skill' => 'مهارت', 'effort' => 'کوشش', 'conditions' => 'شرایط', 'consistency' => 'یکنواختی'];
$selects = static function (string $factor) use ($levels, $val): string {
    $html = '<select name="' . $factor . '">';
    foreach ($levels[$factor] as $key => $label) {
        $selected = (string) $val($factor, 'D') === $key ? ' selected' : '';
        $html .= '<option value="' . $key . '"' . $selected . '>' . $label . '</option>';
    }
    $html .= '</select>';

    return $html;
};
?>
<section class="ts-page" dir="rtl">
    <header class="ts-page-head">
        <div>
            <span class="ts-eyebrow">تمرین عملی</span>
            <h1><i class="fa-solid fa-calculator"></i> ماشین‌حساب‌های آموزشی</h1>
            <p>هر ابزار نتیجه را گام‌به‌گام نشان می‌دهد؛ اعداد را تغییر دهید تا اثر هر پارامتر را ببینید.</p>
        </div>
        <div class="ts-head-actions">
            <a class="ts-button ts-button-outline" href="<?= ts_e(ts_url('learn', 'index')) ?>">درس‌ها</a>
            <a class="ts-button ts-button-outline" href="<?= ts_e(ts_url('learn', 'glossary')) ?>">واژه‌نامه</a>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="ts-alert <?= ts_e($flash['type']) ?>" role="status"><?= ts_e($flash['message']) ?></div>
    <?php endif; ?>

    <?php if ($result): ?>
        <section class="ts-card">
            <div class="ts-card-head"><div><span class="ts-eyebrow">نتیجه</span><h2><?= ts_e($result['title'] ?? 'نتیجهٔ محاسبه') ?></h2></div></div>

            <?php if (!empty($result['error'])): ?>
                <div class="ts-alert danger"><?= ts_e($result['error']) ?></div>
            <?php else: ?>
                <?php if (!empty($result['metrics'])): ?>
                    <div class="ts-result-metrics">
                        <?php foreach ($result['metrics'] as $metric): ?>
                            <div class="ts-result-metric"><span><?= ts_e($metric['label']) ?></span><strong class="ts-num"><?= ts_e($metric['value']) ?></strong></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($result['rows'])): ?>
                    <div class="ts-table-wrap" style="margin-top:14px;">
                        <table class="ts-table">
                            <thead><tr><?php foreach ($result['columns'] as $column): ?><th><?= ts_e($column) ?></th><?php endforeach; ?></tr></thead>
                            <tbody>
                                <?php foreach ($result['rows'] as $row): ?>
                                    <tr><?php foreach ($row as $cell): ?><td class="ts-num"><?= ts_e($cell) ?></td><?php endforeach; ?></tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <?php if (!empty($result['note'])): ?>
                    <div class="ts-note ts-note-compact" style="margin-top:14px;"><i class="fa-solid fa-lightbulb"></i><div><?= ts_e($result['note']) ?></div></div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <details class="ts-tool"<?= $isOpen('sample-size') ?>>
        <summary><i class="fa-solid fa-chart-simple"></i> تعداد چرخهٔ لازم (کفایت نمونه)</summary>
        <form class="ts-form" method="post" action="<?= ts_e(ts_url('learn', 'tools')) ?>">
            <?= $csrfHtml ?>
            <input type="hidden" name="tool" value="sample-size">
            <div class="ts-form-grid">
                <label>میانگین زمان چرخه (ثانیه)<input name="mean" inputmode="decimal" required value="<?= ts_e($val('mean', '10')) ?>"></label>
                <label>انحراف معیار (ثانیه)<input name="std" inputmode="decimal" required value="<?= ts_e($val('std', '0.5')) ?>"></label>
                <label>دقت نسبی هدف (٪)<input name="precision" inputmode="decimal" required value="<?= ts_e($val('precision', '5')) ?>"></label>
                <label>سطح اطمینان
                    <select name="confidence">
                        <?php foreach ($confidences as $key => $label): ?>
                            <option value="<?= ts_e($key) ?>" <?= (string) $val('confidence', '95') === $key ? 'selected' : '' ?>><?= ts_e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div class="ts-form-actions"><button class="ts-button ts-button-primary" type="submit">محاسبه</button></div>
        </form>
    </details>

    <details class="ts-tool"<?= $isOpen('standard-time') ?>>
        <summary><i class="fa-solid fa-hourglass-end"></i> زمان استاندارد</summary>
        <form class="ts-form" method="post" action="<?= ts_e(ts_url('learn', 'tools')) ?>">
            <?= $csrfHtml ?>
            <input type="hidden" name="tool" value="standard-time">
            <div class="ts-form-grid">
                <label>زمان مشاهده‌شده (ثانیه)<input name="observed" inputmode="decimal" required value="<?= ts_e($val('observed', '10')) ?>"></label>
                <label>ارزیابی عملکرد (٪)<input name="rating" inputmode="decimal" required value="<?= ts_e($val('rating', '110')) ?>"></label>
                <label>تکرار در هر واحد<input name="frequency" inputmode="decimal" required value="<?= ts_e($val('frequency', '1')) ?>"></label>
                <label>زمان ماشین (ثانیه، بدون allowance)<input name="machine" inputmode="decimal" value="<?= ts_e($val('machine', '0')) ?>"></label>
                <label>مبنای allowance
                    <select name="basis">
                        <option value="standard" <?= (string) $val('basis', 'standard') === 'standard' ? 'selected' : '' ?>>درصدی از زمان استاندارد (NT ÷ (1−A))</option>
                        <option value="normal" <?= (string) $val('basis', 'standard') === 'normal' ? 'selected' : '' ?>>افزوده بر زمان نرمال (NT × (1+A))</option>
                    </select>
                </label>
                <label>نیاز شخصی (٪)<input name="personal" inputmode="decimal" value="<?= ts_e($val('personal', '5')) ?>"></label>
                <label>خستگی (٪)<input name="fatigue" inputmode="decimal" value="<?= ts_e($val('fatigue', '4')) ?>"></label>
                <label>تأخیر اجتناب‌ناپذیر (٪)<input name="delay" inputmode="decimal" value="<?= ts_e($val('delay', '3')) ?>"></label>
                <label>سایر (٪)<input name="other" inputmode="decimal" value="<?= ts_e($val('other', '0')) ?>"></label>
            </div>
            <div class="ts-form-actions"><button class="ts-button ts-button-primary" type="submit">محاسبهٔ زمان استاندارد</button></div>
        </form>
    </details>

    <details class="ts-tool"<?= $isOpen('rating') ?>>
        <summary><i class="fa-solid fa-gauge-high"></i> ارزیابی عملکرد با وستینگهاوس</summary>
        <form class="ts-form" method="post" action="<?= ts_e(ts_url('learn', 'tools')) ?>">
            <?= $csrfHtml ?>
            <input type="hidden" name="tool" value="rating">
            <div class="ts-form-grid">
                <?php foreach ($factorLabels as $factor => $label): ?>
                    <label><?= ts_e($label) ?><?= $selects($factor) ?></label>
                <?php endforeach; ?>
                <label>زمان مشاهده‌شده (ثانیه، اختیاری)<input name="observed" inputmode="decimal" value="<?= ts_e($val('observed', '10')) ?>"></label>
            </div>
            <div class="ts-form-actions"><button class="ts-button ts-button-primary" type="submit">محاسبهٔ نرخ ارزیابی</button></div>
        </form>
    </details>

    <details class="ts-tool"<?= $isOpen('confidence') ?>>
        <summary><i class="fa-solid fa-arrows-left-right"></i> بازهٔ اطمینان میانگین چرخه</summary>
        <form class="ts-form" method="post" action="<?= ts_e(ts_url('learn', 'tools')) ?>">
            <?= $csrfHtml ?>
            <input type="hidden" name="tool" value="confidence">
            <div class="ts-form-grid">
                <label>میانگین (ثانیه)<input name="mean" inputmode="decimal" required value="<?= ts_e($val('mean', '10')) ?>"></label>
                <label>انحراف معیار (ثانیه)<input name="std" inputmode="decimal" required value="<?= ts_e($val('std', '0.5')) ?>"></label>
                <label>تعداد چرخه<input name="count" type="number" min="2" max="1000" required value="<?= ts_e($val('count', '7')) ?>"></label>
                <label>سطح اطمینان
                    <select name="confidence">
                        <?php foreach ($confidences as $key => $label): ?>
                            <option value="<?= ts_e($key) ?>" <?= (string) $val('confidence', '95') === $key ? 'selected' : '' ?>><?= ts_e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div class="ts-form-actions"><button class="ts-button ts-button-primary" type="submit">محاسبهٔ بازه</button></div>
        </form>
    </details>

    <details class="ts-tool"<?= $isOpen('sampling') ?>>
        <summary><i class="fa-solid fa-chart-pie"></i> نمونه‌برداری کار (نسبت و حجم نمونه)</summary>
        <form class="ts-form" method="post" action="<?= ts_e(ts_url('learn', 'tools')) ?>">
            <?= $csrfHtml ?>
            <input type="hidden" name="tool" value="sampling">
            <div class="ts-form-grid">
                <label>کل مشاهده‌های معتبر<input name="total" type="number" min="1" max="1000000" required value="<?= ts_e($val('total', '289')) ?>"></label>
                <label>مشاهده‌های دستهٔ مولد<input name="count" type="number" min="0" max="1000000" required value="<?= ts_e($val('count', '72')) ?>"></label>
                <label>دقت مطلق (واحد درصد)<input name="precision" inputmode="decimal" required value="<?= ts_e($val('precision', '5')) ?>"></label>
                <label>سطح اطمینان
                    <select name="confidence">
                        <?php foreach ($confidences as $key => $label): ?>
                            <option value="<?= ts_e($key) ?>" <?= (string) $val('confidence', '95') === $key ? 'selected' : '' ?>><?= ts_e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <div class="ts-form-actions"><button class="ts-button ts-button-primary" type="submit">برآورد نسبت</button></div>
        </form>
    </details>

    <div class="ts-note ts-note-compact">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <strong>دقت محاسبه</strong>
            <ul>
                <li>همهٔ محاسبات با دقت کامل انجام و فقط برای نمایش گرد می‌شوند.</li>
                <li>این ابزارها آموزشی‌اند؛ برای ثبت داده و گزارش رسمی از بخش «مطالعات زمان‌سنجی» استفاده کنید.</li>
            </ul>
        </div>
    </div>
</section>
