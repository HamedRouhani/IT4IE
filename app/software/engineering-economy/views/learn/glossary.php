<section class="ee-page" dir="rtl">
    <header class="ee-page-head">
        <div>
            <span class="ee-eyebrow">مرجع سریع</span>
            <h1><i class="fa-solid fa-book"></i> واژه‌نامهٔ اقتصاد مهندسی</h1>
            <p><?= count($terms) ?> اصطلاح کلیدی با معادل فارسی و تعریف کوتاه؛ جست‌وجو در نام انگلیسی یا فارسی کار می‌کند.</p>
        </div>
        <div class="ee-head-actions">
            <a class="ee-button" href="<?= ee_e(ee_url('learn', 'index')) ?>">درس‌ها</a>
            <a class="ee-button ee-button-primary" href="<?= ee_e(ee_url('learn', 'tools')) ?>"><i class="fa-solid fa-calculator"></i> ماشین‌حساب‌ها</a>
        </div>
    </header>

    <section class="ee-card">
        <div class="ee-card-head">
            <div><span class="ee-eyebrow">اصطلاحات</span><h2>جست‌وجو در واژه‌ها</h2></div>
            <span class="ee-tag" data-glossary-count><?= count($terms) ?> اصطلاح</span>
        </div>

        <label class="ee-search" for="glossary-search">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input id="glossary-search" type="search" placeholder="جست‌وجوی اصطلاح…" data-glossary-search autocomplete="off">
        </label>

        <div class="ee-glossary" data-glossary-list>
            <?php foreach ($terms as $term): ?>
                <?php $name = mb_strtolower($term['term'] . ' ' . $term['fa'] . ' ' . $term['def'], 'UTF-8'); ?>
                <article class="ee-glossary-item" data-name="<?= ee_e($name) ?>">
                    <div class="ee-glossary-head">
                        <strong dir="ltr"><?= ee_e($term['term']) ?></strong>
                        <span><?= ee_e($term['fa']) ?></span>
                    </div>
                    <p><?= ee_e($term['def']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <p class="ee-nomatch" data-glossary-empty hidden>اصطلاحی با این عبارت پیدا نشد.</p>
    </section>

    <div class="ee-note">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <strong>یادآوری</strong>
            <p style="margin:4px 0 0;line-height:1.9;">تعریف‌ها کوتاه و کاربردی‌اند؛ برای فرض‌ها و فرمول‌های دقیق هر مفهوم، درس مربوط در بخش «درس‌ها» را ببینید.</p>
        </div>
    </div>
</section>

<script>
(function () {
    'use strict';
    var input = document.querySelector('[data-glossary-search]');
    var list = document.querySelector('[data-glossary-list]');
    var empty = document.querySelector('[data-glossary-empty]');
    var counter = document.querySelector('[data-glossary-count]');
    if (!input || !list) return;

    var items = Array.prototype.slice.call(list.querySelectorAll('.ee-glossary-item'));

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
