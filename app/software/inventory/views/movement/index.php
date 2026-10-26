<section class="inv-page" dir="rtl">
    <header class="inv-page-head"><div><span class="inv-eyebrow">دفتر ثبت تغییر مانده</span><h1><i class="fa-solid fa-arrow-right-arrow-left"></i> گردش موجودی</h1><p>هر ثبت، مانده قلم را به‌شکل تراکنشی تغییر می‌دهد و در سابقه باقی می‌ماند.</p></div><a class="inv-btn inv-btn-secondary" href="<?= inv_e(inv_url('export', 'movements')) ?>"><i class="fa-solid fa-file-csv"></i> دریافت خروجی کامل گردش‌ها</a></header>
    <?php if (!empty($flash)): ?><div class="inv-alert <?= inv_e($flash['type']) ?>" role="status"><?= inv_e($flash['message']) ?></div><?php endif; ?>
    <form class="inv-card inv-movement-form" method="post" action="<?= inv_e(inv_url('movement', 'store')) ?>" data-inv-movement-form>
        <div class="inv-card-head"><div><h2>ثبت گردش جدید</h2><p>مقدار را مثبت وارد کنید؛ جهت گردش را نوع عملیات تعیین می‌کند.</p></div></div><?= $csrfHtml ?>
        <?php if ($items): ?><div class="inv-form-grid">
            <label>قلم *<select name="item_id" required><option value="">انتخاب قلم</option><?php foreach ($items as $item): ?><option value="<?= (int) $item['id'] ?>"><?= inv_e($item['sku'] . ' — ' . $item['name'] . ' (' . $item['unit'] . ')') ?></option><?php endforeach; ?></select></label>
            <label>نوع گردش *<select name="movement_type" required data-inv-movement-type><option value="receipt">دریافت به انبار</option><option value="issue">خروج از انبار</option><option value="adjustment">تعدیل شمارش</option></select></label>
            <label data-inv-adjustment hidden>جهت تعدیل<select name="adjustment_direction"><option value="increase">افزایش مانده</option><option value="decrease">کاهش مانده</option></select></label>
            <label>مقدار *<input name="quantity" type="number" min="0.0001" step="0.0001" required data-inv-quantity></label>
            <label data-inv-order-receipt hidden>از این دریافت، چه مقدار مربوط به سفارش در راه است؟<input name="received_against_order" type="number" min="0" step="0.0001" value="0"><small>این مقدار از سفارش در راه کم و به موجودی انبار اضافه می‌شود.</small></label>
            <label class="inv-form-wide">یادداشت<input name="note" maxlength="500" placeholder="شماره رسید، علت تعدیل یا توضیح کوتاه"></label>
        </div><div class="inv-form-actions"><button class="inv-btn inv-btn-primary" type="submit"><i class="fa-solid fa-check"></i> ثبت گردش</button></div>
        <?php else: ?><div class="inv-empty compact"><span>برای ثبت گردش ابتدا یک قلم موجودی ایجاد کنید.</span><a class="inv-text-link" href="<?= inv_e(inv_url('item', 'create')) ?>">افزودن قلم <i class="fa-solid fa-arrow-left"></i></a></div><?php endif; ?>
    </form>
    <section class="inv-card"><div class="inv-card-head"><div><h2>سابقه گردش</h2><p>آخرین ۵۰ رویداد</p></div></div>
        <?php if ($movements): ?><div class="inv-table-wrap"><table class="inv-table"><thead><tr><th>زمان</th><th>قلم</th><th>نوع گردش</th><th>مقدار تغییر</th><th>تسویه سفارش در راه</th><th>مانده پس از گردش</th><th>یادداشت</th></tr></thead><tbody>
        <?php foreach ($movements as $movement): $labels = ['opening' => 'موجودی اولیه', 'receipt' => 'دریافت', 'issue' => 'خروج', 'adjustment' => 'تعدیل']; ?><tr><td><?= inv_date($movement['created_at']) ?></td><td><strong><?= inv_e($movement['item_name']) ?></strong><small><?= inv_e($movement['sku']) ?></small></td><td><?= inv_e($labels[$movement['movement_type']] ?? $movement['movement_type']) ?></td><td class="<?= (float) $movement['quantity'] < 0 ? 'inv-negative' : 'inv-positive' ?>"><?= (float) $movement['quantity'] > 0 ? '+' : '' ?><?= inv_num($movement['quantity']) ?> <?= inv_e($movement['unit']) ?></td><td><?= inv_num($movement['on_order_received'] ?? 0) ?></td><td><?= inv_num($movement['balance_after']) ?> <?= inv_e($movement['unit']) ?></td><td><?= inv_e($movement['note'] ?: '—') ?></td></tr><?php endforeach; ?>
        </tbody></table></div><?php else: ?><div class="inv-empty compact"><span>هنوز رویدادی ثبت نشده است.</span></div><?php endif; ?>
    </section>
</section>
