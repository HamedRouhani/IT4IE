<?php
$value = static fn(string $key, string $default = ''): string => fl_e($source[$key] ?? $default);
$action = $service ? 'update' : 'store';
$params = $service ? ['id' => (int) $service['id']] : ['project_id' => (int) $project['id']];
$selected = static fn(string $key, string $candidate, string $default): string => (string) ($source[$key] ?? $default) === $candidate ? 'selected' : '';
?>
<main class="fl-page" dir="rtl">
    <header class="fl-page-head">
        <div>
            <span class="fl-eyebrow">گام S · خدمات پشتیبان و فضا</span>
            <h1><i class="fa-solid fa-screwdriver-wrench"></i> <?= $service ? 'ویرایش خدمت پشتیبان' : 'ثبت خدمت پشتیبان' ?></h1>
            <p>پروژه: <?= fl_e($project['title']) ?> · نیازهای خدماتی کارخانه را ثبت کنید و برای هر برآورد فضا، مبنا یا منبع آن را بنویسید.</p>
        </div>
        <a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('project', 'show', ['id' => (int) $project['id'], 'step' => 'services'])) ?>">بازگشت به پروژه</a>
    </header>
    <?php if (!empty($flash)): ?><div class="fl-alert <?= fl_e($flash['type']) ?>" role="status"><?= fl_e($flash['message']) ?></div><?php endif; ?>

    <form class="fl-card fl-form" method="post" action="<?= fl_e(fl_url('service', $action, $params)) ?>">
        <?= $csrfField ?>
        <?php if (!$service): ?><input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>"><?php endif; ?>
        <section class="fl-form-section">
            <span class="fl-eyebrow">نیاز و اولویت</span>
            <h2>مشخصات فضای پشتیبان</h2>
            <div class="fl-form-grid">
                <label>نام خدمت یا فعالیت پشتیبان *
                    <input name="name" maxlength="180" required value="<?= $value('name') ?>" placeholder="مثلاً انبار مواد اولیه">
                </label>
                <label>دستهٔ خدمت *
                    <select name="category" required>
                        <?php foreach ($serviceCategories as $key => $label): ?><option value="<?= fl_e($key) ?>" <?= $selected('category', $key, 'other') ?>><?= fl_e($label) ?></option><?php endforeach; ?>
                    </select>
                </label>
                <label>سطح الزام *
                    <select name="requirement_level" required>
                        <?php foreach ($requirements as $key => $label): ?><option value="<?= fl_e($key) ?>" <?= $selected('requirement_level', $key, 'required') ?>><?= fl_e($label) ?></option><?php endforeach; ?>
                    </select>
                    <small>الزامی را از ترجیح طراحی جدا کنید؛ این انتخاب جایگزین کنترل قیود ایمنی و قانونی نیست.</small>
                </label>
                <label>مساحت برآوردی (مترمربع)
                    <input name="area_m2" inputmode="decimal" value="<?= $value('area_m2') ?>" placeholder="اگر هنوز معلوم نیست، خالی بگذارید">
                    <small>عدد مثبت با حداکثر سه رقم اعشار؛ مقدار خالی یعنی هنوز برآورد نشده است.</small>
                </label>
                <label class="fl-span-2">مبنای برآورد یا منبع مساحت
                    <textarea name="area_basis" rows="3" maxlength="500" placeholder="مثلاً سطح قفسه‌ها به‌علاوهٔ راهروی دسترسی؛ منبع: نقشهٔ موجود یا استاندارد داخلی."><?= $value('area_basis') ?></textarea>
                    <small>در صورت ثبت مساحت، درج مبنا یا منبع آن الزامی است.</small>
                </label>
                <label class="fl-span-2">ملاحظات و محدودیت‌ها
                    <textarea name="notes" rows="3" maxlength="1000" placeholder="مثلاً نیاز به جداسازی، تهویه، دسترسی خودرو یا توسعهٔ آتی."><?= $value('notes') ?></textarea>
                </label>
            </div>
            <aside class="fl-inline-note"><i class="fa-solid fa-circle-info"></i><span>مساحت ثبت‌شده یک ورودی مقدماتی است. فضاهای دسترسی، ایمنی، تأسیسات، ستون‌ها و الزامات محلی باید در برآورد نهایی جداگانه بررسی شوند؛ مقدار نامعلوم را تخمین قطعی وارد نکنید.</span></aside>
        </section>
        <div class="fl-form-actions">
            <button class="fl-button fl-button-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> ذخیرهٔ خدمت</button>
            <a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('project', 'show', ['id' => (int) $project['id'], 'step' => 'services'])) ?>">انصراف</a>
        </div>
    </form>
</main>
