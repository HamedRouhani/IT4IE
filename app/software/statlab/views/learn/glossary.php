<?php
$h = static fn($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<main class="sl-learn" dir="rtl">
    <header class="sl-learn-hero"><div><span class="sl-learn-kicker">مرجع سریع</span><h1>واژه‌نامه آماری</h1><p>تعریف اصطلاحات پرکاربرد در تحلیل‌های StatLab.</p></div><a class="sl-learn-button" href="<?= $h(stat_url('controller=learn')) ?>"><i class="fas fa-arrow-right"></i> بازگشت به درس‌ها</a></header>
    <nav class="sl-learn-actions"><a href="<?= $h(stat_url('controller=learn')) ?>"><i class="fas fa-book-open"></i> درس‌ها</a><a class="is-active" href="<?= $h(stat_url('controller=learn&action=glossary')) ?>"><i class="fas fa-spell-check"></i> واژه‌نامه</a><a href="<?= $h(stat_url('controller=learn&action=tools')) ?>"><i class="fas fa-calculator"></i> محاسبه‌گرها</a></nav>
    <section class="sl-learn-glossary"><?php foreach ($terms as $term): ?><article><h2><?= $h($term['term']) ?></h2><p><?= $h($term['definition']) ?></p></article><?php endforeach; ?></section>
</main>
