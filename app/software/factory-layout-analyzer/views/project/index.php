<main class="fl-page" dir="rtl">
    <header class="fl-page-head">
        <div><span class="fl-eyebrow">فضای کاری شخصی</span><h1><i class="fa-solid fa-folder-open"></i> پروژه‌های چیدمان من</h1><p>برای هر کارخانه یا مسئلهٔ چیدمان، پروژه‌ای جدا بسازید و دورهٔ تحلیل و تقویم کاری را ثبت کنید.</p></div>
        <div class="fl-actions"><a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('project', 'create')) ?>"><i class="fa-solid fa-plus"></i> پروژهٔ جدید</a><a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('learn', 'index')) ?>">مرکز آموزش</a></div>
    </header>

    <?php if (!empty($flash)): ?><div class="fl-alert <?= fl_e($flash['type']) ?>" role="status"><?= fl_e($flash['message']) ?></div><?php endif; ?>

    <section class="fl-stats" aria-label="آمار پروژه‌ها">
        <article><span>کل پروژه‌ها</span><strong><?= (int) $stats['total'] ?></strong><small>شامل پروژه‌های پیش‌نویس</small></article>
        <article><span>نیازمند تکمیل</span><strong><?= (int) $stats['draft'] ?></strong><small>در انتظار داده‌های P-Q-R-S</small></article>
        <article><span>مرحلهٔ فعلی</span><strong>تقویم T</strong><small>پایهٔ زمانی ثبت حجم تولید</small></article>
    </section>

    <section class="fl-card">
        <div class="fl-card-head"><div><span class="fl-eyebrow">فهرست</span><h2>پروژه‌های ذخیره‌شده</h2></div><span class="fl-tag"><?= count($projects) ?> پروژه</span></div>
        <?php if ($projects): ?>
            <div class="fl-table-wrap"><table class="fl-table"><thead><tr><th>کد و عنوان</th><th>کارخانه / صنعت</th><th>دورهٔ تولید</th><th>تقویم کاری</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><strong><?= fl_e($project['project_code'] ?: '—') ?></strong><small><?= fl_e($project['title']) ?></small></td>
                        <td><?= fl_e($project['facility_name'] ?: '—') ?><small><?= fl_e($project['industry'] ?: 'صنعت ثبت نشده') ?></small></td>
                        <td><?= fl_e(['day'=>'روزانه','week'=>'هفتگی','month'=>'ماهانه','year'=>'سالانه'][$project['planning_period']] ?? '—') ?></td>
                        <td><?= (int) $project['shifts_per_day'] ?> شیفت × <?= fl_e($project['hours_per_shift']) ?> ساعت<small><?= (int) $project['working_days_per_week'] ?> روز کاری در هفته</small></td>
                        <?php $projectStatus = (string) ($project['status'] ?? 'draft'); ?><td><span class="fl-status status-<?= fl_e($projectStatus) ?>"><?= fl_e($statuses[$projectStatus] ?? 'نامشخص') ?></span></td>
                        <td><div class="fl-row-actions"><a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('project', 'show', ['id' => (int) $project['id']])) ?>">مشاهده</a><a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('project', 'edit', ['id' => (int) $project['id']])) ?>" aria-label="ویرایش پروژه">ویرایش</a><form method="post" action="<?= fl_e(fl_url('project', 'delete', ['id' => (int) $project['id']])) ?>" onsubmit="return confirm('این پروژه حذف می‌شود. ادامه می‌دهید؟');"><?= $csrfField ?><button class="fl-button fl-button-danger" type="submit" aria-label="حذف پروژه">حذف</button></form></div></td>
                    </tr>
                <?php endforeach; ?>
            </tbody></table></div>
        <?php else: ?>
            <div class="fl-empty"><i class="fa-solid fa-industry"></i><strong>هنوز پروژه‌ای نساخته‌اید</strong><span>ابتدا مشخصات کارخانه و تقویم کاری را ثبت کنید؛ داده‌های محصول و مسیر در گام بعدی تکمیل می‌شوند.</span><a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('project', 'create')) ?>">ساخت اولین پروژه</a></div>
        <?php endif; ?>
    </section>
</main>
