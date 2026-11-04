<section class="ts-page" dir="rtl">
    <header class="ts-page-head">
        <div>
            <span class="ts-eyebrow">مرجع سریع</span>
            <h1><i class="fa-solid fa-book"></i> واژه‌نامهٔ مطالعهٔ کار</h1>
            <p><?= count($terms) ?> اصطلاح کلیدی با معادل فارسی و تعریف کوتاه؛ جست‌وجو در نام انگلیسی یا فارسی کار می‌کند.</p>
        </div>
        <div class="ts-head-actions">
            <a class="ts-button ts-button-outline" href="<?= ts_e(ts_url('learn', 'index')) ?>">درس‌ها</a>
            <a class="ts-button ts-button-primary" href="<?= ts_e(ts_url('learn', 'tools')) ?>"><i class="fa-solid fa-calculator"></i> ماشین‌حساب‌ها</a>
        </div>
    </header>

    <section class="ts-card">
        <div class="ts-card-head">
            <div><span class="ts-eyebrow">اصطلاحات</span><h2>جست‌وجو در واژه‌ها</h2></div>
            <span class="ts-status active" data-ts-glossary-count><?= count($terms) ?> اصطلاح</span>
        </div>

        <label class="ts-search" for="ts-glossary-search">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input id="ts-glossary-search" type="search" placeholder="جست‌وجوی اصطلاح…" data-ts-glossary-search autocomplete="off">
        </label>

        <div class="ts-glossary" data-ts-glossary-list>
            <?php foreach ($terms as $term): ?>
                <?php $name = mb_strtolower($term['term'] . ' ' . $term['fa'] . ' ' . $term['def'], 'UTF-8'); ?>
                <article class="ts-glossary-item" data-name="<?= ts_e($name) ?>">
                    <div class="ts-glossary-head">
                        <strong dir="ltr"><?= ts_e($term['term']) ?></strong>
                        <span><?= ts_e($term['fa']) ?></span>
                    </div>
                    <p><?= ts_e($term['def']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>

        <p class="ts-nomatch" data-ts-glossary-empty hidden>اصطلاحی با این عبارت پیدا نشد.</p>
    </section>

    <div class="ts-note ts-note-compact">
        <i class="fa-solid fa-circle-info"></i>
        <div>
            <strong>یادآوری</strong>
            <p style="margin:4px 0 0;line-height:1.9;">تعریف‌ها کوتاه و کاربردی‌اند؛ برای فرمول و مثال دقیق هر مفهوم، درس مربوط را در بخش «درس‌ها» ببینید.</p>
        </div>
    </div>
</section>

<script>
(function () {
    'use strict';
    var input = document.querySelector('[data-ts-glossary-search]');
    var list = document.querySelector('[data-ts-glossary-list]');
    var empty = document.querySelector('[data-ts-glossary-empty]');
    var counter = document.querySelector('[data-ts-glossary-count]');
    if (!input || !list) return;

    var items = Array.prototype.slice.call(list.querySelectorAll('.ts-glossary-item'));

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
