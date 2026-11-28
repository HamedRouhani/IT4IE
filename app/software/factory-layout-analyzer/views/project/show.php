<main class="fl-page" dir="rtl">
    <header class="fl-page-head"><div><span class="fl-eyebrow">پروژهٔ چیدمان · <?= fl_e($project['project_code'] ?: 'بدون کد') ?></span><h1><i class="fa-solid fa-industry"></i> <?= fl_e($project['title']) ?></h1><p><?= fl_e($project['facility_name'] ?: 'نام کارخانه ثبت نشده') ?><?= $project['industry'] ? ' · ' . fl_e($project['industry']) : '' ?></p></div><div class="fl-actions"><a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('project', 'edit', ['id' => (int) $project['id']])) ?>"><i class="fa-solid fa-pen"></i> ویرایش مشخصات</a><a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('project', 'index')) ?>">همهٔ پروژه‌ها</a></div></header>
    <?php if (!empty($flash)): ?><div class="fl-alert <?= fl_e($flash['type']) ?>" role="status"><?= fl_e($flash['message']) ?></div><?php endif; ?>

    <?php
    $workflowLabels = [
        'overview' => ['title' => 'نمای کلی', 'description' => 'وضعیت و اطلاعات پروژه'],
        'products' => ['title' => 'محصولات و مقدار تولید', 'description' => 'گام P–Q'],
        'routes' => ['title' => 'مسیر عملیات', 'description' => 'گام R'],
        'flows' => ['title' => 'جریان مواد', 'description' => 'ماتریس From-To'],
        'services' => ['title' => 'خدمات پشتیبان', 'description' => 'گام S'],
        'relationships' => ['title' => 'روابط فعالیت‌ها', 'description' => 'نمودار و ماتریس REL'],
        'space' => ['title' => 'نیاز فضای تولید', 'description' => 'برآورد فضای بخش‌ها'],
    ];
    $workflowDone = [
        'overview' => true,
        'products' => count($products) > 0,
        'routes' => count($products) > 0 && $slpReadiness['routedProducts'] === count($products),
        'flows' => count($materialFlows) > 0,
        'services' => count($supportServices) > 0,
        'relationships' => count($activityRelationships) > 0,
        'space' => count($productionAreas) > 0 && $slpReadiness['documentedProductionAreas'] === count($productionAreas),
    ];
    $activePosition = array_search($activeStep, $workflowSteps, true);
    $previousStep = $activePosition > 0 ? $workflowSteps[$activePosition - 1] : null;
    $nextStep = $activePosition < count($workflowSteps) - 1 ? $workflowSteps[$activePosition + 1] : null;
    $recommendedStep = null;
    foreach (array_slice($workflowSteps, 1) as $candidateStep) {
        if (!$workflowDone[$candidateStep]) {
            $recommendedStep = $candidateStep;
            break;
        }
    }
    $workflowGuidance = [
        'products' => 'محصول یا خانوادهٔ محصول را همراه مقدار تولید، واحد و دورهٔ برنامه‌ریزی وارد کنید.',
        'routes' => 'برای هر محصول، ایستگاه‌ها و فعالیت‌ها را به ترتیب واقعی فرایند ثبت کنید.',
        'services' => 'خدمات پشتیبان موردنیاز کارخانه را ثبت کنید و برای برآورد فضا، مبنا را مستند کنید.',
        'flows' => 'پس از تعریف فعالیت‌ها، جابه‌جایی‌های جهت‌دار بین مبدأ و مقصد را با دوره و منبع داده ثبت کنید.',
        'relationships' => 'برای زوج فعالیت‌ها کد نزدیکی و دلیل قابل بررسی ثبت کنید؛ زوج‌های ارزیابی‌نشده را با U اشتباه نگیرید.',
        'space' => 'برای هر بخش تولیدی، مساحت موردنیاز و اجزای منظورشده در برآورد را همراه با مبنا ثبت کنید.',
    ];
    ?>
    <nav class="fl-workflow" aria-label="مراحل طراحی چیدمان">
        <div class="fl-workflow-heading"><div><span class="fl-eyebrow">فرایند طراحی چیدمان</span><h2><?= fl_e($workflowLabels[$activeStep]['title']) ?></h2></div><span class="fl-workflow-position">مرحلهٔ <?= (int) $activePosition + 1 ?> از <?= count($workflowSteps) ?></span></div>
        <ol class="fl-workflow-steps">
            <?php foreach ($workflowSteps as $index => $stepKey): ?>
                <li class="<?= $activeStep === $stepKey ? 'is-active' : '' ?> <?= $workflowDone[$stepKey] ? 'is-done' : '' ?>">
                    <a href="<?= fl_e(fl_url('project', 'show', ['id' => (int) $project['id'], 'step' => $stepKey])) ?>" <?= $activeStep === $stepKey ? 'aria-current="step"' : '' ?>>
                        <span class="fl-workflow-number"><?= $workflowDone[$stepKey] && $stepKey !== 'overview' ? '<i class="fa-solid fa-check"></i>' : (int) $index + 1 ?></span>
                        <span class="fl-workflow-copy"><strong><?= fl_e($workflowLabels[$stepKey]['title']) ?></strong><small><?= fl_e($workflowLabels[$stepKey]['description']) ?></small></span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ol>
    </nav>

    <?php if ($activeStep === 'overview'): ?>
    <section class="fl-next-action" aria-labelledby="fl-next-action-title">
        <?php if ($recommendedStep !== null): ?>
            <div class="fl-next-action-copy"><span class="fl-eyebrow">پیشنهاد برای ادامه · <?= fl_e($workflowLabels[$recommendedStep]['title']) ?></span><h2 id="fl-next-action-title"><?= fl_e($workflowGuidance[$recommendedStep]) ?></h2></div>
            <a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('project', 'show', ['id' => (int) $project['id'], 'step' => $recommendedStep])) ?>">رفتن به این گام <i class="fa-solid fa-arrow-left"></i></a>
        <?php else: ?>
            <div class="fl-next-action-copy"><span class="fl-eyebrow">ورودی اولیهٔ همهٔ گام‌ها ثبت شده</span><h2 id="fl-next-action-title">چک‌لیست آمادگی را مرور کنید و داده‌ها را با منابع واقعی پروژه تطبیق دهید.</h2></div>
            <span class="fl-workflow-position"><?= count($workflowSteps) - 1 ?> گام دارای داده</span>
        <?php endif; ?>
    </section>
    <?php
    $slpReadinessItems = [
        ['code' => 'P–Q', 'label' => 'محصول و مقدار تولید', 'value' => $slpReadiness['products'] . ' محصول ثبت‌شده', 'ready' => $slpReadiness['products'] > 0],
        ['code' => 'R', 'label' => 'مسیر عملیات', 'value' => $slpReadiness['routedProducts'] . ' از ' . $slpReadiness['products'] . ' محصول دارای مسیر', 'ready' => $slpReadiness['products'] > 0 && $slpReadiness['routedProducts'] === $slpReadiness['products']],
        ['code' => 'From-To', 'label' => 'جریان‌های جهت‌دار مواد', 'value' => $slpReadiness['flows'] . ' مسیر دارای داده', 'ready' => $slpReadiness['flows'] > 0],
        ['code' => 'S', 'label' => 'خدمات پشتیبان', 'value' => $slpReadiness['supportServices'] . ' خدمت ثبت‌شده', 'ready' => $slpReadiness['supportServices'] > 0],
        ['code' => 'A', 'label' => 'نیاز فضای تولیدی', 'value' => $slpReadiness['documentedProductionAreas'] . ' از ' . $slpReadiness['productionAreas'] . ' بخش دارای برآورد', 'ready' => $slpReadiness['productionAreas'] > 0 && $slpReadiness['documentedProductionAreas'] === $slpReadiness['productionAreas']],
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

    <?php $projectStatus = (string) ($project['status'] ?? 'draft'); ?>
    <section class="fl-card"><div class="fl-card-head"><div><span class="fl-eyebrow">مبنای مطالعه</span><h2>اطلاعات ثبت‌شده</h2></div><span class="fl-status status-<?= fl_e($projectStatus) ?>"><?= fl_e($statuses[$projectStatus] ?? 'نامشخص') ?></span></div>
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
    <?php endif; ?>

    <?php if (in_array($activeStep, ['products', 'routes'], true)): ?>
    <section class="fl-card">
        <div class="fl-card-head">
            <div><span class="fl-eyebrow"><?= $activeStep === 'products' ? 'P–Q · محصول و مقدار تولید' : 'R · توالی عملیات' ?></span><h2><?= $activeStep === 'products' ? 'محصولات پروژه' : 'مسیر عملیات محصولات' ?></h2></div>
            <?php if ($activeStep === 'products'): ?><a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('product', 'create', ['project_id' => (int) $project['id']])) ?>"><i class="fa-solid fa-plus"></i> افزودن محصول</a><?php endif; ?>
        </div>
        <p class="fl-section-intro"><?= $activeStep === 'products' ? 'هر ردیف یک محصول یا خانواده و مقدار تولید آن در دورهٔ ' . fl_e($periodLabel) . ' است. مقادیر با واحدهای متفاوت با هم جمع نمی‌شوند.' : 'برای هر محصول، ایستگاه‌ها و فعالیت‌ها را به ترتیب واقعی انجام فرایند ثبت کنید.' ?></p>
        <?php if ($products): ?>
            <?php if ($activeStep === 'products'): ?>
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
            <?php endif; ?>
            <?php if ($activeStep === 'routes'): ?>
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
            <?php endif; ?>
        <?php else: ?>
            <div class="fl-empty"><i class="fa-solid fa-box-open"></i><strong>ابتدا محصول تعریف کنید</strong><span>ثبت مسیر عملیات پس از تعریف دست‌کم یک محصول یا خانوادهٔ محصول ممکن است.</span><a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('product', 'create', ['project_id' => (int) $project['id']])) ?>">ثبت اولین محصول</a></div>
        <?php endif; ?>
    </section>
    <?php endif; ?>

    <?php if ($activeStep === 'services'): ?>
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
    <?php endif; ?>

    <?php if ($activeStep === 'flows'): ?>
    <section class="fl-card fl-flow-section">
        <div class="fl-card-head"><div><span class="fl-eyebrow">SLP · جریان مواد</span><h2>ثبت جریان و ماتریس From-To</h2></div><span class="fl-readiness-count"><?= count($materialFlows) ?> مسیر ثبت‌شده</span></div>
        <p class="fl-section-intro">برای هر مسیر جهت‌دار، تعداد جابه‌جایی بار، دورهٔ مشاهده و مبنای داده را ثبت کنید. ردیف A به B با B به A متفاوت است و خانهٔ ثبت‌نشده به معنی صفر حرکت نیست.</p>
        <aside class="fl-inline-note"><i class="fa-solid fa-circle-info"></i><span>واحد این نسخه «تعداد جابه‌جایی بار» است، نه وزن یا حجم مواد. صفر را فقط وقتی ثبت کنید که مسیر را در دورهٔ مشخص بررسی کرده‌اید و واقعاً حرکتی رخ نداده است؛ خانهٔ خالی یعنی هنوز داده‌ای ثبت نشده. برای مقایسهٔ معتبر، تعریف یک بار را بین مسیرها یکسان کنید.</span></aside>
        <?php if (count($activityAreas) >= 2): ?>
            <form class="fl-flow-form" method="post" action="<?= fl_e(fl_url('flow', 'store', ['project_id' => (int) $project['id']])) ?>">
                <?= $csrfField ?>
                <label>مبدأ<select name="from_area" required><option value="">انتخاب فعالیت</option><?php foreach ($activityAreas as $activityArea): ?><option value="<?= fl_e($activityArea) ?>"><?= fl_e($activityArea) ?></option><?php endforeach; ?></select></label>
                <label>مقصد<select name="to_area" required><option value="">انتخاب فعالیت</option><?php foreach ($activityAreas as $activityArea): ?><option value="<?= fl_e($activityArea) ?>"><?= fl_e($activityArea) ?></option><?php endforeach; ?></select></label>
                <label>تعداد جابه‌جایی بار (صفر مجاز است) *<input name="load_moves" type="text" inputmode="decimal" pattern="[0-9۰-۹٠-٩]+([٫.][0-9۰-۹٠-٩]{1,4})?" maxlength="15" required placeholder="مثلاً ۱۲۵ یا ۰"></label>
                <label>دورهٔ مشاهده *<select name="flow_period" required><?php foreach ($flowPeriods as $key => $label): ?><option value="<?= fl_e($key) ?>" <?= $key === $project['planning_period'] ? 'selected' : '' ?>><?= fl_e($label) ?></option><?php endforeach; ?></select></label>
                <label class="fl-flow-evidence">منبع یا مبنای داده *<input name="evidence" maxlength="500" required placeholder="مثلاً شمارش برگه‌های انتقال در هفتهٔ کاری نمونه"></label>
                <button class="fl-button fl-button-primary" type="submit"><i class="fa-solid fa-plus"></i> ثبت جریان</button>
            </form>
        <?php else: ?>
            <div class="fl-empty"><strong>برای ثبت جریان، دست‌کم دو فعالیت لازم است</strong><span>ابتدا مسیر عملیات محصولات یا خدمات پشتیبان را تعریف کنید.</span></div>
        <?php endif; ?>

        <?php if (count($activityAreas) >= 2): ?>
            <?php $flowByPair = []; foreach ($materialFlows as $flow) { $flowByPair[$flow['from_area']][$flow['to_area']] = $flow; } ?>
            <div class="fl-flow-matrix-block"><h3>ماتریس جهت‌دار جابه‌جایی‌ها</h3><div class="fl-table-wrap"><table class="fl-table fl-flow-matrix">
                <thead><tr><th scope="col">مبدأ ↓ / مقصد →</th><?php foreach ($activityAreas as $activityArea): ?><th scope="col" title="<?= fl_e($activityArea) ?>"><?= fl_e($activityArea) ?></th><?php endforeach; ?></tr></thead>
                <tbody><?php foreach ($activityAreas as $rowIndex => $fromArea): ?><tr><th scope="row"><?= fl_e($fromArea) ?></th><?php foreach ($activityAreas as $columnIndex => $toArea): ?>
                    <?php if ($rowIndex === $columnIndex): ?><td class="fl-flow-diagonal" aria-label="مبدأ و مقصد یکسان">—</td>
                    <?php elseif (isset($flowByPair[$fromArea][$toArea])): $matrixFlow = $flowByPair[$fromArea][$toArea]; $observedZero = (float) $matrixFlow['load_moves'] === 0.0; ?><td class="<?= $observedZero ? 'fl-flow-zero' : '' ?>" title="<?= fl_e($matrixFlow['evidence']) ?>"><strong><?= fl_e($matrixFlow['load_moves']) ?></strong><small><?= $observedZero ? 'صفرِ مشاهده‌شده · ' : '' ?><?= fl_e($flowPeriods[$matrixFlow['flow_period']] ?? 'دوره نامشخص') ?></small></td>
                    <?php else: ?><td class="fl-flow-unrecorded" title="جریانی ثبت نشده" aria-label="جریانی ثبت نشده">·</td><?php endif; ?>
                <?php endforeach; ?></tr><?php endforeach; ?></tbody>
            </table></div></div>
        <?php endif; ?>

        <?php if ($materialFlows): ?>
            <div class="fl-table-wrap"><table class="fl-table fl-flow-table"><thead><tr><th>مبدأ</th><th>مقصد</th><th>بار در دوره</th><th>دوره</th><th>منبع یا مبنا</th><th>عملیات</th></tr></thead><tbody>
                <?php foreach ($materialFlows as $flow): $flowDialogId = 'fl-flow-edit-' . (int) $flow['id']; ?>
                    <tr><td><?= fl_e($flow['from_area']) ?></td><td><?= fl_e($flow['to_area']) ?></td><td><?= fl_e($flow['load_moves']) ?></td><td><?= fl_e($flowPeriods[$flow['flow_period']] ?? 'نامشخص') ?></td><td><?= fl_e($flow['evidence']) ?></td>
                        <td><div class="fl-row-actions"><button class="fl-button fl-button-ghost" type="button" aria-haspopup="dialog" aria-controls="<?= fl_e($flowDialogId) ?>" onclick="document.getElementById('<?= fl_e($flowDialogId) ?>').showModal()">ویرایش</button><form method="post" action="<?= fl_e(fl_url('flow', 'delete', ['id' => (int) $flow['id']])) ?>" onsubmit="return confirm('این جریان از پروژه حذف شود؟');"><?= $csrfField ?><button class="fl-button fl-button-danger" type="submit">حذف</button></form></div></td></tr>
                <?php endforeach; ?>
            </tbody></table></div>
            <?php foreach ($materialFlows as $flow): $flowDialogId = 'fl-flow-edit-' . (int) $flow['id']; ?>
                <dialog class="fl-flow-dialog" id="<?= fl_e($flowDialogId) ?>" aria-labelledby="<?= fl_e($flowDialogId) ?>-title"><div class="fl-flow-dialog-card">
                    <div class="fl-flow-dialog-head"><div><span class="fl-eyebrow">ویرایش جریان جهت‌دار</span><h3 id="<?= fl_e($flowDialogId) ?>-title"><?= fl_e($flow['from_area']) ?> → <?= fl_e($flow['to_area']) ?></h3></div><button class="fl-button fl-button-ghost" type="button" aria-label="بستن پنجره" onclick="this.closest('dialog').close()">×</button></div>
                    <form method="post" action="<?= fl_e(fl_url('flow', 'update', ['id' => (int) $flow['id']])) ?>"><?= $csrfField ?>
                        <label>تعداد جابه‌جایی بار (صفر مجاز است) *<input name="load_moves" type="text" inputmode="decimal" pattern="[0-9۰-۹٠-٩]+([٫.][0-9۰-۹٠-٩]{1,4})?" maxlength="15" required value="<?= fl_e($flow['load_moves']) ?>"></label>
                        <label>دورهٔ مشاهده *<select name="flow_period" required><?php foreach ($flowPeriods as $key => $label): ?><option value="<?= fl_e($key) ?>" <?= $flow['flow_period'] === $key ? 'selected' : '' ?>><?= fl_e($label) ?></option><?php endforeach; ?></select></label>
                        <label>منبع یا مبنای داده *<input name="evidence" maxlength="500" required value="<?= fl_e($flow['evidence']) ?>"></label>
                        <div class="fl-flow-dialog-actions"><button class="fl-button fl-button-primary" type="submit">ذخیرهٔ تغییرات</button><button class="fl-button fl-button-ghost" type="button" onclick="this.closest('dialog').close()">انصراف</button></div>
                    </form>
                </div></dialog>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="fl-relationship-empty">هنوز جریان جهت‌داری ثبت نشده است. ماتریس خالی، به‌تنهایی به معنی نبود جابه‌جایی نیست.</p>
        <?php endif; ?>
        <p class="fl-area-caveat">اگر نام فعالیتی در مسیر محصول یا خدمات تغییر کرد، جریان‌های ثبت‌شده را بازبینی کنید؛ مبدأ و مقصد در زمان ثبت به‌صورت نام نگهداری می‌شوند.</p>
    </section>
    <?php endif; ?>

    <?php if ($activeStep === 'relationships'): ?>
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
                    <?php $relationshipDialogId = 'fl-rel-edit-' . (int) $relationship['id']; ?>
                    <tr><td><?= fl_e($relationship['area_a']) ?></td><td><span class="fl-rel-code rel-<?= fl_e($relationship['closeness_code']) ?>"><?= fl_e($relationship['closeness_code']) ?> · <?= fl_e($relationshipLabels[$relationship['closeness_code']] ?? 'نامشخص') ?></span></td><td><?= fl_e($relationship['area_b']) ?></td><td><?= fl_e($relationship['reason']) ?></td>
                        <td><div class="fl-row-actions"><button class="fl-button fl-button-ghost" type="button" aria-haspopup="dialog" aria-controls="<?= fl_e($relationshipDialogId) ?>" onclick="document.getElementById('<?= fl_e($relationshipDialogId) ?>').showModal()">ویرایش</button><form method="post" action="<?= fl_e(fl_url('relationship', 'delete', ['id' => (int) $relationship['id']])) ?>" onsubmit="return confirm('این رابطه حذف شود؟');"><?= $csrfField ?><button class="fl-button fl-button-danger" type="submit">حذف</button></form></div></td></tr>
                <?php endforeach; ?></tbody>
            </table></div>
            <?php foreach ($activityRelationships as $relationship): ?>
                <?php $relationshipDialogId = 'fl-rel-edit-' . (int) $relationship['id']; ?>
                <dialog class="fl-rel-dialog" id="<?= fl_e($relationshipDialogId) ?>" aria-labelledby="<?= fl_e($relationshipDialogId) ?>-title">
                    <div class="fl-rel-dialog-card">
                        <div class="fl-rel-dialog-head"><div><span class="fl-eyebrow">ویرایش رابطهٔ فعالیت</span><h3 id="<?= fl_e($relationshipDialogId) ?>-title"><?= fl_e($relationship['area_a']) ?> · <?= fl_e($relationship['area_b']) ?></h3></div><button class="fl-button fl-button-ghost" type="button" aria-label="بستن پنجره" onclick="this.closest('dialog').close()">×</button></div>
                        <form method="post" action="<?= fl_e(fl_url('relationship', 'update', ['id' => (int) $relationship['id']])) ?>">
                            <?= $csrfField ?>
                            <label>کد نزدیکی<select name="closeness_code" required><?php foreach ($relationshipLabels as $code => $label): ?><option value="<?= fl_e($code) ?>" <?= $relationship['closeness_code'] === $code ? 'selected' : '' ?>><?= fl_e($code . ' · ' . $label) ?></option><?php endforeach; ?></select></label>
                            <label>دلیل رابطه<input name="reason" maxlength="500" required value="<?= fl_e($relationship['reason']) ?>"></label>
                            <div class="fl-rel-dialog-actions"><button class="fl-button fl-button-primary" type="submit">ذخیرهٔ تغییرات</button><button class="fl-button fl-button-ghost" type="button" onclick="this.closest('dialog').close()">انصراف</button></div>
                        </form>
                    </div>
                </dialog>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="fl-relationship-empty">هنوز رابطه‌ای ثبت نشده است. هر زوج فعالیت فقط یک‌بار وارد نمودار می‌شود.</p>
        <?php endif; ?>
        <?php if (count($activityAreas) >= 2): ?>
            <?php
            $relationshipByPair = [];
            foreach ($activityRelationships as $relationship) {
                $relationshipByPair[$relationship['area_a']][$relationship['area_b']] = $relationship;
                $relationshipByPair[$relationship['area_b']][$relationship['area_a']] = $relationship;
            }
            ?>
            <div class="fl-rel-matrix-block">
                <h3>ماتریس روابط فعالیت‌ها</h3>
                <p class="fl-relationship-empty">ماتریس متقارن است؛ خانهٔ «—» روی قطر، رابطهٔ یک فعالیت با خودش را نشان می‌دهد. نقطهٔ خاکستری یعنی این زوج هنوز ارزیابی نشده است.</p>
                <div class="fl-table-wrap"><table class="fl-table fl-rel-matrix"><thead><tr><th scope="col">فعالیت</th><?php foreach ($activityAreas as $activityArea): ?><th scope="col" title="<?= fl_e($activityArea) ?>"><?= fl_e($activityArea) ?></th><?php endforeach; ?></tr></thead>
                    <tbody><?php foreach ($activityAreas as $rowIndex => $rowArea): ?><tr><th scope="row"><?= fl_e($rowArea) ?></th><?php foreach ($activityAreas as $columnIndex => $columnArea): ?>
                        <?php if ($rowIndex === $columnIndex): ?><td class="fl-rel-diagonal" aria-label="قطر اصلی">—</td>
                        <?php elseif (isset($relationshipByPair[$rowArea][$columnArea])): $matrixRelationship = $relationshipByPair[$rowArea][$columnArea]; ?><td><span class="fl-rel-code rel-<?= fl_e($matrixRelationship['closeness_code']) ?>" title="<?= fl_e($matrixRelationship['reason']) ?>"><?= fl_e($matrixRelationship['closeness_code']) ?></span></td>
                        <?php else: ?><td class="fl-rel-unrated" title="ارزیابی‌نشده" aria-label="ارزیابی‌نشده">·</td><?php endif; ?>
                    <?php endforeach; ?></tr><?php endforeach; ?></tbody>
                </table></div>
                <div class="fl-rel-legend" aria-label="راهنمای کدهای رابطه"><?php foreach ($relationshipLabels as $code => $label): ?><span><b class="fl-rel-code rel-<?= fl_e($code) ?>"><?= fl_e($code) ?></b><?= fl_e($label) ?></span><?php endforeach; ?></div>
            </div>
        <?php endif; ?>
        <p class="fl-area-caveat">فعالیت‌ها در این نسخه از نام ایستگاه‌های مسیر و نام خدمات پشتیبان ساخته می‌شوند. رابطه‌ها نام فعالیت را هنگام ثبت نگه می‌دارند؛ اگر نام ایستگاهی را تغییر دادید، رابطه‌های مرتبط را بازبینی کنید.</p>
    </section>
    <?php endif; ?>
    <?php if ($activeStep === 'space'): ?>
    <section class="fl-card fl-space-section">
        <div class="fl-card-head"><div><span class="fl-eyebrow">SLP · نیاز فضا</span><h2>برآورد فضای بخش‌های تولیدی</h2></div><span class="fl-readiness-count"><?= (int) $productionSpaceSummary['documentedCount'] ?> از <?= count($productionAreas) ?> بخش برآورد شده</span></div>
        <p class="fl-section-intro">برای هر ناحیهٔ تولیدی که در مسیر محصول تعریف شده، مساحت موردنیاز و مبنای برآورد را ثبت کنید. اگر چند محصول از یک بخش مشترک استفاده می‌کنند، نام ایستگاه را در همهٔ مسیرها یکسان بنویسید تا بخش یک‌بار شمرده شود. در مبنا مشخص کنید چه مواردی مثل تجهیزات، فضای کار اپراتور، ذخیرهٔ بین‌فرایندی یا دسترسی تعمیرات را لحاظ کرده‌اید.</p>
        <div class="fl-area-summary" aria-label="خلاصهٔ نیاز فضای تولید">
            <article><span>جمع مساحت‌های ثبت‌شدهٔ تولید</span><strong><?= fl_e($productionSpaceSummary['totalM2']) ?> <small>مترمربع</small></strong><small><?= (int) $productionSpaceSummary['documentedCount'] ?> بخش برآوردشده</small></article>
            <article class="<?= $productionSpaceSummary['missingCount'] > 0 ? 'has-unknown' : '' ?>"><span>بخش‌های نیازمند برآورد</span><strong><?= (int) $productionSpaceSummary['missingCount'] ?></strong><small>از <?= count($productionAreas) ?> بخش تولیدی</small></article>
        </div>
        <p class="fl-area-caveat">این جمع، برآورد اولیهٔ مساحت تولیدی است؛ مساحت خدمات پشتیبان در گام جداگانهٔ خودش می‌ماند. راهروهای اصلی، ستون‌ها، هم‌پوشانی‌ها و الزامات ایمنی را جداگانه بررسی کنید و این جمع را مساحت نهایی ساختمان تلقی نکنید.</p>
        <?php if ($productionAreas): ?>
            <div class="fl-table-wrap"><table class="fl-table fl-production-space-table">
                <thead><tr><th>بخش تولیدی</th><th>مساحت موردنیاز</th><th>مبنای ثبت‌شده</th><th>ورود یا ویرایش</th><th>عملیات</th></tr></thead>
                <tbody><?php foreach ($productionAreas as $areaName): $spaceRequirement = $productionSpacesByArea[$areaName] ?? null; ?>
                    <tr>
                        <td><strong><?= fl_e($areaName) ?></strong></td>
                        <td><?= $spaceRequirement ? fl_e($spaceRequirement['area_m2']) . ' مترمربع' : '<span class="fl-space-missing">برآورد نشده</span>' ?></td>
                        <td><?= $spaceRequirement ? fl_e($spaceRequirement['area_basis']) : '—' ?><?php if ($spaceRequirement && !empty($spaceRequirement['notes'])): ?><small><?= fl_e($spaceRequirement['notes']) ?></small><?php endif; ?></td>
                        <td><details class="fl-space-editor"><summary><?= $spaceRequirement ? 'ویرایش برآورد' : 'ثبت برآورد' ?></summary>
                            <form method="post" action="<?= fl_e(fl_url('space', 'store', ['project_id' => (int) $project['id']])) ?>">
                                <?= $csrfField ?><input type="hidden" name="area_name" value="<?= fl_e($areaName) ?>">
                                <label>مساحت موردنیاز (مترمربع) *<input name="area_m2" type="text" inputmode="decimal" pattern="[0-9۰-۹٠-٩]+([٫.][0-9۰-۹٠-٩]{1,3})?" maxlength="14" required value="<?= fl_e($spaceRequirement['area_m2'] ?? '') ?>" placeholder="مثلاً ۴۸٫۵"></label>
                                <label>مبنای برآورد *<input name="area_basis" maxlength="500" required value="<?= fl_e($spaceRequirement['area_basis'] ?? '') ?>" placeholder="مثلاً جانمایی ماشین‌آلات به‌همراه دسترسی اپراتور"></label>
                                <label>یادداشت تکمیلی<input name="notes" maxlength="1000" value="<?= fl_e($spaceRequirement['notes'] ?? '') ?>" placeholder="فرض‌ها یا محدودیت‌های برآورد"></label>
                                <button class="fl-button fl-button-primary" type="submit">ذخیرهٔ برآورد</button>
                            </form>
                        </details></td>
                        <td><?php if ($spaceRequirement): ?><form method="post" action="<?= fl_e(fl_url('space', 'delete', ['id' => (int) $spaceRequirement['id']])) ?>" onsubmit="return confirm('برآورد فضای این بخش حذف شود؟');"><?= $csrfField ?><button class="fl-button fl-button-danger" type="submit">حذف برآورد</button></form><?php else: ?>—<?php endif; ?></td>
                    </tr>
                <?php endforeach; ?></tbody>
            </table></div>
        <?php else: ?>
            <div class="fl-empty"><i class="fa-solid fa-ruler-combined"></i><strong>ابتدا مسیر یک محصول را ثبت کنید</strong><span>فهرست بخش‌های تولیدی از نام ایستگاه‌های ثبت‌شده در مسیر محصولات ساخته می‌شود.</span><a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('project', 'show', ['id' => (int) $project['id'], 'step' => 'routes'])) ?>">رفتن به مسیر عملیات</a></div>
        <?php endif; ?>
        <?php if ($orphanedProductionSpaces): ?>
            <aside class="fl-inline-note"><i class="fa-solid fa-triangle-exclamation"></i><span>برای <?= count($orphanedProductionSpaces) ?> برآورد، دیگر مسیر فعالی با همان نام وجود ندارد. این برآوردها در جمع بالا منظور نشده‌اند؛ اگر نام بخش تغییر کرده، پس از بازبینی آن‌ها را حذف کنید و برای نام جدید برآورد تازه‌ای ثبت کنید.</span></aside>
            <div class="fl-table-wrap"><table class="fl-table"><thead><tr><th>نام قبلی بخش</th><th>مساحت</th><th>مبنا</th><th>عملیات</th></tr></thead><tbody>
                <?php foreach ($orphanedProductionSpaces as $orphanedSpace): ?><tr><td><?= fl_e($orphanedSpace['area_name']) ?></td><td><?= fl_e($orphanedSpace['area_m2']) ?> مترمربع</td><td><?= fl_e($orphanedSpace['area_basis']) ?></td><td><form method="post" action="<?= fl_e(fl_url('space', 'delete', ['id' => (int) $orphanedSpace['id']])) ?>" onsubmit="return confirm('برآورد قدیمی حذف شود؟');"><?= $csrfField ?><button class="fl-button fl-button-danger" type="submit">حذف</button></form></td></tr><?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>
    <?php endif; ?>
    <?php if ($activeStep === 'overview'): ?>
    <aside class="fl-note"><i class="fa-solid fa-graduation-cap"></i><div><strong>نکتهٔ آموزشی SLP</strong><p>P محصول یا خانوادهٔ محصول و Q مقدار تولید است. واحد هر مقدار باید روشن باشد؛ برای تبدیل واحد یا مقایسهٔ محصولات ناهمگون، ضرایب و قواعد تبدیل را در مرحلهٔ جریان مواد تعریف می‌کنیم.</p><a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('learn', 'show', ['lesson' => 'slp-overview'])) ?>">مرور درس مبانی SLP</a></div></aside>
    <?php endif; ?>
    <?php if ($activeStep !== 'overview'): ?>
    <nav class="fl-workflow-controls" aria-label="ادامهٔ مراحل طراحی چیدمان">
        <?php if ($previousStep !== null): ?><a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('project', 'show', ['id' => (int) $project['id'], 'step' => $previousStep])) ?>"><i class="fa-solid fa-arrow-right"></i> گام قبل: <?= fl_e($workflowLabels[$previousStep]['title']) ?></a><?php else: ?><span></span><?php endif; ?>
        <?php if ($nextStep !== null): ?><a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('project', 'show', ['id' => (int) $project['id'], 'step' => $nextStep])) ?>">گام بعد: <?= fl_e($workflowLabels[$nextStep]['title']) ?> <i class="fa-solid fa-arrow-left"></i></a><?php else: ?><a class="fl-button fl-button-primary" href="<?= fl_e(fl_url('project', 'show', ['id' => (int) $project['id'], 'step' => 'overview'])) ?>">بازگشت به خلاصهٔ پروژه <i class="fa-solid fa-arrow-left"></i></a><?php endif; ?>
    </nav>
    <?php endif; ?>
</main>
