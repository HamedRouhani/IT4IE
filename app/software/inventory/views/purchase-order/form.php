<section class="inv-page" dir="rtl">
    <header class="inv-page-head"><div><span class="inv-eyebrow">پیش‌نویس قابل ویرایش تا زمان ارسال</span><h1><i class="fa-solid fa-file-circle-plus"></i> ایجاد سفارش خرید</h1><p>تعداد سفارش را فقط برای اقلام انتخاب‌شده وارد کنید.</p></div><a class="inv-btn inv-btn-secondary" href="<?= inv_e(inv_url('purchase_order')) ?>"><i class="fa-solid fa-arrow-right"></i> بازگشت</a></header>
    <?php if (!empty($flash)): ?><div class="inv-alert <?= inv_e($flash['type']) ?>" role="status"><?= inv_e($flash['message']) ?></div><?php endif; ?>
    <?php if ($items): ?><form class="inv-card inv-form" method="post" action="<?= inv_e(inv_url('purchase_order', 'store')) ?>" data-inv-form>
        <?= $csrfHtml ?>
        <div class="inv-form-grid">
            <label>تأمین‌کننده *<input name="supplier_name" required maxlength="180" autocomplete="organization"></label>
            <label class="inv-date-label">موعد تحویل (شمسی)<input class="hr-datepicker" name="expected_date" type="text" inputmode="numeric" placeholder="۱۴۰۵/۰۸/۱۰" autocomplete="off" pattern="(13[0-9]{2}|14[0-9]{2}|1500)/(0?[1-9]|1[0-2])/(0?[1-9]|[12][0-9]|3[01])" title="تاریخ را به‌شکل شمسی سال/ماه/روز وارد کنید."><small>تاریخ در پایگاه‌داده به میلادی ذخیره می‌شود.</small></label>
            <label class="inv-form-wide">یادداشت<input name="notes" maxlength="1000" placeholder="شرایط یا توضیح سفارش"></label>
        </div>
        <div class="inv-card-head"><div><h2>اقلام سفارش</h2><p>پیشنهاد محاسباتی راهنماست؛ پیش‌نویس را پیش از ارسال بررسی کنید.</p></div></div>
        <div class="inv-table-wrap"><table class="inv-table"><thead><tr><th>قلم</th><th>موجودی</th><th>در راه</th><th>نقطه سفارش</th><th>پیشنهاد</th><th>مقدار سفارش</th><th>بهای واحد</th></tr></thead><tbody>
        <?php foreach ($items as $item): $analysis = $item['_analysis']; $prefill = !empty($suggested) && $analysis['should_order'] ? number_format((float) $analysis['suggested_order'], 4, '.', '') : ''; ?><tr><td><strong><?= inv_e($item['name']) ?></strong><small><?= inv_e($item['sku']) ?> · <?= inv_e($item['unit']) ?> · ABC <?= inv_e($analysis['abc_class']) ?></small></td><td><?= inv_num($item['on_hand']) ?></td><td><?= inv_num($item['on_order']) ?></td><td><?= inv_num($analysis['reorder_point']) ?></td><td><?= $analysis['should_order'] ? inv_num($analysis['suggested_order']) : '—' ?></td><td><input class="inv-order-input" type="number" min="0.0001" step="0.0001" name="quantity[<?= (int) $item['id'] ?>]" value="<?= inv_e($prefill) ?>" aria-label="مقدار سفارش <?= inv_e($item['name']) ?>"></td><td><input class="inv-order-input" type="number" min="0" step="0.0001" name="unit_cost[<?= (int) $item['id'] ?>]" value="<?= inv_e(number_format((float) $item['unit_cost'], 4, '.', '')) ?>" aria-label="بهای خرید <?= inv_e($item['name']) ?>"></td></tr><?php endforeach; ?>
        </tbody></table></div>
        <div class="inv-form-note"><i class="fa-solid fa-circle-info"></i><span>ذخیرهٔ این فرم فقط پیش‌نویس می‌سازد و موجودی در راه را تغییر نمی‌دهد. پس از ارسال سفارش، مقدار باز به موجودی در راه اضافه می‌شود.</span></div>
        <div class="inv-form-actions"><button class="inv-btn inv-btn-primary" type="submit"><i class="fa-solid fa-floppy-disk"></i> ذخیره پیش‌نویس</button><a class="inv-btn inv-btn-ghost" href="<?= inv_e(inv_url('purchase_order')) ?>">انصراف</a></div>
    </form><?php else: ?><div class="inv-card inv-empty"><i class="fa-solid fa-box-open"></i><strong>برای سفارش خرید ابتدا قلم موجودی بسازید.</strong><a class="inv-btn inv-btn-primary" href="<?= inv_e(inv_url('item', 'create')) ?>">افزودن قلم</a></div><?php endif; ?>
</section>
<script src="/public/assets/js/software/hr-datepicker.js?v=<?= time() ?>" defer></script>
