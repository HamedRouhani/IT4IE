<section class="ee-page" dir="rtl">
    <header class="ee-page-head">
        <div>
            <span class="ee-eyebrow">اتصال به Time Study Analyzer</span>
            <h1><i class="fa-solid fa-stopwatch"></i> برآورد هزینه از زمان‌سنجی</h1>
            <p>زمان استاندارد نیروی انسانی را به هزینهٔ مستقیم هر واحد و جریان هزینهٔ سالانه تبدیل کنید.</p>
        </div>
        <div class="ee-head-actions">
            <a class="ee-button" href="/software/time-study-analyzer/?controller=study&amp;action=show&amp;id=<?= (int) $study['id'] ?>">
                <i class="fa-solid fa-arrow-right"></i> بازگشت به گزارش زمان‌سنجی
            </a>
        </div>
    </header>

    <?php if (!empty($flash)): ?>
        <div class="ee-alert <?= ee_e($flash['type']) ?>" role="status"><?= ee_e($flash['message']) ?></div>
    <?php endif; ?>

    <section class="ee-card">
        <h2><i class="fa-solid fa-clipboard-check"></i> مبنای محاسبه</h2>
        <div class="ee-form-grid">
            <div><strong>مطالعه</strong><p><?= ee_e($study['study_no']) ?> · <?= ee_e($study['title']) ?></p></div>
            <div><strong>فرایند</strong><p><?= ee_e($study['process_name']) ?></p></div>
            <div><strong>زمان استاندارد نیروی انسانی</strong><p><?= ee_num($standardLaborSeconds, 4) ?> ثانیه/<?= ee_e($study['unit_name']) ?></p></div>
            <div><strong>نمونه</strong><p><?= (int) $result['complete_valid_cycles'] ?> چرخهٔ کامل معتبر · کفایت نمونه تأیید شده</p></div>
        </div>
        <div class="ee-note" style="margin-top:16px">
            <i class="fa-solid fa-circle-info"></i>
            <div>زمان دستی با allowance ثبت‌شده در مطالعه استاندارد شده است. زمان ماشین از هزینهٔ نیروی انسانی جدا می‌ماند؛ سربار، مواد، انرژی و هزینه‌های دیگر نیز در این برآورد نیستند. پروژه با جریان‌های اسمی ساخته می‌شود و رشد سالانهٔ هزینه را جداگانه وارد کنید.</div>
        </div>
    </section>

    <form class="ee-form ee-card" method="post" action="<?= ee_e(ee_url('integration', 'createFromTimeStudy', ['id' => (int) $study['id']])) ?>">
        <?= csrf_field() ?>
        <div class="ee-form-section">
            <h3>فرض‌های هزینه و افق تحلیل</h3>
            <div class="ee-form-grid">
                <label>دستمزد مستقیم ساعتی *
                    <input type="text" name="labor_rate_per_hour" inputmode="decimal" required value="500000" placeholder="مثلاً ۵۰۰۰۰۰">
                    <small>فقط نرخ دستمزد نیروی انسانی را وارد کنید.</small>
                </label>
                <label>تیراژ سالانهٔ سال اول *
                    <input type="text" name="annual_quantity" inputmode="decimal" required value="10000" placeholder="مثلاً ۱۰۰۰۰">
                    <small>تعداد واحدهای قابل تولید در سال اول.</small>
                </label>
                <label>افق تحلیل (سال) *
                    <input type="number" name="study_period" min="1" max="100" step="1" required value="5">
                </label>
                <label>رشد سالانهٔ هزینهٔ نیروی کار (%) *
                    <input type="text" name="annual_growth_pct" inputmode="decimal" required value="0" placeholder="مثلاً ۲۰">
                    <small>برای تبدیل هزینهٔ سال اول به جریان‌های اسمی سال‌های بعد.</small>
                </label>
                <label>نرخ بازده حداقل اسمی (MARR) (%) *
                    <input type="text" name="marr_pct" inputmode="decimal" required value="12">
                    <small>چون جریان‌های هزینه اسمی هستند، این نرخ نیز باید اسمی باشد.</small>
                </label>
                <label>واحد پول
                    <select name="currency">
                        <option value="IRT" selected>IRT — تومان</option>
                        <option value="IRR">IRR — ریال</option>
                        <option value="USD">USD — دلار</option>
                        <option value="EUR">EUR — یورو</option>
                    </select>
                </label>
            </div>
        </div>

        <div class="ee-note" style="margin-top:16px">
            <i class="fa-solid fa-calculator"></i>
            <div>
                هزینهٔ مستقیم هر واحد = زمان استاندارد نیروی انسانی (ثانیه) ÷ ۳۶۰۰ × دستمزد ساعتی.
                هزینهٔ سال اول = هزینهٔ هر واحد × تیراژ سال اول؛ تیراژ در سال‌های بعد ثابت فرض می‌شود و هزینهٔ دستمزد هر سال با نرخ رشد ثبت‌شده تعدیل می‌شود. جریان هر سال بعداً در بخش جریان نقدی قابل ویرایش است.
                سال پایه در جریان نقدی صفر است.
            </div>
        </div>

        <div class="ee-form-actions">
            <a class="ee-button ee-button-ghost" href="/software/time-study-analyzer/?controller=study&amp;action=show&amp;id=<?= (int) $study['id'] ?>">انصراف</a>
            <button class="ee-button ee-button-primary" type="submit"><i class="fa-solid fa-file-circle-plus"></i> ساخت پروژهٔ هزینه و ادامهٔ تحلیل</button>
        </div>
    </form>
</section>
