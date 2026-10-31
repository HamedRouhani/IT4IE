<?php $editing = !empty($item); $value = static fn($key, $default = '') => inv_e($item[$key] ?? $default); ?>
<section class="inv-page" dir="rtl">
    <header class="inv-page-head"><div><span class="inv-eyebrow">اطلاعات تحلیل و تأمین</span><h1><i class="fa-solid fa-box"></i> <?= $editing ? 'ویرایش قلم موجودی' : 'افزودن قلم موجودی' ?></h1><p>اعداد را با واحد زمانی و پولی یکسان وارد کنید.</p></div><a class="inv-btn inv-btn-secondary" href="<?= inv_e(inv_url('item')) ?>"><i class="fa-solid fa-arrow-right"></i> بازگشت</a></header>
    <?php if (!empty($flash)): ?><div class="inv-alert <?= inv_e($flash['type']) ?>" role="status"><?= inv_e($flash['message']) ?></div><?php endif; ?>
    <form class="inv-card inv-form" method="post" action="<?= inv_e(inv_url('item', $editing ? 'update' : 'store', $editing ? ['id' => $item['id']] : [])) ?>" data-inv-form>
        <?= $csrfHtml ?>
        <div class="inv-form-section"><h2>مشخصات قلم</h2><div class="inv-form-grid">
            <label>کد قلم *<input name="sku" required maxlength="80" value="<?= $value('sku') ?>" autocomplete="off"></label>
            <label>نام قلم *<input name="name" required maxlength="180" value="<?= $value('name') ?>"></label>
            <label>دسته‌بندی<input name="category" maxlength="120" value="<?= $value('category') ?>"></label>
            <label>واحد *<input name="unit" required maxlength="40" value="<?= $value('unit', 'عدد') ?>"></label>
        </div></div>
        <div class="inv-form-section"><h2>موجودی و تقاضا</h2><div class="inv-form-grid">
            <label>موجودی فعلی *<input name="on_hand" type="number" min="0" step="0.0001" required value="<?= $value('on_hand', '0') ?>" <?= $editing ? 'readonly aria-readonly="true"' : '' ?>><small><?= $editing ? 'برای تغییر مانده از دفتر گردش استفاده کنید.' : 'این مقدار به‌عنوان موجودی اولیه در دفتر گردش ثبت می‌شود.' ?></small></label>
            <label>سفارش در راه<input name="on_order" type="number" min="0" step="0.0001" value="<?= $value('on_order', '0') ?>" <?= $editing ? 'readonly aria-readonly="true"' : '' ?>><small><?= $editing ? 'مانده با ارسال، دریافت یا لغو سفارش خرید به‌روزرسانی می‌شود.' : 'مقدار اولیه را می‌توان ثبت کرد؛ پس از آن از سفارش خرید استفاده کنید.' ?></small></label>
            <label>تقاضای سالانه *<input name="annual_demand" type="number" min="0" step="0.0001" required value="<?= $value('annual_demand', '0') ?>"></label>
            <label>انحراف معیار تقاضای روزانه *<input name="daily_demand_std" type="number" min="0" step="0.0001" required value="<?= $value('daily_demand_std', '0') ?>"></label>
            <label>زمان تأمین (روز) *<input name="lead_time_days" type="number" min="0" step="0.01" required value="<?= $value('lead_time_days', '0') ?>"></label>
        </div></div>
        <div class="inv-form-section"><h2>پارامترهای هزینه و سطح خدمت</h2><div class="inv-form-grid">
            <label>بهای هر واحد *<input name="unit_cost" type="number" min="0" step="0.0001" required value="<?= $value('unit_cost', '0') ?>"></label>
            <label>هزینه هر سفارش *<input name="order_cost" type="number" min="0" step="0.0001" required value="<?= $value('order_cost', '0') ?>"></label>
            <label>نرخ نگهداری سالانه *<input name="holding_rate" type="number" min="0" max="1" step="0.0001" required value="<?= $value('holding_rate', '0.25') ?>"><small>نسبت سالانه؛ ۰٫۲۵ یعنی ۲۵٪ ارزش واحد در سال.</small></label>
            <label>سطح خدمت هدف *<select name="service_level" required><?php foreach (\App\Software\Inventory\Services\InventoryPolicyService::serviceLevels() as $level => $label): ?><option value="<?= inv_e($level) ?>" <?= (string) ($item['service_level'] ?? '95') === (string) $level ? 'selected' : '' ?>><?= inv_e($label) ?></option><?php endforeach; ?></select></label>
        </div></div>
        <div class="inv-form-note"><i class="fa-solid fa-circle-info"></i><span>همهٔ بها و هزینه‌ها را با یک واحد پولی مشترک ثبت کنید؛ ارزش موجودی یک برآورد بر اساس بهای واحد واردشده است، نه ارزش‌گذاری حسابداری. برای پیشنهاد سفارش، تقاضای سالانه، انحراف معیار روزانه و زمان تأمین نیز باید معتبر باشند.</span></div>
        <div class="inv-form-actions"><button class="inv-btn inv-btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> <?= $editing ? 'ذخیره تغییرات' : 'ثبت قلم' ?></button><a class="inv-btn inv-btn-ghost" href="<?= inv_e(inv_url('item')) ?>">انصراف</a></div>
    </form>
</section>
