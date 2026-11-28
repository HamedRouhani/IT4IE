<main class="fl-page" dir="rtl">
    <header class="fl-page-head"><div><span class="fl-eyebrow">پروژهٔ چیدمان · <?= fl_e($project['project_code'] ?: 'بدون کد') ?></span><h1><i class="fa-solid fa-industry"></i> <?= fl_e($project['title']) ?></h1><p><?= fl_e($project['facility_name'] ?: 'نام کارخانه ثبت نشده') ?><?= $project['industry'] ? ' · ' . fl_e($project['industry']) : '' ?></p></div><div class="fl-actions"><a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('project', 'edit', ['id' => (int) $project['id']])) ?>"><i class="fa-solid fa-pen"></i> ویرایش مشخصات</a><a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('project', 'index')) ?>">همهٔ پروژه‌ها</a></div></header>
    <?php if (!empty($flash)): ?><div class="fl-alert <?= fl_e($flash['type']) ?>" role="status"><?= fl_e($flash['message']) ?></div><?php endif; ?>

    <section class="fl-card"><div class="fl-card-head"><div><span class="fl-eyebrow">مبنای مطالعه</span><h2>اطلاعات ثبت‌شده</h2></div><span class="fl-status">پیش‌نویس</span></div>
        <dl class="fl-details">
            <div><dt>کارخانه یا سایت</dt><dd><?= fl_e($project['facility_name'] ?: 'ثبت نشده') ?></dd></div>
            <div><dt>صنعت</dt><dd><?= fl_e($project['industry'] ?: 'ثبت نشده') ?></dd></div>
            <div><dt>موقعیت</dt><dd><?= fl_e($project['site_location'] ?: 'ثبت نشده') ?></dd></div>
            <div><dt>دورهٔ ثبت مقدار تولید</dt><dd><?= fl_e($periodLabel) ?></dd></div>
            <div><dt>برنامهٔ شیفت</dt><dd><?= (int) $project['shifts_per_day'] ?> شیفت × <?= fl_e($project['hours_per_shift']) ?> ساعت</dd></div>
            <div><dt>روز کاری</dt><dd><?= (int) $project['working_days_per_week'] ?> روز در هفته</dd></div>
            <div class="fl-detail-wide"><dt>دامنه و هدف</dt><dd><?= nl2br(fl_e($project['scope_description'] ?: 'هنوز توضیحی ثبت نشده است.')) ?></dd></div>
        </dl>
    </section>

    <aside class="fl-note"><i class="fa-solid fa-arrow-left"></i><div><strong>گام بعدی این پروژه</strong><p>ثبت خانواده‌های محصول و مقدار تولید هرکدام در دورهٔ «<?= fl_e($periodLabel) ?>»، سپس تعریف فعالیت‌های عملیاتی و خدمات پشتیبان و مسیر ساخت هر محصول. این داده‌ها هستهٔ P، Q، R و S در SLP هستند.</p><a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('learn', 'show', ['lesson' => 'slp-overview'])) ?>">مرور درس مبانی SLP</a></div></aside>
</main>
