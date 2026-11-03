<?php
$method = $study['method'];
$status = $study['status'];
$active = $status === 'active';
$draft = $status === 'draft';
$stopwatch = $method === 'stopwatch';
$pendingSlots = [];
$slotRows = $study['slots'];
usort($slotRows, static function ($a, $b) {
    $state = static fn($row) => $row['slot_status'] === 'planned' ? 0 : 1;
    return ($state($a) <=> $state($b)) ?: strcmp($a['scheduled_at'], $b['scheduled_at']);
});
foreach ($slotRows as $slot) if ($slot['slot_status'] === 'planned') $pendingSlots[] = $slot;

$pendingCount = count($pendingSlots);
$observedCount = 0;
foreach ($slotRows as $slot) if ($slot['slot_status'] === 'observed') $observedCount++;

// مقادیر آخرین چرخه برای «کپی از چرخهٔ قبل» (فقط عناصر همان چرخه).
$lastCycleNo = 0;
foreach ($study['observations'] as $observation) {
    $lastCycleNo = max($lastCycleNo, (int) $observation['cycle_no']);
}
$lastCycleValues = [];
if ($lastCycleNo > 0) {
    foreach ($study['observations'] as $observation) {
        if ((int) $observation['cycle_no'] !== $lastCycleNo) continue;
        $lastCycleValues[(int) $observation['element_id']] = [
            'seconds' => (float) $observation['observed_seconds'],
            'rating' => (float) $observation['performance_rating_pct'],
        ];
    }
}
?>
<section class="ts-page" dir="rtl">
    <header class="ts-page-head ts-print-head"><div><span class="ts-eyebrow"><?= ts_e($study['study_no']) ?> · <?= $stopwatch ? 'زمان‌سنجی مستقیم' : 'نمونه‌برداری کار' ?></span><h1><?= ts_e($study['title']) ?></h1><p><?= ts_e($study['process_name']) ?><?= $study['product_name'] ? ' · ' . ts_e($study['product_name']) : '' ?><?= $study['work_center'] ? ' · ' . ts_e($study['work_center']) : '' ?></p></div><div class="ts-head-actions"><span class="ts-status <?= ts_e($status) ?>"><?= ts_e(ts_status($status)) ?></span><a class="ts-button ts-button-outline ts-no-print" href="<?= ts_e(ts_url('export', 'study', ['id'=>$study['id']])) ?>"><i class="fa-solid fa-file-csv"></i> CSV</a><button class="ts-button ts-button-outline ts-no-print" type="button" onclick="window.print()"><i class="fa-solid fa-print"></i> چاپ</button><form method="post" action="<?= ts_e(ts_url('study', 'delete', ['id' => $study['id']])) ?>" data-ts-confirm="این مطالعه با همهٔ چرخه‌ها، مشاهدات و برنامه‌های نمونه‌برداری برای همیشه حذف می‌شود و قابل بازگشت نیست. ادامه می‌دهید?" class="ts-no-print"><?= $csrfHtml ?><input type="hidden" name="back" value="<?= $status === 'archived' ? 'archived' : '' ?>"><button class="ts-button ts-button-outline" type="submit"><i class="fa-solid fa-trash"></i> حذف</button></form></div></header>
    <?php if (!empty($flash)): ?><div class="ts-alert <?= ts_e($flash['type']) ?> ts-no-print" role="status"><?= ts_e($flash['message']) ?></div><?php endif; ?>
    <section class="ts-card ts-study-meta">
        <div class="ts-card-head"><div><span class="ts-eyebrow">دامنه و روش</span><h2>تعریف ثبت‌شدهٔ مطالعه</h2></div><?php if ($draft): ?><a class="ts-button ts-button-outline ts-no-print" href="<?= ts_e(ts_url('study', 'edit', ['id'=>$study['id']])) ?>"><i class="fa-solid fa-pen"></i> ویرایش پیش‌نویس</a><?php endif; ?></div>
        <dl class="ts-meta-grid"><div><dt>تاریخ شمسی</dt><dd><?= ts_e(ts_date($study['study_date'])) ?></dd></div><div><dt>واحد / شیفت</dt><dd><?= ts_e($study['unit_name']) ?><?= $study['shift_label'] ? ' · ' . ts_e($study['shift_label']) : '' ?></dd></div><div><dt>واحد سازمانی</dt><dd><?= ts_e($study['department'] ?: '—') ?></dd></div><div><dt>محصول / خدمت</dt><dd><?= ts_e($study['product_name'] ?: '—') ?></dd></div><div><dt>ایستگاه</dt><dd><?= ts_e($study['work_center'] ?: '—') ?></dd></div><div><dt>شناسهٔ مشاهده‌شونده</dt><dd><?= ts_e($study['operator_ref'] ?: '—') ?></dd></div></dl>
        <div class="ts-method-description"><strong>روش فعلی کار</strong><p><?= nl2br(ts_e($study['method_description'])) ?></p><?php if ($study['work_conditions']): ?><strong>شرایط و ارگونومی</strong><p><?= nl2br(ts_e($study['work_conditions'])) ?></p><?php endif; ?><?php if ($study['allowance_rationale']): ?><strong>توجیه allowance</strong><p><?= nl2br(ts_e($study['allowance_rationale'])) ?></p><?php endif; ?></div>
        <?php if ($draft): ?><form method="post" action="<?= ts_e(ts_url('study', 'start', ['id'=>$study['id']])) ?>" class="ts-inline-actions ts-no-print" data-ts-confirm="پس از شروع، روش و عناصر قفل می‌شوند و فقط دادهٔ مشاهده ثبت می‌شود. مطالعه را آغاز می‌کنید?"><?= $csrfHtml ?><button class="ts-button ts-button-primary" type="submit"><i class="fa-solid fa-play"></i> آغاز مطالعه</button></form><?php endif; ?>
    </section>

    <section class="ts-card ts-no-print">
        <div class="ts-card-head"><div><span class="ts-eyebrow">مسیر تکمیل</span><h2>سه قدم تا گزارش نهایی</h2><p><?= $stopwatch ? 'تعریف انجام شده است؛ چرخه‌ها را ثبت کنید، کفایت نمونه را ببینید و سپس نهایی کنید.' : 'زمان‌های تصادفی شیفت را بسازید، نتیجهٔ هر زمان را ثبت کنید و سپس نهایی کنید.' ?></p></div><span class="ts-status <?= ts_e($status) ?>"><?= ts_e(ts_status($status)) ?></span></div>
        <div class="ts-stats ts-result-stats">
            <?php if ($stopwatch): ?>
            <article><span>۱. چرخهٔ ثبت‌شده</span><strong><?= count($study['cycles']) ?></strong><small>هر چرخه شامل همهٔ عناصر است</small></article>
            <article><span>۲. چرخهٔ کامل معتبر / هدف</span><strong><?= (int) $result['complete_valid_cycles'] ?> / <?= $result['required_cycles'] === null ? '—' : (int) $result['required_cycles'] ?></strong><small class="<?= $result['sample_is_sufficient'] ? 'ts-good' : 'ts-warning-text' ?>"><?= $result['sample_is_sufficient'] ? 'کفایت نمونه تأمین شد' : 'نمونه در حال تکمیل است' ?></small></article>
            <article><span>۳. ثبت چرخهٔ بعدی</span><strong><a class="ts-link" href="#cycle-form">رفتن به فرم ثبت</a></strong><small>Enter در جدول به فیلد بعدی می‌رود</small></article>
            <?php else: ?>
            <article><span>۱. زمان برنامه‌ریزی‌شده</span><strong><?= count($slotRows) ?></strong><small><?= $pendingCount ?> زمان هنوز باز است</small></article>
            <article><span>۲. مشاهدهٔ ثبت‌شده</span><strong><?= $observedCount ?></strong><small>از <?= count($slotRows) ?> زمان</small></article>
            <article><span>۳. ثبت نتیجهٔ زمان بعدی</span><strong><a class="ts-link" href="#sampling-queue">رفتن به صف مشاهده</a></strong><small><?= $active ? ($pendingCount === 0 ? 'برای نهایی‌سازی آماده است' : 'همهٔ زمان‌ها باید تعیین تکلیف شوند') : 'ابتدا مطالعه را آغاز کنید' ?></small></article>
            <?php endif; ?>
        </div>
    </section>

    <?php if ($stopwatch): ?>
        <?php $enough = $result['sample_is_sufficient']; ?>
        <section class="ts-stats ts-result-stats">
            <article><span>زمان نرمال چرخه</span><strong><?= ts_num($result['normal_cycle_seconds'], 3) ?> <small>ثانیه</small></strong></article>
            <article><span>زمان استاندارد چرخه</span><strong><?= ts_num($result['standard_cycle_seconds'], 3) ?> <small>ثانیه</small></strong></article>
            <article><span>ظرفیت نظری ساعتی</span><strong><?= ts_num($result['capacity_per_hour'], 2) ?> <small><?= ts_e($study['unit_name']) ?>/ساعت</small></strong></article>
            <article><span>چرخهٔ کامل معتبر / هدف نمونه</span><strong><?= (int) $result['complete_valid_cycles'] ?> / <?= $result['required_cycles'] === null ? '—' : (int) $result['required_cycles'] ?></strong><small class="<?= $enough ? 'ts-good' : 'ts-warning-text' ?>"><?= $enough ? 'به دقت هدف رسیده' : 'نمونه هنوز کفایت ندارد یا پراکندگی قابل برآورد نیست' ?></small></article>
        </section>
        <section class="ts-card">
            <div class="ts-card-head"><div><span class="ts-eyebrow">تحلیل زمان‌سنجی</span><h2>نتیجهٔ عناصر و چرخه</h2></div><span class="ts-status <?= $enough ? 'completed' : 'draft' ?>"><?= $result['confidence_lower'] !== null ? 'بازهٔ تقریبی ' . ts_e($study['confidence_level']) . '٪: ' . ts_num($result['confidence_lower'], 3) . ' تا ' . ts_num($result['confidence_upper'], 3) . ' ثانیه' : 'برای فاصلهٔ اطمینان دست‌کم دو چرخهٔ کامل لازم است' ?></span></div>
            <div class="ts-table-wrap"><table class="ts-table ts-report-table"><thead><tr><th>عنصر</th><th>نوع</th><th>تعداد معتبر</th><th>مشاهده‌شده (s)</th><th>Rating میانگین</th><th>نرمال / بار (s)</th><th>SD (s)</th><th>CV</th></tr></thead><tbody>
            <?php foreach ($result['elements'] as $stats): $e=$stats['element']; ?><tr><td><strong><?= ts_e($e['name']) ?></strong><small><?= ts_e($e['element_code'] ?: ('عنصر ' . $e['sequence_no'])) ?> · × <?= ts_num($e['frequency_per_cycle'],4) ?> در هر واحد</small></td><td><?= $e['element_type'] === 'manual' ? 'دستی' : 'ماشین' ?></td><td><?= (int) $stats['valid_count'] ?> معتبر<?= $stats['excluded_count'] ? ' · ' . (int) $stats['excluded_count'] . ' کنارگذاشته' : '' ?></td><td><?= ts_num($stats['average_observed'],3) ?></td><td><?= $e['element_type'] === 'machine' ? '۱۰۰٪' : ts_num($stats['average_rating'],1) . '٪' ?></td><td><?= ts_num($stats['normal_seconds'],3) ?> / <?= ts_num(($stats['normal_seconds'] ?? 0) * (float)$e['frequency_per_cycle'],3) ?></td><td><?= ts_num($stats['std_dev'],3) ?></td><td><?= $stats['cv_pct'] === null ? '—' : ts_num($stats['cv_pct'],1) . '٪' ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
            <div class="ts-formula-box"><strong>ردیابی محاسبه</strong><p>زمان نرمال چرخه = جمع (میانگینِ زمان مشاهده × rating ÷ ۱۰۰ × تکرار عنصر). زمان دستی با allowance <?= $study['allowance_basis'] === 'standard' ? 'A از زمان استاندارد: NT ÷ (1 − A)' : 'به‌صورت افزوده بر زمان نرمال: NT × (1 + A)' ?> تعدیل می‌شود؛ زمان ماشین جداگانه جمع می‌شود.</p><small>Allowance کل: <?= ts_num($result['allowance_total_pct'],2) ?>٪ · چرخه‌های کنارگذاشته: <?= (int) $result['cycles_excluded'] ?> · چرخه‌های معتبر ناقص: <?= (int) $result['incomplete_valid_cycles'] ?>. کفایت نمونه با تقریب نرمال و دقت نسبی هدف <?= ts_num($study['precision_pct'],1) ?>٪ محاسبه شده؛ نتیجه را با دانش فرایند بازبینی کنید.</small></div>
        </section>
        <?php if ($active): ?>
        <section class="ts-card ts-no-print"><div class="ts-card-head"><div><span class="ts-eyebrow">ثبت مشاهده</span><h2>ثبت چرخهٔ بعدی</h2><p>هر چرخه را یک‌بار کامل ثبت کنید. زمان‌های خارج از روال را با علت کنار بگذارید؛ داده حذف نمی‌شود.</p></div><span class="ts-status active">چرخهٔ بعدی: <?= count($study['cycles']) + 1 ?></span></div>
            <form method="post" id="cycle-form" action="<?= ts_e(ts_url('study', 'addCycle', ['id'=>$study['id']])) ?>" class="ts-form" data-ts-form data-ts-cycle-form>
                <?= $csrfHtml ?><div class="ts-form-grid ts-no-print"><label class="ts-span-2">چسباندن سریع زمان‌ها (به‌ترتیب عناصر)<textarea data-ts-paste rows="2" placeholder="مثلاً 7.2 8.1 6.4 یا هر عدد در یک خط"></textarea></label><div class="ts-form-actions ts-span-2"><button type="button" class="ts-button ts-button-outline" data-ts-paste-apply><i class="fa-solid fa-wand-magic-sparkles"></i> پخش در عناصر</button><button type="button" class="ts-button ts-button-outline" data-ts-copy-previous><i class="fa-solid fa-clone"></i> کپی از چرخهٔ قبل</button></div></div><script type="application/json" data-ts-last-cycle><?= json_encode($lastCycleValues, JSON_UNESCAPED_UNICODE) ?></script><div class="ts-table-wrap" data-ts-enter-nav><table class="ts-table"><thead><tr><th>عنصر کار</th><th>زمان مشاهده‌شده (ثانیه)</th><th>ارزیابی عملکرد (%)</th><th>اعتبار عنصر</th><th>علت کنارگذاری / یادداشت</th></tr></thead><tbody>
                <?php foreach ($study['elements'] as $element): ?><tr><td><strong><?= ts_e($element['name']) ?></strong><small><?= $element['element_type'] === 'manual' ? 'دستی؛ rating قابل ثبت' : 'ماشین؛ rating ثابت ۱۰۰٪' ?></small></td><td><input class="ts-table-input" required type="number" name="observations[<?= (int)$element['id'] ?>][seconds]" min="0.0001" max="36000" step="0.0001" inputmode="decimal"></td><td><?php if ($element['element_type'] === 'manual'): ?><input class="ts-table-input" required type="number" name="observations[<?= (int)$element['id'] ?>][rating]" min="50" max="150" step="0.1" value="100"><?php else: ?><input type="hidden" name="observations[<?= (int)$element['id'] ?>][rating]" value="100"><span class="ts-muted">۱۰۰٪</span><?php endif; ?></td><td><select class="ts-table-input" name="observations[<?= (int)$element['id'] ?>][valid]" data-ts-valid><option value="1">معتبر</option><option value="0">کنارگذاری</option></select></td><td><input class="ts-table-input" name="observations[<?= (int)$element['id'] ?>][reason]" maxlength="500" placeholder="علت / یادداشت"><input type="hidden" name="observations[<?= (int)$element['id'] ?>][note]" value=""></td></tr><?php endforeach; ?>
                </tbody></table></div>
                <div class="ts-form-grid ts-cycle-extra"><label>وضعیت کل چرخه<select name="cycle_status" data-ts-cycle-status><option value="valid">چرخهٔ عادی</option><option value="excluded">چرخهٔ غیرعادی / کنارگذاشته</option></select></label><label data-ts-cycle-reason hidden>علت کنارگذاری چرخه *<input name="cycle_exclusion_reason" maxlength="500"></label><label>مشاهده‌گر<input name="observer_name" maxlength="120" value="<?= ts_e($study['operator_ref'] ?? '') ?>"></label><label class="ts-span-2">یادداشت چرخه<input name="cycle_note" maxlength="1000" placeholder="شرایط غیرعادی، توقف یا تغییر روش"></label></div>
                <div class="ts-form-actions"><button class="ts-button ts-button-primary" type="submit"><i class="fa-solid fa-plus"></i> ثبت چرخه</button></div>
            </form>
        </section>
        <?php endif; ?>
        <section class="ts-card"><div class="ts-card-head"><div><span class="ts-eyebrow">ردپای داده</span><h2>چرخه‌های ثبت‌شده</h2></div><span><?= count($study['cycles']) ?> چرخه</span></div>
            <?php if ($study['cycles']): ?><div class="ts-table-wrap"><table class="ts-table"><thead><tr><th>چرخه</th><th>زمان ثبت</th><th>وضعیت</th><th>مشاهده‌گر</th><th>علت / یادداشت</th></tr></thead><tbody><?php foreach (array_slice($study['cycles'],0,100) as $cycle): ?><tr><td><?= (int)$cycle['cycle_no'] ?></td><td><?= ts_e(ts_date($cycle['observed_at'], 'Y/m/d H:i:s')) ?></td><td><span class="ts-status <?= $cycle['cycle_status'] === 'valid' ? 'completed' : 'archived' ?>"><?= $cycle['cycle_status'] === 'valid' ? 'معتبر' : 'کنارگذاشته' ?></span></td><td><?= ts_e($cycle['observer_name'] ?: '—') ?></td><td><?= ts_e($cycle['exclusion_reason'] ?: $cycle['note'] ?: '—') ?></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><div class="ts-empty compact">پس از آغاز مطالعه، چرخه‌ها اینجا ثبت و نگهداری می‌شوند.</div><?php endif; ?>
        </section>
    <?php else: ?>
        <?php require __DIR__ . '/../sampling/summary.php'; ?>
        <?php require __DIR__ . '/../sampling/planner.php'; ?>
        <?php require __DIR__ . '/../sampling/observations.php'; ?>
    <?php endif; ?>

    <section class="ts-final-actions ts-no-print">
        <?php if ($active): ?><form method="post" action="<?= ts_e(ts_url('study','complete',['id'=>$study['id']])) ?>" data-ts-confirm="پس از نهایی‌سازی، اطلاعات قفل می‌شود. ابتدا کفایت داده و همهٔ زمان‌های باز را بررسی کرده‌اید?"><?= $csrfHtml ?><button class="ts-button ts-button-primary" type="submit"><i class="fa-solid fa-lock"></i> نهایی‌سازی و قفل گزارش</button></form><?php endif; ?>
        <?php if ($status === 'completed'): ?><form method="post" action="<?= ts_e(ts_url('study','archive',['id'=>$study['id']])) ?>" data-ts-confirm="مطالعه به بایگانی منتقل شود? گزارش آن حفظ خواهد شد."><?= $csrfHtml ?><button class="ts-button ts-button-outline" type="submit"><i class="fa-solid fa-box-archive"></i> بایگانی</button></form><?php endif; ?>
    </section>
</section>
