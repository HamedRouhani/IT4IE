<section class="inv-page" dir="rtl">
    <header class="inv-page-head">
        <div>
            <span class="inv-eyebrow">Inventory Analyzer</span>
            <h1><i class="fa-solid fa-boxes-stacked"></i> داشبورد کنترل موجودی</h1>
            <p>وضعیت اقلام، هشدار سفارش و ارزش موجودی را یکجا ببینید.</p>
        </div>
        <div class="inv-head-actions">
            <a class="inv-btn inv-btn-primary" href="<?= inv_e(inv_url('item', 'create')) ?>"><i class="fa-solid fa-plus"></i> افزودن قلم</a>
            <a class="inv-btn inv-btn-secondary" href="<?= inv_e(inv_url('purchase_order', 'create', ['suggested' => 1])) ?>"><i class="fa-solid fa-file-circle-plus"></i> پیش‌نویس سفارش پیشنهادی</a>
            <a class="inv-btn inv-btn-secondary" href="<?= inv_e(inv_url('movement')) ?>"><i class="fa-solid fa-arrow-right-arrow-left"></i> ثبت گردش</a>
        </div>
    </header>

    <?php if (!empty($flash)): ?><div class="inv-alert <?= inv_e($flash['type']) ?>" role="status"><?= inv_e($flash['message']) ?></div><?php endif; ?>

    <div class="inv-stats">
        <article class="inv-stat"><span class="inv-stat-icon"><i class="fa-solid fa-box"></i></span><div><small>اقلام فعال</small><strong><?= inv_num($stats['item_count'], 0) ?></strong></div></article>
        <article class="inv-stat is-warning"><span class="inv-stat-icon"><i class="fa-solid fa-cart-shopping"></i></span><div><small>نیازمند سفارش</small><strong><?= inv_num($stats['reorder_count'], 0) ?></strong></div></article>
        <article class="inv-stat is-blue"><span class="inv-stat-icon"><i class="fa-solid fa-coins"></i></span><div><small>ارزش موجودی فعلی</small><strong><?= inv_num($stats['inventory_value']) ?></strong></div></article>
        <article class="inv-stat is-teal"><span class="inv-stat-icon"><i class="fa-solid fa-right-left"></i></span><div><small>گردش‌های ثبت‌شده</small><strong><?= inv_num($stats['movement_count'], 0) ?></strong></div></article>
        <article class="inv-stat is-warning"><span class="inv-stat-icon"><i class="fa-solid fa-truck-fast"></i></span><div><small>سفارش‌های باز خرید</small><strong><?= inv_num($stats['open_po_count'], 0) ?></strong></div></article>
    </div>

    <section class="inv-card">
        <div class="inv-card-head"><div><h2>پیشنهادهای سفارش</h2><p>بر اساس نقطه سفارش و فرض‌های ثبت‌شده برای هر قلم</p></div><a href="<?= inv_e(inv_url('analysis')) ?>" class="inv-text-link">مشاهده تحلیل کامل <i class="fa-solid fa-arrow-left"></i></a></div>
        <?php $reorderItems = array_slice(array_values(array_filter($items, static fn($row) => $row['_analysis']['should_order'])), 0, 8); ?>
        <?php if ($reorderItems): ?>
            <div class="inv-table-wrap"><table class="inv-table"><thead><tr><th>کد / قلم</th><th>ABC</th><th>موقعیت موجودی</th><th>نقطه سفارش</th><th>مقدار پیشنهادی</th><th></th></tr></thead><tbody>
            <?php foreach ($reorderItems as $row): ?><tr><td><strong><?= inv_e($row['name']) ?></strong><small><?= inv_e($row['sku']) ?></small></td><td><span class="inv-badge inv-abc-<?= inv_e(strtolower($row['_analysis']['abc_class'])) ?>"><?= inv_e($row['_analysis']['abc_class']) ?></span></td><td><?= inv_num($row['_analysis']['inventory_position']) ?> <?= inv_e($row['unit']) ?></td><td><?= inv_num($row['_analysis']['reorder_point']) ?> <?= inv_e($row['unit']) ?></td><td><strong class="inv-accent"><?= inv_num($row['_analysis']['suggested_order']) ?> <?= inv_e($row['unit']) ?></strong></td><td><a class="inv-icon-link" href="<?= inv_e(inv_url('item', 'edit', ['id' => $row['id']])) ?>" aria-label="ویرایش قلم"><i class="fa-solid fa-pen"></i></a></td></tr><?php endforeach; ?>
            </tbody></table></div>
        <?php else: ?><div class="inv-empty"><i class="fa-solid fa-circle-check"></i><strong>در حال حاضر قلمی زیر نقطه سفارش نیست.</strong><span>برای دریافت پیشنهاد، تقاضا و زمان تأمین اقلام را ثبت کنید.</span></div><?php endif; ?>
    </section>

    <div class="inv-dashboard-grid">
        <section class="inv-card"><div class="inv-card-head"><div><h2>آخرین سفارش‌های خرید</h2><p>پیش‌نویس‌ها و وضعیت دریافت</p></div><a href="<?= inv_e(inv_url('purchase_order')) ?>" class="inv-text-link">همه سفارش‌ها</a></div>
            <?php if (!empty($orders)): ?><div class="inv-movement-list"><?php $poLabels = ['draft' => 'پیش‌نویس', 'ordered' => 'ارسال‌شده', 'partial' => 'دریافت مرحله‌ای', 'received' => 'دریافت کامل', 'cancelled' => 'لغوشده']; foreach ($orders as $order): ?><a class="inv-movement-row inv-order-row" href="<?= inv_e(inv_url('purchase_order', 'show', ['id' => $order['id']])) ?>"><span class="inv-movement-symbol"><i class="fa-solid fa-file-invoice"></i></span><span class="inv-movement-name"><strong><?= inv_e($order['order_no']) ?> · <?= inv_e($order['supplier_name']) ?></strong><small><?= inv_e($poLabels[$order['status']] ?? $order['status']) ?> · <?= inv_num($order['line_count'], 0) ?> ردیف</small></span><b><?= inv_num($order['order_value']) ?></b></a><?php endforeach; ?></div><?php else: ?><div class="inv-empty compact"><span>سفارشی ثبت نشده است.</span><a href="<?= inv_e(inv_url('purchase_order', 'create')) ?>" class="inv-text-link">ایجاد سفارش</a></div><?php endif; ?>
        </section>
        <section class="inv-card"><div class="inv-card-head"><div><h2>آخرین گردش‌ها</h2><p>دفتر ثبت دریافت، خروج و تعدیل</p></div><a href="<?= inv_e(inv_url('movement')) ?>" class="inv-text-link">دفتر گردش</a></div>
            <?php if (!empty($movements)): ?><div class="inv-movement-list">
                <?php foreach ($movements as $movement): $label = ['opening' => 'موجودی اولیه', 'receipt' => 'دریافت', 'issue' => 'خروج', 'adjustment' => 'تعدیل'][$movement['movement_type']] ?? $movement['movement_type']; ?>
                    <div class="inv-movement-row"><span class="inv-movement-symbol <?= (float) $movement['quantity'] < 0 ? 'is-minus' : 'is-plus' ?>"><i class="fa-solid <?= (float) $movement['quantity'] < 0 ? 'fa-arrow-down' : 'fa-arrow-up' ?>"></i></span><div class="inv-movement-name"><strong><?= inv_e($movement['item_name']) ?></strong><small><?= inv_e($label) ?> · <?= inv_date($movement['created_at']) ?></small></div><b><?= (float) $movement['quantity'] > 0 ? '+' : '' ?><?= inv_num($movement['quantity']) ?> <?= inv_e($movement['unit']) ?></b></div>
                <?php endforeach; ?>
            </div><?php else: ?><div class="inv-empty compact"><span>هنوز گردش موجودی ثبت نشده است.</span></div><?php endif; ?>
        </section>
        <section class="inv-card inv-abc-card"><div class="inv-card-head"><div><h2>نمای کلی ABC</h2><p>بر پایهٔ ارزش مصرف سالانه</p></div><i class="fa-solid fa-chart-pie inv-card-icon"></i></div><div class="inv-abc-grid"><div><span class="inv-badge inv-abc-a">A</span><strong><?= inv_num($stats['abc_a'], 0) ?></strong><small>قلم</small></div><div><span class="inv-badge inv-abc-b">B</span><strong><?= inv_num($stats['abc_b'], 0) ?></strong><small>قلم</small></div><div><span class="inv-badge inv-abc-c">C</span><strong><?= inv_num($stats['abc_c'], 0) ?></strong><small>قلم</small></div></div><a href="<?= inv_e(inv_url('analysis')) ?>" class="inv-btn inv-btn-secondary inv-btn-block">رفتن به تحلیل موجودی</a></section>
    </div>
</section>
