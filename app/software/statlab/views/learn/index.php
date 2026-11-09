<?php
$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
$url = static fn(string $query): string => stat_url($query);
?>
<main class="sl-learn" dir="rtl">
    <header class="sl-learn-hero">
        <div><span class="sl-learn-kicker"><i class="fas fa-graduation-cap" aria-hidden="true"></i> آموزش گام‌به‌گام آمار</span><h1>مرکز آموزش StatLab</h1><p>از شناخت داده تا انتخاب آزمون و گزارش نتیجه؛ همراه با مثال، فرمول و تمرین.</p></div>
        <div class="sl-learn-hero__stats"><span><strong><?= (int) $totalLessons ?></strong> درس</span><span><strong><?= (int) $glossaryCount ?></strong> اصطلاح</span><span>محاسبه‌گرهای آموزشی</span></div>
    </header>
    <nav class="sl-learn-actions" aria-label="بخش‌های آموزش">
        <a class="is-active" href="<?= $h($url('controller=learn')) ?>"><i class="fas fa-book-open"></i> درس‌ها</a>
        <a href="<?= $h($url('controller=learn&action=glossary')) ?>"><i class="fas fa-spell-check"></i> واژه‌نامه</a>
        <a href="<?= $h($url('controller=learn&action=tools')) ?>"><i class="fas fa-calculator"></i> محاسبه‌گرها</a>
        <a href="<?= $h($url('controller=smart_statistician')) ?>"><i class="fas fa-wand-magic-sparkles"></i> دستیار آماری</a>
    </nav>
    <section class="sl-learn-filter" aria-label="فیلتر درس‌ها">
        <span>سطح آموزش:</span>
        <a class="<?= $levelFilter === 'all' ? 'is-active' : '' ?>" href="<?= $h($url('controller=learn')) ?>">همه</a>
        <?php foreach ($levels as $key => $label): ?><a class="<?= $levelFilter === $key ? 'is-active' : '' ?>" href="<?= $h($url('controller=learn&level=' . $key)) ?>"><?= $h($label) ?></a><?php endforeach; ?>
    </section>
    <section class="sl-learn-grid" aria-label="فهرست درس‌ها">
        <?php foreach ($lessons as $i => $lesson): ?>
            <a class="sl-learn-card" href="<?= $h($url('controller=learn&action=show&lesson=' . rawurlencode($lesson['id']))) ?>">
                <span class="sl-learn-card__number"><?= sprintf('%02d', $i + 1) ?></span>
                <span class="sl-learn-card__icon"><i class="<?= $h($lesson['icon']) ?>" aria-hidden="true"></i></span>
                <span class="sl-learn-card__body"><span class="sl-learn-card__meta"><?= $h($levels[$lesson['level']] ?? $lesson['level']) ?> · <?= (int) $lesson['minutes'] ?> دقیقه</span><strong><?= $h($lesson['title']) ?></strong><span><?= $h($lesson['summary']) ?></span></span>
                <i class="fas fa-arrow-left sl-learn-card__arrow" aria-hidden="true"></i>
            </a>
        <?php endforeach; ?>
    </section>
    <aside class="sl-learn-note"><i class="fas fa-circle-info" aria-hidden="true"></i><p>محتوای آموزشی برای یادگیری و مرور روش‌هاست. انتخاب نهایی آزمون به طرح نمونه‌گیری، کیفیت داده و فرض‌های مسئله وابسته است.</p></aside>
</main>
