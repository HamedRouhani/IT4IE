<?php
$value = static fn(string $key, string $default = ''): string => fl_e($source[$key] ?? $default);
$action = $product ? 'update' : 'store';
$params = $product ? ['id' => (int) $product['id']] : ['project_id' => (int) $project['id']];
?>
<main class="fl-page" dir="rtl">
    <header class="fl-page-head">
        <div>
            <span class="fl-eyebrow">گام P و Q · داده‌های محصول</span>
            <h1><i class="fa-solid fa-box"></i> <?= $product ? 'ویرایش محصول' : 'ثبت محصول یا خانوادهٔ محصول' ?></h1>
            <p>پروژه: <?= fl_e($project['title']) ?> · مقدار تولید برای هر محصول جداگانه و در دورهٔ <?= fl_e($periodLabel) ?> ثبت می‌شود.</p>
        </div>
        <a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('project', 'show', ['id' => (int) $project['id'], 'step' => 'products'])) ?>">بازگشت به پروژه</a>
    </header>
    <?php if (!empty($flash)): ?><div class="fl-alert <?= fl_e($flash['type']) ?>" role="status"><?= fl_e($flash['message']) ?></div><?php endif; ?>

    <form class="fl-card fl-form" method="post" action="<?= fl_e(fl_url('product', $action, $params)) ?>">
        <?= $csrfField ?>
        <?php if (!$product): ?><input type="hidden" name="project_id" value="<?= (int) $project['id'] ?>"><?php endif; ?>
        <section class="fl-form-section">
            <span class="fl-eyebrow">شناسه و مقدار</span>
            <h2>مشخصات محصول در برنامهٔ تولید</h2>
            <div class="fl-form-grid">
                <label>نام محصول یا خانوادهٔ محصول *
                    <input name="name" maxlength="180" required value="<?= $value('name') ?>" placeholder="مثلاً مجموعهٔ پمپ صنعتی">
                </label>
                <label>کد محصول <span class="fl-muted">(اختیاری و یکتا در این پروژه)</span>
                    <input name="product_code" maxlength="40" value="<?= $value('product_code') ?>" placeholder="مثلاً P-01">
                </label>
                <label>مقدار تولید در هر <?= fl_e($periodLabel) ?> *
                    <input name="production_quantity" inputmode="decimal" required value="<?= $value('production_quantity') ?>" placeholder="مثلاً 1250 یا ۱۲۵۰٫۵">
                    <small>عدد مثبت؛ حداکثر ۴ رقم اعشار. مقدار هر محصول جداگانه نگهداری می‌شود.</small>
                </label>
                <label>واحد همین محصول *
                    <input name="production_unit" maxlength="40" required value="<?= $value('production_unit') ?>" placeholder="مثلاً دستگاه، تن، کیلوگرم، لیتر">
                    <small>واحد را صریح وارد کنید؛ مقادیر با واحدهای متفاوت با هم جمع نمی‌شوند.</small>
                </label>
                <label class="fl-span-2">یادداشت و فرض‌های مقدار تولید
                    <textarea name="notes" rows="3" maxlength="1000" placeholder="منبع برآورد، ترکیب خانواده یا فرض‌های برنامهٔ تولید را بنویسید."><?= $value('notes') ?></textarea>
                </label>
            </div>
            <aside class="fl-inline-note"><i class="fa-solid fa-circle-info"></i><span>این مقدار، تقاضا/حجم تولید مبنای تحلیل است. برای محاسبهٔ جریان مواد و مساحت، در گام‌های بعد مسیر ساخت، ضایعات و ضرایب تبدیل جداگانه ثبت خواهند شد.</span></aside>
        </section>
        <div class="fl-form-actions">
            <button class="fl-button fl-button-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> ذخیرهٔ محصول</button>
            <a class="fl-button fl-button-ghost" href="<?= fl_e(fl_url('project', 'show', ['id' => (int) $project['id'], 'step' => 'products'])) ?>">انصراف</a>
        </div>
    </form>
</main>
