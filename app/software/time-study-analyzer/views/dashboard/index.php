<section class="ts-page" dir="rtl">
    <header class="ts-hero">
        <div><span class="ts-eyebrow">نسخهٔ پایدار 1.0.0 · سامانهٔ مطالعهٔ کار</span><h1><i class="fa-solid fa-stopwatch"></i> Time Study Analyzer</h1><p>روش استاندارد را مستند کنید، زمان را با ردپای داده بسنجید و نتیجه را با فرض‌های شفاف گزارش کنید.</p></div>
        <a class="ts-button ts-button-primary" href="<?= ts_e(ts_url('study', 'index')) ?>"><i class="fa-solid fa-folder-open"></i> مطالعات</a>
    </header>

    <section class="ts-stats" aria-label="خلاصه مطالعات">
        <article><span>کل مطالعات</span><strong><?= (int) ($stats['total'] ?? 0) ?></strong></article>
        <article><span>پیش‌نویس</span><strong><?= (int) ($stats['drafts'] ?? 0) ?></strong></article>
        <article><span>در حال اجرا</span><strong><?= (int) ($stats['active'] ?? 0) ?></strong></article>
        <article><span>نهایی‌شده</span><strong><?= (int) ($stats['completed'] ?? 0) ?></strong></article>
    </section>

    <section class="ts-card">
        <div class="ts-card-head"><div><span class="ts-eyebrow">انتخاب روش</span><h2>ایجاد مطالعهٔ جدید</h2></div></div>
        <div class="ts-method-grid">
            <a class="ts-method-card" href="<?= ts_e(ts_url('study', 'create', ['method' => 'stopwatch'])) ?>"><span class="ts-method-icon"><i class="fa-solid fa-stopwatch"></i></span><strong>زمان‌سنجی مستقیم</strong><p>ثبت عناصر کار در چرخه‌های متوالی، ارزیابی عملکرد و محاسبهٔ زمان نرمال و استاندارد.</p><span>تعریف عناصر، مشاهده‌های تکراری، کنترل کفایت نمونه <i class="fa-solid fa-arrow-left"></i></span></a>
            <a class="ts-method-card" href="<?= ts_e(ts_url('study', 'create', ['method' => 'work_sampling'])) ?>"><span class="ts-method-icon"><i class="fa-solid fa-chart-pie"></i></span><strong>نمونه‌برداری کار</strong><p>برنامهٔ تصادفی مشاهده، ثبت وضعیت کار در لحظه و برآورد سهم فعالیت‌ها با فاصلهٔ اطمینان.</p><span>برنامهٔ شیفت، دسته‌بندی فعالیت و کنترل نمونه <i class="fa-solid fa-arrow-left"></i></span></a>
        </div>
    </section>

    <section class="ts-card">
        <div class="ts-card-head"><div><span class="ts-eyebrow">ادامهٔ کار</span><h2>آخرین مطالعات</h2></div><a class="ts-link" href="<?= ts_e(ts_url('study', 'index')) ?>">همهٔ مطالعات <i class="fa-solid fa-arrow-left"></i></a></div>
        <?php if (!empty($stats['recent'])): ?><div class="ts-table-wrap"><table class="ts-table"><thead><tr><th>کد / عنوان</th><th>روش</th><th>فرایند</th><th>تاریخ مطالعه</th><th>وضعیت</th><th></th></tr></thead><tbody>
        <?php foreach ($stats['recent'] as $row): ?><tr><td><strong><?= ts_e($row['study_no']) ?></strong><small><?= ts_e($row['title']) ?></small></td><td><?= $row['method'] === 'stopwatch' ? 'زمان‌سنجی مستقیم' : 'نمونه‌برداری کار' ?></td><td><?= ts_e($row['process_name']) ?></td><td><?= ts_e(ts_date($row['study_date'])) ?></td><td><span class="ts-status <?= ts_e($row['status']) ?>"><?= ts_e(ts_status($row['status'])) ?></span></td><td><a class="ts-button ts-button-outline" href="<?= ts_e(ts_url('study', 'show', ['id' => $row['id']])) ?>">بازکردن</a></td></tr><?php endforeach; ?>
        </tbody></table></div><?php else: ?><div class="ts-empty"><i class="fa-solid fa-clipboard-list"></i><strong>هنوز مطالعه‌ای ثبت نشده است.</strong><span>یکی از دو روش را انتخاب کنید تا با تعریف روش و محدودهٔ مطالعه شروع شود.</span></div><?php endif; ?>
    </section>

    <aside class="ts-note"><i class="fa-solid fa-shield-heart"></i><div><strong>اندازه‌گیری همراه با توجه به انسان</strong><p>این ابزار زمان و روش کار را برای بهبود فرایند ثبت می‌کند. دادهٔ زمان به‌تنهایی معیار ارزیابی فرد نیست؛ شرایط محیطی، ارگونومی، ایمنی و مشارکت کارکنان را در تفسیر نتایج لحاظ کنید.</p></div></aside>
</section>
