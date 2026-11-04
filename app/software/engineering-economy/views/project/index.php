<section class="ee-page" dir="rtl">
    <header class="ee-page-head">
        <div>
            <span class="ee-eyebrow">تحلیل و توجیه سرمایه‌گذاری</span>
            <h1><i class="fa-solid fa-coins"></i> اقتصاد مهندسی</h1>
            <p>پروژه بسازید، جریان نقدی را ثبت کنید و با NPV، IRR، ERR و دورهٔ بازگشت تصمیم بگیرید.</p>
        </div>
        <div class="ee-head-actions">
            <a class="ee-button ee-button-primary" href="<?= ee_e(ee_url('project', 'create')) ?>"><i class="fa-solid fa-plus"></i> پروژهٔ جدید</a>
            <a class="ee-button" href="<?= ee_e(ee_url('learn', 'index')) ?>"><i class="fa-solid fa-graduation-cap"></i> مرکز آموزش</a>
            <?php if ($showArchived): ?>
                <a class="ee-button" href="<?= ee_e(ee_url('project', 'index')) ?>">پروژه‌های جاری</a>
            <?php else: ?>
                <a class="ee-button" href="<?= ee_e(ee_url('project', 'index', ['archived' => 1])) ?>">بایگانی</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="ee-alert <?= ee_e($flash['type']) ?>" role="status"><?= ee_e($flash['message']) ?></div>
    <?php endif; ?>

    <section class="ee-kpis" aria-label="خلاصهٔ پروژه‌ها">
        <article class="ee-kpi"><span>کل پروژه‌ها</span><strong><?= (int) $stats['total'] ?></strong><small>شامل بایگانی</small></article>
        <article class="ee-kpi is-accent"><span>در جریان</span><strong><?= (int) $stats['active'] ?></strong><small>آمادهٔ تحلیل</small></article>
        <article class="ee-kpi"><span>پیش‌نویس</span><strong><?= (int) $stats['draft'] ?></strong><small>در حال تکمیل</small></article>
        <article class="ee-kpi"><span>نهایی / بایگانی</span><strong><?= (int) $stats['completed'] ?> / <?= (int) $stats['archived'] ?></strong><small>قفل‌شده برای گزارش</small></article>
    </section>

    <section class="ee-card">
        <div class="ee-card-head">
            <div><span class="ee-eyebrow">فهرست</span><h2>پروژه‌های اقتصادی</h2></div>
            <span class="ee-tag"><?= count($projects) ?> مورد</span>
        </div>

        <?php if ($projects): ?>
            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead>
                        <tr>
                            <th>کد / عنوان</th>
                            <th>نوع مطالعه</th>
                            <th>دوره</th>
                            <th>MARR</th>
                            <th>گزینه‌ها</th>
                            <th>وضعیت</th>
                            <th>آخرین تغییر</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $row): ?>
                            <tr>
                                <td>
                                    <strong><?= ee_e($row['project_no'] ?: '—') ?></strong>
                                    <small><?= ee_e($row['title']) ?></small>
                                </td>
                                <td><span class="ee-tag"><?= ee_e(ee_kind_label((string) $row['study_kind'])) ?></span></td>
                                <td class="ee-num"><?= (int) $row['study_period'] ?> سال</td>
                                <td class="ee-num"><?= ee_num($row['marr_pct'], 2) ?>٪</td>
                                <td class="ee-num"><?= (int) $row['alternative_count'] ?></td>
                                <td><span class="ee-status <?= ee_e($row['status']) ?>"><?= ee_e(ee_status_label((string) $row['status'])) ?></span></td>
                                <td class="ee-num"><?= ee_e(ee_date($row['updated_at'] ?: $row['created_at'])) ?></td>
                                <td>
                                    <div class="ee-head-actions">
                                        <a class="ee-button" href="<?= ee_e(ee_url('project', 'show', ['id' => (int) $row['id']])) ?>">گزارش</a>
                                        <form method="post" action="<?= ee_e(ee_url('project', 'delete', ['id' => (int) $row['id']])) ?>" onsubmit="return confirm('این پروژه با همهٔ داده‌های وابسته حذف می‌شود و قابل بازگشت نیست. ادامه می‌دهید؟');">
                                            <?= csrf_field() ?>
                                            <button class="ee-button is-danger" type="submit" aria-label="حذف پروژه"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="ee-empty">
                <i class="fa-solid fa-folder-open"></i>
                <strong>هنوز پروژه‌ای تعریف نشده است</strong>
                <span>با «پروژهٔ جدید» شروع کنید و نرخ بازدهٔ حداقلی، دورهٔ مطالعه و روش استهلاک را همان ابتدا ثبت کنید.</span>
            </div>
        <?php endif; ?>
    </section>
</section>
