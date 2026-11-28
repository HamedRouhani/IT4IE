<main class="fl-page" dir="rtl">
    <header class="fl-page-head"><div><span class="fl-eyebrow">پروژهٔ چیدمان · <?= fl_e($project['project_code'] ?: 'بدون کد') ?></span><h1><i class="fa-solid fa-industry"></i> <?= fl_e($project['title']) ?></h1><p><?= fl_e($project['facility_name'] ?: 'نام کارخانه ثبت نشده') ?><?= $project['industry'] ? ' · ' . fl_e($project['industry']) : '' ?></p></div><div class="fl-actions"><a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('project', 'edit', ['id' => (int) $project['id']])) ?>"><i class="fa-solid fa-pen"></i> ویرایش مشخصات</a><a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('project', 'index')) ?>">همهٔ پروژه‌ها</a></div></header>
    <?php if (!empty($flash)): ?><div class="fl-alert <?= fl_e($flash['type']) ?>" role="status"><?= fl_e($flash['message']) ?></div><?php endif; ?>

    <?php
    $slpReadinessItems = [
        ['code' => 'P–Q', 'label' => 'محصول و مقدار تولید', 'value' => $slpReadiness['products'] . ' محصول ثبت‌شده', 'ready' => $slpReadiness['products'] > 0],
        ['code' => 'R', 'label' => 'مسیر عملیات', 'value' => $slpReadiness['routedProducts'] . ' از ' . $slpReadiness['products'] . ' محصول دارای مسیر', 'ready' => $slpReadiness['products'] > 0 && $slpReadiness['routedProducts'] === $slpReadiness['products']],
        ['code' => 'S', 'label' => 'خدمات پشتیبان', 'value' => $slpReadiness['supportServices'] . ' خدمت ثبت‌شده', 'ready' => $slpReadiness['supportServices'] > 0],
        ['code' => 'T', 'label' => 'تقویم و زمان‌بندی', 'value' => $periodLabel . ' · ' . (int) $project['shifts_per_day'] . ' شیفت، ' . $project['hours_per_shift'] . ' ساعت', 'ready' => $slpReadiness['calendarConfigured']],
    ];
    ?>
    <section class="fl-card fl-readiness" aria-labelledby="slp-readiness-title">
        <div class="fl-card-head"><div><span class="fl-eyebrow">SLP · کنترل ورودی‌ها</span><h2 id="slp-readiness-title">آمادگی داده‌های پروژه</h2></div><span class="fl-readiness-count"><?= count(array_filter($slpReadinessItems, static fn(array $item): bool => $item['ready'])) ?> از <?= count($slpReadinessItems) ?></span></div>
        <p class="fl-section-intro">این مرور، فقط وضعیت ثبت داده‌ها را نشان می‌دهد؛ کامل‌بودن فهرست خدمات را با مناسب‌بودن آن برای طرح نهایی اشتباه نگیرید.</p>
        <ul class="fl-readiness-list">
            <?php foreach ($slpReadinessItems as $item): ?>
                <li class="<?= $item['ready'] ? 'is-ready' : 'is-pending' ?>"><span class="fl-readiness-code"><?= fl_e($item['code']) ?></span><span class="fl-readiness-copy"><strong><?= fl_e($item['label']) ?></strong><small><?= fl_e($item['value']) ?></small></span><span class="fl-readiness-state"><?= $item['ready'] ? 'ثبت شده' : 'نیازمند بررسی' ?></span></li>
            <?php endforeach; ?>
        </ul>
    </section>

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
            <div class="fl-route-area">
                <div class="fl-route-heading"><div><span class="fl-eyebrow">R · مسیر عملیات</span><h3>توالی فعالیت‌ها برای هر محصول</h3></div><p>ترتیب واقعی حرکت و پردازش را ثبت کنید؛ جابه‌جایی گام‌ها از همین‌جا انجام می‌شود.</p></div>
                <?php $typeLabels = ['operation' => 'عملیات', 'inspection' => 'بازرسی', 'transport' => 'حمل‌ونقل', 'storage' => 'انبارش', 'delay' => 'تأخیر']; ?>
                <?php foreach ($products as $item): $steps = $routeStepsByProduct[(int) $item['id']] ?? []; ?>
                    <section class="fl-route-product">
                        <div class="fl-route-product-head"><h4><?= fl_e($item['name']) ?></h4><a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('route', 'create', ['product_id' => (int) $item['id']])) ?>"><i class="fa-solid fa-plus"></i> افزودن گام مسیر</a></div>
                        <?php if ($steps): ?>
                            <ol class="fl-route-steps">
                                <?php foreach ($steps as $position => $step): ?>
                                    <li>
                                        <span class="fl-route-number"><?= (int) $step['step_no'] ?></span>
                                        <div class="fl-route-copy"><strong><?= fl_e($step['area_name']) ?></strong><span class="fl-route-type"><?= fl_e($typeLabels[$step['activity_type']] ?? 'فعالیت') ?></span>
                                            <?php if (!empty($step['description'])): ?><small><?= fl_e($step['description']) ?></small><?php endif; ?>
                                            <?php if (!empty($step['notes'])): ?><small class="fl-route-notes">یادداشت: <?= fl_e($step['notes']) ?></small><?php endif; ?>
                                        </div>
                                        <div class="fl-row-actions fl-route-actions">
                                            <?php if ($position > 0): ?><form method="post" action="<?= fl_e(fl_url('route', 'move', ['id' => (int) $step['id']])) ?>"><?= $csrfField ?><input type="hidden" name="direction" value="up"><button class="fl-button fl-button-ghost fl-icon-button" type="submit" aria-label="انتقال گام به بالا" title="انتقال به بالا"><i class="fa-solid fa-arrow-up"></i></button></form><?php endif; ?>
                                            <?php if ($position < count($steps) - 1): ?><form method="post" action="<?= fl_e(fl_url('route', 'move', ['id' => (int) $step['id']])) ?>"><?= $csrfField ?><input type="hidden" name="direction" value="down"><button class="fl-button fl-button-ghost fl-icon-button" type="submit" aria-label="انتقال گام به پایین" title="انتقال به پایین"><i class="fa-solid fa-arrow-down"></i></button></form><?php endif; ?>
                                            <a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('route', 'edit', ['id' => (int) $step['id']])) ?>">ویرایش</a>
                                            <form method="post" action="<?= fl_e(fl_url('route', 'delete', ['id' => (int) $step['id']])) ?>" onsubmit="return confirm('این گام از مسیر محصول حذف شود؟');"><?= $csrfField ?><button class="fl-button fl-button-danger" type="submit">حذف</button></form>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                        <?php else: ?>
                            <p class="fl-route-empty">برای این محصول هنوز مسیری ثبت نشده است. گام نخست را از ایستگاه یا فعالیتی که فرایند واقعاً از آن شروع می‌شود وارد کنید.</p>
                        <?php endif; ?>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="fl-empty"><i class="fa-solid fa-box-open"></i><strong>هنوز محصولی ثبت نشده است</strong><span>برای آغاز تحلیل مقدار تولید، محصول یا خانوادهٔ محصول را با واحد مشخص و مقدار دوره‌ای ثبت کنید.</span><a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('product', 'create', ['project_id' => (int) $project['id']])) ?>">ثبت اولین محصول</a></div>
        <?php endif; ?>
    </section>

    <section class="fl-card">
        <div class="fl-card-head">
            <div><span class="fl-eyebrow">S · خدمات و فعالیت‌های پشتیبان</span><h2>نیازهای پشتیبان و برآورد فضا</h2></div>
            <a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('service', 'create', ['project_id' => (int) $project['id']])) ?>"><i class="fa-solid fa-plus"></i> افزودن خدمت</a>
        </div>
        <p class="fl-section-intro">خدمات موردنیاز را از فضای تولیدی جدا ثبت کنید. برای مساحت برآوردی، واحد مترمربع و مبنای محاسبه ذخیره می‌شود؛ مقدار نامعلوم خالی می‌ماند.</p>
        <div class="fl-area-summary" aria-label="خلاصه برآورد فضای خدمات پشتیبان">
            <article><span>جمع مساحت‌های ثبت‌شده</span><strong><?= fl_e($supportAreaSummary['documentedTotalM2']) ?> <small>مترمربع</small></strong><small><?= (int) $supportAreaSummary['knownCount'] ?> خدمت دارای برآورد</small></article>
            <article class="<?= $supportAreaSummary['unknownCount'] > 0 ? 'has-unknown' : '' ?>"><span>نیازمند برآورد فضا</span><strong><?= (int) $supportAreaSummary['unknownCount'] ?></strong><small>خدمت بدون مقدار مساحت</small></article>
        </div>
        <p class="fl-area-caveat">این جمع فقط مجموع اعداد ثبت‌شده برای خدمات پشتیبان است؛ مساحت نهایی کارخانه یا مساحت ناخالص موردنیاز را نشان نمی‌دهد. هم‌پوشانی، مسیرهای دسترسی و الزامات اختصاصی باید جداگانه بررسی شوند.</p>
        <?php if ($supportServices): ?>
            <?php $serviceCategories = ['storage' => 'انبارش و انبار', 'maintenance' => 'تعمیرات و نگهداری', 'quality' => 'کنترل کیفیت و آزمایشگاه', 'utilities' => 'تأسیسات و انرژی', 'office' => 'اداری و سرپرستی', 'welfare' => 'رفاهی و خدمات کارکنان', 'safety' => 'ایمنی و محیط زیست', 'shipping' => 'دریافت و ارسال', 'other' => 'سایر خدمات']; ?>
            <?php $serviceRequirements = ['required' => 'الزامی', 'preferred' => 'ترجیحی', 'optional' => 'اختیاری']; ?>
            <div class="fl-table-wrap"><table class="fl-table fl-service-table">
                <thead><tr><th>خدمت پشتیبان</th><th>دسته</th><th>سطح الزام</th><th>مساحت</th><th>مبنا و ملاحظات</th><th>عملیات</th></tr></thead>
                <tbody><?php foreach ($supportServices as $service): ?>
                    <tr>
                        <td><strong><?= fl_e($service['name']) ?></strong></td>
                        <td><?= fl_e($serviceCategories[$service['category']] ?? 'سایر خدمات') ?></td>
                        <td><span class="fl-requirement <?= fl_e($service['requirement_level']) ?>"><?= fl_e($serviceRequirements[$service['requirement_level']] ?? 'نامشخص') ?></span></td>
                        <td><?= $service['area_m2'] === null ? 'برآورد نشده' : fl_e($service['area_m2']) . ' مترمربع' ?></td>
                        <td><?= fl_e($service['area_basis'] ?: $service['notes'] ?: '—') ?><?php if ($service['area_basis'] && $service['notes']): ?><small><?= fl_e($service['notes']) ?></small><?php endif; ?></td>
                        <td><div class="fl-row-actions">
                            <a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('service', 'edit', ['id' => (int) $service['id']])) ?>">ویرایش</a>
                            <form method="post" action="<?= fl_e(fl_url('service', 'delete', ['id' => (int) $service['id']])) ?>" onsubmit="return confirm('این خدمت پشتیبان از پروژه حذف شود؟');"><?= $csrfField ?><button class="fl-button fl-button-danger" type="submit">حذف</button></form>
                        </div></td>
                    </tr>
                <?php endforeach; ?></tbody>
            </table></div>
        <?php else: ?>
            <div class="fl-empty"><i class="fa-solid fa-screwdriver-wrench"></i><strong>هنوز خدمت پشتیبانی ثبت نشده است</strong><span>انبار، تعمیرات، کنترل کیفیت، تأسیسات و نیازهای مشابه را با سطح الزام مشخص کنید.</span><a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('service', 'create', ['project_id' => (int) $project['id']])) ?>">ثبت اولین خدمت</a></div>
        <?php endif; ?>
    </section>

    <section class="fl-card fl-relationship-section">
        <?php $relationshipLabels = ['A' => 'ضرورت مطلق', 'E' => 'اهمیت بسیار زیاد', 'I' => 'اهمیت زیاد', 'O' => 'اهمیت معمولی', 'U' => 'اهمیت ناچیز', 'X' => 'نامطلوب؛ نیازمند جدایی']; ?>
        <div class="fl-card-head"><div><span class="fl-eyebrow">REL · روابط غیرمادی</span><h2>ثبت روابط فعالیت‌ها (REL)</h2></div><span class="fl-readiness-count"><?= count($activityRelationships) ?> رابطه</span></div>
        <p class="fl-section-intro">برای هر زوج، کد نزدیکی و دلیل قابل‌بررسی ثبت کنید. کدها امتیاز عددی نیستند و زوج‌های ثبت‌نشده هنوز ارزیابی نشده‌اند (معادل U نیستند). رابطهٔ X را با دلیل روشن و ملاحظهٔ ایمنی مستند کنید.</p>
        <?php if (count($activityAreas) >= 2): ?>
            <form class="fl-relationship-form" method="post" action="<?= fl_e(fl_url('relationship', 'store', ['project_id' => (int) $project['id']])) ?>">
                <?= $csrfField ?>
                <label>فعالیت اول<select name="area_a" required><option value="">انتخاب کنید</option><?php foreach ($activityAreas as $activityArea): ?><option value="<?= fl_e($activityArea) ?>"><?= fl_e($activityArea) ?></option><?php endforeach; ?></select></label>
                <label>فعالیت دوم<select name="area_b" required><option value="">انتخاب کنید</option><?php foreach ($activityAreas as $activityArea): ?><option value="<?= fl_e($activityArea) ?>"><?= fl_e($activityArea) ?></option><?php endforeach; ?></select></label>
                <label>کد نزدیکی<select name="closeness_code" required><?php foreach ($relationshipLabels as $code => $label): ?><option value="<?= fl_e($code) ?>"><?= fl_e($code . ' · ' . $label) ?></option><?php endforeach; ?></select></label>
                <label class="fl-relationship-reason">دلیل رابطه *<input name="reason" maxlength="500" required placeholder="مثلاً تکرار انتقال مواد یا محدودیت ایمنی"></label>
                <button class="fl-button fl-button-primary" type="submit"><i class="fa-solid fa-plus"></i> ثبت رابطه</button>
            </form>
        <?php else: ?>
            <div class="fl-empty"><strong>برای ساخت نمودار، دست‌کم دو فعالیت لازم است</strong><span>فعالیت‌ها از مسیر محصولات و فهرست خدمات پشتیبان این پروژه گردآوری می‌شوند.</span></div>
        <?php endif; ?>
        <?php if ($activityRelationships): ?>
            <div class="fl-table-wrap"><table class="fl-table fl-relationship-table">
                <thead><tr><th>فعالیت اول</th><th>کد</th><th>فعالیت دوم</th><th>دلیل ثبت‌شده</th><th>عملیات</th></tr></thead>
                <tbody><?php foreach ($activityRelationships as $relationship): ?>
                    <tr><td><?= fl_e($relationship['area_a']) ?></td><td><span class="fl-rel-code rel-<?= fl_e($relationship['closeness_code']) ?>"><?= fl_e($relationship['closeness_code']) ?> · <?= fl_e($relationshipLabels[$relationship['closeness_code']] ?? 'نامشخص') ?></span></td><td><?= fl_e($relationship['area_b']) ?></td><td><?= fl_e($relationship['reason']) ?></td>
                        <td><form method="post" action="<?= fl_e(fl_url('relationship', 'delete', ['id' => (int) $relationship['id']])) ?>" onsubmit="return confirm('این رابطه حذف شود؟');"><?= $csrfField ?><button class="fl-button fl-button-danger" type="submit">حذف</button></form></td></tr>
                <?php endforeach; ?></tbody>
            </table></div>
        <?php else: ?>
            <p class="fl-relationship-empty">هنوز رابطه‌ای ثبت نشده است. هر زوج فعالیت فقط یک‌بار وارد نمودار می‌شود.</p>
        <?php endif; ?>
        <p class="fl-area-caveat">فعالیت‌ها در این نسخه از نام ایستگاه‌های مسیر و نام خدمات پشتیبان ساخته می‌شوند. رابطه‌ها نام فعالیت را هنگام ثبت نگه می‌دارند؛ اگر نام ایستگاهی را تغییر دادید، رابطه‌های مرتبط را بازبینی کنید.</p>
    </section>
    <aside class="fl-note"><i class="fa-solid fa-graduation-cap"></i><div><strong>نکتهٔ آموزشی SLP</strong><p>P محصول یا خانوادهٔ محصول و Q مقدار تولید است. واحد هر مقدار باید روشن باشد؛ برای تبدیل واحد یا مقایسهٔ محصولات ناهمگون، ضرایب و قواعد تبدیل را در مرحلهٔ جریان مواد تعریف می‌کنیم.</p><a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('learn', 'show', ['lesson' => 'slp-overview'])) ?>">مرور درس مبانی SLP</a></div></aside>
</main>
