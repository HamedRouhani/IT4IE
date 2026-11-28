<?php
$value = static fn(string $key, string $default = ''): string => fl_e($source[$key] ?? $default);
$action = $step ? 'update' : 'store';
$params = $step ? ['id' => (int) $step['id']] : ['product_id' => (int) $product['id']];
$selected = static fn(string $candidate): string => (string) ($source['activity_type'] ?? 'operation') === $candidate ? 'selected' : '';
?>
<main class="fl-page" dir="rtl">
    <header class="fl-page-head">
        <div>
            <span class="fl-eyebrow">گام R · مسیر و توالی عملیات</span>
            <h1><i class="fa-solid fa-route"></i> <?= $step ? 'ویرایش گام مسیر' : 'افزودن گام به مسیر' ?></h1>
            <p>محصول: <?= fl_e($product['name']) ?><?= $step ? ' · گام ' . (int) $step['step_no'] : '' ?></p>
        </div>
        <a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('project', 'show', ['id' => (int) $product['project_id'], 'step' => 'routes'])) ?>">بازگشت به پروژه</a>
    </header>
    <?php if (!empty($flash)): ?><div class="fl-alert <?= fl_e($flash['type']) ?>" role="status"><?= fl_e($flash['message']) ?></div><?php endif; ?>

    <form class="fl-card fl-form" method="post" action="<?= fl_e(fl_url('route', $action, $params)) ?>">
        <?= $csrfField ?>
        <?php if (!$step): ?><input type="hidden" name="product_id" value="<?= (int) $product['id'] ?>"><?php endif; ?>
        <section class="fl-form-section">
            <span class="fl-eyebrow">مکان و فعالیت فرایند</span>
            <h2>یک گام واقعی از مسیر ساخت را ثبت کنید</h2>
            <div class="fl-form-grid">
                <label>نام بخش، ایستگاه یا محل فعالیت *
                    <input name="area_name" maxlength="180" required value="<?= $value('area_name') ?>" placeholder="مثلاً برشکاری یا ایستگاه مونتاژ">
                    <small>نام را طوری انتخاب کنید که بعداً بتوان آن را به یک فعالیت در تحلیل چیدمان نسبت داد.</small>
                </label>
                <label>نوع فعالیت *
                    <select name="activity_type" required>
                        <?php foreach ($types as $type => $label): ?><option value="<?= fl_e($type) ?>" <?= $selected($type) ?>><?= fl_e($label) ?></option><?php endforeach; ?>
                    </select>
                    <small>طبقه‌بندی فعالیت برای خوانایی مسیر است و به‌تنهایی فاصله یا مقدار جابه‌جایی را تعیین نمی‌کند.</small>
                </label>
                <label class="fl-span-2">شرح کار در این گام
                    <textarea name="description" rows="3" maxlength="500" placeholder="عملیات یا ماده‌ای که وارد و خارج این فعالیت می‌شود را توضیح دهید."><?= $value('description') ?></textarea>
                </label>
                <label class="fl-span-2">فرض‌ها و منبع اطلاعات
                    <textarea name="notes" rows="3" maxlength="1000" placeholder="مثلاً ترتیب از مشاهدهٔ خط تولید یا دستور ساخت استخراج شده است."><?= $value('notes') ?></textarea>
                </label>
            </div>
            <aside class="fl-inline-note"><i class="fa-solid fa-circle-info"></i><span>گام جدید به انتهای مسیر افزوده می‌شود. برای جابه‌جایی آن از دکمه‌های بالا و پایین در صفحهٔ پروژه استفاده کنید. مسیر ثبت‌شده مبنای بعدیِ تعیین انتقال بین فعالیت‌ها خواهد بود.</span></aside>
        </section>
        <div class="fl-form-actions">
            <button class="fl-button fl-button-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> ذخیرهٔ گام</button>
            <a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('project', 'show', ['id' => (int) $product['project_id'], 'step' => 'routes'])) ?>">انصراف</a>
        </div>
    </form>
</main>
