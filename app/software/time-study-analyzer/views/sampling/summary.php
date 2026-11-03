<?php
/** @var array $study */
/** @var array $result */
?>
<section class="ts-stats ts-result-stats" aria-label="خلاصه نمونه‌برداری">
    <article><span>مشاهدات طبقه‌بندی‌شده</span><strong><?= (int) $result['observed_count'] ?></strong></article>
    <article><span>حجم نمونهٔ هدف (حالت محافظه‌کارانه)</span><strong><?= (int) $result['required_sample_size'] ?></strong><small>با سطح اطمینان <?= ts_e($study['confidence_level']) ?>٪ و دقت مطلق <?= ts_num($study['precision_pct'], 1) ?> واحد درصد</small></article>
    <article><span>سهم فعالیت مولد</span><strong><?= $result['productive_share'] === null ? '—' : ts_num(100 * $result['productive_share'], 1) . '٪' ?></strong><small>دقت واقعی: <?= $result['achieved_precision_pct'] === null ? '—' : ts_num($result['achieved_precision_pct'], 1) . ' واحد درصد' ?></small></article>
    <article><span>تعیین تکلیف برنامه</span><strong><?= (int) $result['resolved_count'] ?> / <?= (int) $result['planned_count'] ?></strong><small><?= (int) $result['missed_count'] ?> ازدست‌رفته · <?= (int) $result['void_count'] ?> باطل</small></article>
</section>

<section class="ts-card">
    <div class="ts-card-head"><div><span class="ts-eyebrow">برآورد نسبت فعالیت‌ها</span><h2>نتایج نمونه‌برداری</h2></div><span class="ts-status <?= $result['sample_is_sufficient'] ? 'completed' : 'draft' ?>"><?= $result['sample_is_sufficient'] ? 'به حجم نمونهٔ هدف رسیده' : 'نمونهٔ ناکافی' ?></span></div>
    <div class="ts-table-wrap"><table class="ts-table"><thead><tr><th>دستهٔ فعالیت</th><th>طبقه</th><th>تعداد</th><th>سهم از مشاهدات معتبر</th><th>بازهٔ اطمینان <?= ts_e($study['confidence_level']) ?>٪ (Wilson)</th><th>دقت به‌دست‌آمده</th><th>کفایت</th><th>سهم</th></tr></thead><tbody>
    <?php foreach ($result['categories'] as $row): ?><tr><td><strong><?= ts_e($row['category']['name']) ?></strong></td><td><?= ts_e(ts_category_type($row['category']['category_type'])) ?></td><td><?= (int) $row['count'] ?></td><td><?= $row['share'] === null ? '—' : ts_num(100 * $row['share'], 1) . '٪' ?></td><td><?= $row['lower'] === null ? '—' : ts_num(100 * $row['lower'], 1) . '٪ تا ' . ts_num(100 * $row['upper'], 1) . '٪' ?></td><td><?= $row['achieved_precision_pct'] === null ? '—' : ts_num($row['achieved_precision_pct'], 1) . ' واحد درصد' ?></td><td><?= $row['is_sufficient'] === null ? '—' : ($row['is_sufficient'] ? 'کافی' : 'نیاز به ' . max(0, (int) $row['required_count'] - (int) $row['count']) . ' مشاهده') ?></td><td><div class="ts-bar" role="img" aria-label="<?= $row['share'] === null ? 'بدون داده' : ts_num(100 * $row['share'], 1) . ' درصد' ?>"><span style="width:<?= $row['share'] === null ? 0 : min(100, max(0, 100 * $row['share'])) ?>%"></span></div></td></tr><?php endforeach; ?>
    </tbody></table></div>
    <?php if (!empty($result['warnings'])): ?><div class="ts-note ts-note-compact ts-no-print"><i class="fa-solid fa-triangle-exclamation"></i><div><strong>بازبینی کیفیت داده</strong><ul><?php foreach ($result['warnings'] as $warning): ?><li><?= ts_e($warning) ?></li><?php endforeach; ?></ul></div></div><?php endif; ?>
    <div class="ts-formula-box"><strong>روش محاسبه و تفسیر</strong><p>نسبت هر دسته برابر تعداد مشاهده‌های آن دسته تقسیم بر کل مشاهده‌های معتبر است. مشاهده‌های ازدست‌رفته و باطل در مخرج نسبت‌ها وارد نمی‌شوند؛ تعداد و نرخ آن‌ها جداگانه پایش شود.</p><small>هدف نمونه با فرمول محافظه‌کارانهٔ n = z² × 0.25 / e² و دقت مطلق e محاسبه شده است. بازهٔ نسبت‌ها با روش Wilson گزارش می‌شود. برنامهٔ تصادفی فقط وقتی مبنای استنباط است که طبق زمان‌های ازپیش‌تعیین‌شده اجرا و مشاهده‌ها تا حد امکان مستقل باشند.</small></div>
</section>
