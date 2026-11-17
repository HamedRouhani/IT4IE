<?php
$payload = $template['payload'] ?? [];
$preview = $payload['preview'] ?? [];
$columns = is_array($preview['columns'] ?? null) ? $preview['columns'] : [];
$rows = is_array($preview['rows'] ?? null) ? $preview['rows'] : [];
$steps = is_array($payload['steps'] ?? null) ? $payload['steps'] : [];
$method = $template['method_code'] ?? '';
?>
<section class="research-library research-detail-page" dir="rtl">
    <nav class="research-breadcrumb"><a href="/research">کتابخانهٔ نمونه‌ها</a><i class="fa-solid fa-chevron-left" aria-hidden="true"></i><span><?= htmlspecialchars($template['title'], ENT_QUOTES, 'UTF-8') ?></span></nav>
    <article class="research-detail">
        <header class="research-detail-header">
            <span class="research-eyebrow"><i class="fa-solid fa-book-open" aria-hidden="true"></i> <?= htmlspecialchars($toolName, ENT_QUOTES, 'UTF-8') ?> · <?= htmlspecialchars($method, ENT_QUOTES, 'UTF-8') ?></span>
            <h1><?= htmlspecialchars($template['title'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="research-lead"><?= htmlspecialchars($template['summary'], ENT_QUOTES, 'UTF-8') ?></p>
            <div class="research-detail-meta"><span><i class="fa-regular fa-clock"></i> حدود <?= (int)($template['estimated_minutes'] ?? 15) ?> دقیقه</span><span><i class="fa-solid fa-signal"></i> <?= htmlspecialchars(['beginner'=>'مقدماتی','intermediate'=>'متوسط','advanced'=>'پیشرفته'][$template['difficulty'] ?? 'beginner'] ?? 'مقدماتی', ENT_QUOTES, 'UTF-8') ?></span><span><i class="fa-solid fa-database"></i> داده و فرض آموزشی</span></div>
        </header>
        <?php if (!empty($payload['problem_statement'])): ?><section class="research-detail-section"><h2><i class="fa-solid fa-bullseye"></i> مسئله</h2><p><?= nl2br(htmlspecialchars($payload['problem_statement'], ENT_QUOTES, 'UTF-8')) ?></p></section><?php endif; ?>
        <?php if ($columns && $rows): ?><section class="research-detail-section"><div class="research-section-heading"><div><p class="research-eyebrow">پیش‌نمایش داده</p><h2>داده‌ای که در پروژه ساخته می‌شود</h2></div><span class="research-count"><?= count($rows) ?> ردیف</span></div><div class="research-table-wrap"><table class="research-data-table"><thead><tr><?php foreach($columns as $column): ?><th><?= htmlspecialchars((string)$column, ENT_QUOTES, 'UTF-8') ?></th><?php endforeach; ?></tr></thead><tbody><?php foreach($rows as $row): ?><tr><?php foreach($columns as $i=>$_): ?><td><?= htmlspecialchars((string)($row[$i] ?? ''), ENT_QUOTES, 'UTF-8') ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></div></section><?php endif; ?>
        <?php if ($steps): ?><section class="research-detail-section"><h2><i class="fa-solid fa-list-check"></i> مسیر پیشنهادی</h2><ol class="research-steps"><?php foreach($steps as $step): ?><li><?= htmlspecialchars((string)$step, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ol></section><?php endif; ?>
        <?php if (!empty($payload['interpretation_note'])): ?><aside class="research-note"><i class="fa-solid fa-lightbulb" aria-hidden="true"></i><span><?= htmlspecialchars($payload['interpretation_note'], ENT_QUOTES, 'UTF-8') ?></span></aside><?php endif; ?>
        <div class="research-detail-actions"><form method="post" action="/research/<?= rawurlencode($template['slug']) ?>"><?= $csrfField ?? '' ?><button class="research-button research-button-primary" type="submit"><i class="fa-solid fa-folder-plus" aria-hidden="true"></i> <?= !empty($isLoggedIn) ? 'ساخت پروژه از این نمونه' : 'ورود و ساخت نمونه در ماژول' ?></button></form><a class="research-button research-button-secondary" href="/research">بازگشت به نمونه‌ها</a></div>
        <p class="research-use-note"><i class="fa-solid fa-circle-info"></i> با اجرا، یک پروژهٔ مستقل و قابل ویرایش در ماژول مربوط ایجاد می‌شود. داده‌ها ساختگی و آموزشی‌اند؛ برای پژوهش یا تصمیم واقعی، داده‌ها و فرض‌های خود را جایگزین کنید.</p>
    </article>
</section>
