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

    <section class="fl-card">
        <div class="fl-card-head">
            <div><span class="fl-eyebrow">P · محصولات و خانواده‌ها</span><h2>مقدار تولید در دورهٔ <?= fl_e($periodLabel) ?></h2></div>
            <a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('product', 'create', ['project_id' => (int) $project['id']])) ?>"><i class="fa-solid fa-plus"></i> افزودن محصول</a>
        </div>
        <p class="fl-section-intro">هر ردیف یک محصول یا خانواده و مقدار تولید آن در دورهٔ برنامه‌ریزی پروژه است. مقادیر با واحدهای متفاوت با هم جمع نمی‌شوند.</p>
        <?php if ($products): ?>
            <div class="fl-table-wrap"><table class="fl-table fl-product-table">
                <thead><tr><th>کد</th><th>محصول / خانواده</th><th>مقدار در هر <?= fl_e($periodLabel) ?></th><th>یادداشت</th><th>عملیات</th></tr></thead>
                <tbody><?php foreach ($products as $item): ?>
                    <tr>
                        <td><?= fl_e($item['product_code'] ?: '—') ?></td>
                        <td><strong><?= fl_e($item['name']) ?></strong></td>
                        <td><strong><?= fl_e($item['production_quantity']) ?></strong> <?= fl_e($item['production_unit']) ?></td>
                        <td><?= fl_e($item['notes'] ?: '—') ?></td>
                        <td><div class="fl-row-actions">
                            <a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('product', 'edit', ['id' => (int) $item['id']])) ?>">ویرایش</a>
                            <form method="post" action="<?= fl_e(fl_url('product', 'delete', ['id' => (int) $item['id']])) ?>" onsubmit="return confirm('این محصول از پروژه حذف شود؟');">
                                <?= $csrfField ?><button class="fl-button fl-button-danger" type="submit">حذف</button>
                            </form>
                        </div></td>
                    </tr>
                <?php endforeach; ?></tbody>
            </table></div>
        <?php else: ?>
            <div class="fl-empty"><i class="fa-solid fa-box-open"></i><strong>هنوز محصولی ثبت نشده است</strong><span>برای آغاز تحلیل مقدار تولید، محصول یا خانوادهٔ محصول را با واحد مشخص و مقدار دوره‌ای ثبت کنید.</span><a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('product', 'create', ['project_id' => (int) $project['id']])) ?>">ثبت اولین محصول</a></div>
        <?php endif; ?>
    </section>

    <aside class="fl-note"><i class="fa-solid fa-graduation-cap"></i><div><strong>نکتهٔ آموزشی SLP</strong><p>P محصول یا خانوادهٔ محصول و Q مقدار تولید است. واحد هر مقدار باید روشن باشد؛ برای تبدیل واحد یا مقایسهٔ محصولات ناهمگون، ضرایب و قواعد تبدیل را در مرحلهٔ جریان مواد تعریف می‌کنیم.</p><a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('learn', 'show', ['lesson' => 'slp-overview'])) ?>">مرور درس مبانی SLP</a></div></aside>
</main>
