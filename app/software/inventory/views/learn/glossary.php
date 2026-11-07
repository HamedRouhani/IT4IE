<section class="inv-page" dir="rtl">
    <header class="inv-page-head">
        <div>
            <span class="inv-eyebrow">مرجع سریع</span>
            <h1><i class="fa-solid fa-book"></i> واژه‌نامهٔ کنترل موجودی</h1>
            <p><?= count($terms) ?> اصطلاح کلیدی با معادل فارسی و تعریف کوتاه؛ جست‌وجو در نام انگلیسی یا فارسی کار می‌کند.</p>
        </div>
        <div class="inv-head-actions">
            <a class="inv-btn inv-btn-secondary" href="<?= inv_e(inv_url('learn', 'index')) ?>">درس‌ها</a>
            <a class="inv-btn inv-btn-primary" href="<?= inv_e(inv_url('learn', 'tools')) ?>"><i class="fa-solid fa-calculator"></i> ماشین‌حساب‌ها</a>
        </div>
    </header>

    <section class="inv-card">
        <div class="inv-card-head">
            <div><span class="inv-eyebrow">اصطلاحات</span><h2>جست‌وجو در واژه‌ها</h2></div>
            <span class="inv-tag" data-inv-glossary-count><?= count($terms) ?> اصطلاح</span>
        </div>

        <label class="inv-search" for="inv-glossary-search">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input id="inv-glossary-search" type="search" placeholder="جست‌وجوی اصطلاح…" data-inv-glossary-search autocomplete="off">
        </label>

        <div class="inv-glossary" data-inv-glossary-list>
            <?php foreach ($terms as $term): ?>
                <?php
                $searchText = $term['term'] . ' ' . $term['fa'] . ' ' . $term['def'];
                $searchText = function_exists('mb_strtolower') ? mb_strtolower($searchText, 'UTF-8') : strtolower($searchText);
                ?>
                <article class="inv-glossary-item" data-name="<?= inv_e($searchText) ?>">
                    <div class="inv-glossary-head">
                        <strong dir="ltr"><?= inv_e($term['term']) ?></strong>
                        <span><?= inv_e($term['fa']) ?></span>
                    </div>
                    <p><?= inv_e($term['def']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <p class="inv-nomatch" data-inv-glossary-empty hidden>اصطلاحی با این عبارت پیدا نشد.</p>
    </section>

    <div class="inv-note">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <strong>یادآوری</strong>
            <p>تعریف‌ها کوتاه و کاربردی‌اند؛ برای فرض‌ها و فرمول‌های دقیق هر مفهوم، درس مربوط در بخش «درس‌ها» را ببینید. مفروضات محاسباتی ماژول نیز در مستند روش محاسبات آمده است.</p>
        </div>
    </div>
</section>

<script>
(function () {
    'use strict';
    var input = document.querySelector('[data-inv-glossary-search]');
    var list = document.querySelector('[data-inv-glossary-list]');
    var empty = document.querySelector('[data-inv-glossary-empty]');
    var counter = document.querySelector('[data-inv-glossary-count]');
    if (!input || !list) return;

    var items = Array.prototype.slice.call(list.querySelectorAll('.inv-glossary-item'));

    var apply = function () {
        var term = input.value.trim().toLowerCase();
        var visible = 0;
        items.forEach(function (item) {
            var text = (item.getAttribute('data-name') || '').toLowerCase();
            var show = term === '' || text.indexOf(term) !== -1;
            item.hidden = !show;
            if (show) visible++;
        });
        if (empty) empty.hidden = visible !== 0;
        if (counter) counter.textContent = visible + ' اصطلاح';
    };

    input.addEventListener('input', apply);
    input.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') { input.value = ''; apply(); }
    });
})();
</script>
