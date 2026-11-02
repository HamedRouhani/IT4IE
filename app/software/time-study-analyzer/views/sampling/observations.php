<?php
$samplingSlots = $study['slots'];
usort($samplingSlots, static function ($a, $b) {
    $pendingA = $a['slot_status'] === 'planned' ? 0 : 1;
    $pendingB = $b['slot_status'] === 'planned' ? 0 : 1;
    return ($pendingA <=> $pendingB) ?: strcmp($a['scheduled_at'], $b['scheduled_at']);
});
$pendingSamplingSlots = array_values(array_filter($samplingSlots, static fn($slot) => $slot['slot_status'] === 'planned'));
?>
<section class="ts-card ts-no-print">
    <div class="ts-card-head"><div><span class="ts-eyebrow">اجرای نمونه‌برداری</span><h2>صف زمان‌های مشاهده</h2><p>در هر زمانِ برنامه‌ریزی‌شده، وضعیت واقعی کار را همان لحظه ثبت کنید. برای زمان ازدست‌رفته یا باطل، علت روشن بنویسید.</p></div><span class="ts-status <?= $pendingSamplingSlots ? 'active' : 'completed' ?>"><?= count($pendingSamplingSlots) ?> زمان باز</span></div>
    <?php if ($samplingSlots): ?>
    <div class="ts-sampling-queue">
        <?php foreach ($samplingSlots as $slot): ?>
        <article class="ts-sampling-item <?= $slot['slot_status'] === 'planned' ? 'is-pending' : 'is-resolved' ?>">
            <div class="ts-sampling-time"><span>زمان برنامه‌ریزی‌شده</span><strong><?= ts_e(ts_date($slot['scheduled_at'], 'Y/m/d')) ?></strong><b><?= ts_e(ts_date($slot['scheduled_at'], 'H:i')) ?></b></div>
            <div class="ts-sampling-result"><span>نتیجه</span>
                <?php if ($slot['slot_status'] === 'planned'): ?><span class="ts-status active">در انتظار</span>
                <?php elseif ($slot['slot_status'] === 'observed'): ?><span class="ts-status completed"><?= ts_e($slot['category_name'] ?: 'دسته نامشخص') ?></span>
                <?php else: ?><span class="ts-status archived"><?= $slot['slot_status'] === 'missed' ? 'مشاهده ازدست‌رفته' : 'باطل‌شده' ?></span><?php endif; ?>
            </div>
            <?php if ($slot['slot_status'] === 'planned' && $active): ?>
            <form method="post" action="<?= ts_e(ts_url('study', 'recordSample', ['id' => $study['id'], 'slot' => $slot['id']])) ?>" class="ts-sampling-entry" data-ts-sample-form>
                <?= $csrfHtml ?>
                <label>وضعیت ثبت<select name="slot_status" data-ts-slot-status><option value="observed">مشاهده شد</option><option value="missed">مشاهده انجام نشد</option><option value="void">زمان نامعتبر / باطل</option></select></label>
                <label>دستهٔ کار در همان لحظه<select name="category_id" data-ts-category required><option value="">انتخاب دسته</option><?php foreach ($study['categories'] as $category): ?><option value="<?= (int) $category['id'] ?>"><?= ts_e($category['name']) ?> · <?= ts_e(ts_category_type($category['category_type'])) ?></option><?php endforeach; ?></select></label>
                <label>مشاهده‌گر<input name="observer_name" maxlength="120" value="<?= ts_e($study['operator_ref'] ?? '') ?>" placeholder="نام یا کد مشاهده‌گر"></label>
                <label class="ts-sampling-note">یادداشت / علت<input name="note" maxlength="1000" data-ts-note placeholder="شرایط خاص مشاهده؛ در صورت عدم مشاهده یا ابطال، علت الزامی است"></label>
                <button class="ts-button ts-button-primary" type="submit"><i class="fa-solid fa-check"></i> ثبت نتیجه</button>
            </form>
            <?php else: ?><div class="ts-sampling-audit"><span>زمان ثبت نتیجه</span><strong><?= ts_e(ts_date($slot['actual_at'], 'Y/m/d H:i')) ?></strong><span>مشاهده‌گر</span><strong><?= ts_e($slot['observer_name'] ?: '—') ?></strong><?php if ($slot['note']): ?><p><?= nl2br(ts_e($slot['note'])) ?></p><?php endif; ?></div><?php endif; ?>
        </article>
        <?php endforeach; ?>
    </div>
    <?php else: ?><div class="ts-empty compact">هنوز برنامه‌ای ساخته نشده است. ابتدا زمان‌های تصادفی شیفت را تولید کنید.</div><?php endif; ?>
</section>
