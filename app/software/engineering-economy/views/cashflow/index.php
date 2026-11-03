<section class="ee-page" dir="rtl">
    <header class="ee-page-head">
        <div>
            <span class="ee-eyebrow"><?= ee_e($project['project_no'] ?: 'پروژه') ?> · <?= ee_e(ee_kind_label((string) $project['study_kind'])) ?></span>
            <h1><i class="fa-solid fa-table-columns"></i> گزینه‌ها و جریان نقدی</h1>
            <p><?= ee_e($project['title']) ?> — برای هر گزینه یک جریان نقدی سال‌به‌سال ثبت کنید. سرمایه‌گذاری اولیه فقط در سال ۰.</p>
        </div>
        <div class="ee-head-actions">
            <a class="ee-button" href="<?= ee_e(ee_url('project', 'show', ['id' => (int) $project['id']])) ?>"><i class="fa-solid fa-arrow-right"></i> گزارش پروژه</a>
            <a class="ee-button ee-button-accent" href="<?= ee_e(ee_url('analysis', 'index', ['id' => (int) $project['id']])) ?>"><i class="fa-solid fa-chart-line"></i> تحلیل</a>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="ee-alert <?= ee_e($flash['type']) ?>" role="status"><?= ee_e($flash['message']) ?></div>
    <?php endif; ?>

    <section class="ee-card">
        <div class="ee-card-head">
            <div><span class="ee-eyebrow">گزینه‌ها</span><h2>فهرست گزینه‌های مطالعه</h2><p>در مقایسه، هر گزینه یک جریان نقدی مستقل دارد؛ حداقل دو گزینه برای مقایسه لازم است.</p></div>
            <span class="ee-tag"><?= count($alternatives) ?> گزینه</span>
        </div>

        <?php if ($alternatives): ?>
            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead>
                        <tr><th>گزینه</th><th>نقش</th><th>عمر (سال)</th><th>سال‌های ثبت‌شده</th><th></th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alternatives as $alternative): ?>
                            <tr>
                                <td><strong><?= ee_e($alternative['name']) ?></strong><?php if ($alternative['description']): ?><small><?= ee_e($alternative['description']) ?></small><?php endif; ?></td>
                                <td><?= $alternative['is_baseline'] ? '<span class="ee-tag">گزینهٔ پایه</span>' : '<span class="ee-tag">جایگزین</span>' ?></td>
                                <td class="ee-num"><?= (int) $alternative['life_years'] ?></td>
                                <td class="ee-num"><?= (int) $alternative['flow_count'] ?></td>
                                <td>
                                    <div class="ee-head-actions">
                                        <a class="ee-button" href="<?= ee_e(ee_url('cashflow', 'edit', ['id' => (int) $alternative['id']])) ?>">ویرایش جریان</a>
                                        <form method="post" action="<?= ee_e(ee_url('cashflow', 'deleteAlternative', ['id' => (int) $alternative['id']])) ?>" onsubmit="return confirm('این گزینه و همهٔ جریان نقدی آن حذف شود؟');">
                                            <?= csrf_field() ?>
                                            <button class="ee-button is-danger" type="submit" aria-label="حذف گزینه"><i class="fa-solid fa-trash"></i></button>
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
                <i class="fa-solid fa-layer-group"></i>
                <strong>هنوز گزینه‌ای تعریف نشده است</strong>
                <span>با فرم پایین، گزینهٔ اول (مثلاً «خرید دستگاه جدید») را بسازید.</span>
            </div>
        <?php endif; ?>
    </section>

    <section class="ee-card">
        <div class="ee-card-head"><div><span class="ee-eyebrow">افزودن</span><h2>گزینهٔ جدید</h2></div></div>
        <form class="ee-form" method="post" action="<?= ee_e(ee_url('cashflow', 'addAlternative', ['id' => (int) $project['id']])) ?>">
            <?= csrf_field() ?>
            <div class="ee-form-grid">
                <label>نام گزینه *<input name="name" maxlength="160" required placeholder="خرید دستگاه جدید / ادامهٔ وضع موجود"></label>
                <label>عمر مفید (سال)<input name="life_years" type="number" min="1" max="100" value="<?= (int) $project['study_period'] ?>"></label>
                <label class="ee-span-2">توضیح<textarea name="description" rows="2" maxlength="5000" placeholder="مشخصات فنی، ظرفیت یا فرض‌های کلیدی این گزینه"></textarea></label>
                <label class="ee-span-2" style="display:flex;align-items:center;gap:8px;">
                    <input type="checkbox" name="is_baseline" value="1" style="width:auto;">
                    <span>این گزینه مبنای مقایسه است (وضع موجود / عدم اقدام)</span>
                </label>
            </div>
            <div class="ee-form-actions">
                <button class="ee-button ee-button-primary" type="submit"><i class="fa-solid fa-plus"></i> ساخت گزینه</button>
            </div>
        </form>
    </section>

    <section class="ee-card">
        <div class="ee-card-head">
            <div><span class="ee-eyebrow">ردیابی</span><h2>فرض‌های مستند</h2><p>هر عدد کلیدی (MARR، تورم، قیمت خرید) باید منبع داشته باشد تا گزارش قابل دفاع باشد.</p></div>
        </div>

        <?php if (!empty($project['assumptions'])): ?>
            <div class="ee-table-wrap">
                <table class="ee-table">
                    <thead><tr><th>فرض</th><th>مقدار</th><th>واحد</th><th>منبع</th><th></th></tr></thead>
                    <tbody>
                        <?php foreach ($project['assumptions'] as $assumption): ?>
                            <tr>
                                <td><?= ee_e($assumption['title']) ?></td>
                                <td class="ee-num"><?= ee_e($assumption['value'] ?: '—') ?></td>
                                <td><?= ee_e($assumption['unit'] ?: '—') ?></td>
                                <td><?= ee_e($assumption['source'] ?: '—') ?></td>
                                <td>
                                    <form method="post" action="<?= ee_e(ee_url('cashflow', 'deleteAssumption', ['id' => (int) $assumption['id']])) ?>">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>">
                                        <button class="ee-button is-danger" type="submit" aria-label="حذف فرض"><i class="fa-solid fa-xmark"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <form class="ee-form" method="post" action="<?= ee_e(ee_url('cashflow', 'addAssumption', ['id' => (int) $project['id']])) ?>">
            <?= csrf_field() ?>
            <div class="ee-form-grid">
                <label>عنوان فرض *<input name="title" maxlength="180" required placeholder="نرخ بازدهٔ حداقلی"></label>
                <label>مقدار<input name="value" maxlength="120" placeholder="۲۴"></label>
                <label>واحد<input name="unit" maxlength="40" placeholder="٪"></label>
                <label>منبع<input name="source" maxlength="500" placeholder="مصوبهٔ هیئت‌مدیره ۱۴۰۵/۰۳"></label>
                <label class="ee-span-2">توضیح<textarea name="note" rows="2" maxlength="1000"></textarea></label>
            </div>
            <div class="ee-form-actions">
                <button class="ee-button" type="submit"><i class="fa-solid fa-clipboard-check"></i> ثبت فرض</button>
            </div>
        </form>
    </section>

    <div class="ee-note">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <strong>قواعد ثبت جریان نقدی</strong>
            <ul>
                <li>سال ۰ مخصوص سرمایه‌گذاری اولیه است (خروجی).</li>
                <li>ورودی و خروجی هر سال جدا ثبت می‌شود تا نسبت فایده/هزینه و تحلیل افزایشی ممکن باشد.</li>
                <li>مبالغ را در ارز <strong><?= ee_e($project['currency']) ?></strong> و به ارزش همان سال وارد کنید؛ تحلیل تورم به‌صورت جداگانه اعمال می‌شود.</li>
            </ul>
        </div>
    </div>
</section>
