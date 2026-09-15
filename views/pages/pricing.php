<?php
// views/pages/pricing.php
$pageTitle = 'تعرفه‌ها و بسته‌ها - IT4IE';
?>

<section class="pricing-page">
    <div class="container">

        <!-- Hero -->
        <div class="pricing-hero">
            <span class="pricing-badge">💎 طرح‌های عضویت</span>
            <h1>تعرفه‌های <span class="text-gradient">IT4IE</span></h1>
            <p>از ارزیابی رایگان شروع کنید؛ وقتی به قدرت کامل ابزارها نیاز داشتید، ارتقا دهید.</p>
        </div>

        <!-- Grid تعرفه‌ها -->
        <div class="pricing-grid">
            <?php foreach ($plans as $plan):
                $features = json_decode($plan['features'] ?? '[]', true) ?: [];
                $isCurrent = $currentSub && (int)$currentSub['plan_id'] === (int)$plan['id'];
                $yearlySave = $plan['price_monthly'] > 0 && $plan['price_yearly'] > 0
                    ? round((1 - ($plan['price_yearly'] / ($plan['price_monthly'] * 12))) * 100) : 0;
            ?>
            <article class="pricing-card <?= $plan['is_featured'] ? 'featured' : '' ?>">
                <?php if ($plan['is_featured']): ?>
                    <span class="plan-badge">⭐ پیشنهاد ما</span>
                <?php endif; ?>

                <h3><?= htmlspecialchars($plan['name']) ?></h3>
                <p class="plan-desc"><?= htmlspecialchars($plan['description'] ?? '') ?></p>

                <div class="plan-price">
                    <?php if ($plan['price_monthly'] > 0): ?>
                        <span class="price-value"><?= number_format($plan['price_monthly']) ?></span>
                        <small>تومان / ماه</small>
                    <?php else: ?>
                        <span class="price-value">رایگان</span>
                        <small>برای همیشه</small>
                    <?php endif; ?>
                </div>

                <ul class="plan-features">
                    <?php foreach ($features as $f): ?>
                    <li>
                        <i class="fas fa-check-circle"></i>
                        <span><?= htmlspecialchars($f) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <?php if ($isCurrent): ?>
                    <span class="btn-plan current">
                        <i class="fas fa-check"></i> طرح فعلی شما
                    </span>
                <?php elseif ($plan['price_monthly'] <= 0): ?>
                    <form method="POST" action="/billing/subscribe/<?= $plan['id'] ?>">
                        <button type="submit" name="period" value="monthly" class="btn-plan">
                            <i class="fas fa-rocket"></i> شروع رایگان
                        </button>
                    </form>
                <?php else: ?>
                    <form method="POST" action="/billing/subscribe/<?= $plan['id'] ?>" class="plan-form">
                        <button type="submit" name="period" value="monthly" class="btn-plan <?= $plan['is_featured'] ? 'primary' : '' ?>">
                            اشتراک ماهانه
                        </button>
                        <?php if ($plan['price_yearly'] > 0): ?>
                        <button type="submit" name="period" value="yearly" class="btn-plan ghost">
                            سالانه <?= number_format($plan['price_yearly']) ?>
                            <?php if ($yearlySave > 0): ?>
                                <span class="save-badge">-<?= $yearlySave ?>٪</span>
                            <?php endif; ?>
                        </button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </article>
            <?php endforeach; ?>
        </div>

        <!-- کارت‌های مکمل -->
        <div class="pricing-extra">
            <div class="extra-card">
                <div class="extra-icon"><i class="fas fa-ticket-alt"></i></div>
                <h4>کد اشتراک دارید؟</h4>
                <p>کد دریافتی از طریق تلگرام یا فروش سازمانی را اینجا فعال کنید.</p>
                <form method="POST" action="/billing/redeem">
                    <input type="text" name="code" placeholder="IT4IE-1M-XXXXXX" required dir="ltr">
                    <button type="submit" class="btn-plan">فعال‌سازی کد</button>
                </form>
            </div>

            <div class="extra-card">
                <div class="extra-icon"><i class="fas fa-building"></i></div>
                <h4>خرید سازمانی</h4>
                <p>پیش‌فاکتور رسمی + پرداخت حواله‌ای برای شرکت‌ها و سازمان‌ها.</p>
                <form method="POST" action="/billing/invoice">
                    <input type="hidden" name="plan_id" value="3">
                    <input type="hidden" name="period" value="yearly">
                    <input type="text" name="company" placeholder="نام شرکت" required>
                    <input type="text" name="national_id" placeholder="شناسه ملی" required dir="ltr">
                    <input type="text" name="contact" placeholder="شماره تماس" required dir="ltr">
                    <button type="submit" class="btn-plan primary">
                        <i class="fas fa-file-invoice"></i> درخواست پیش‌فاکتور
                    </button>
                </form>
            </div>
        </div>

        <!-- اعتمادسازی -->
        <div class="pricing-trust">
            <div class="trust-item">
                <i class="fas fa-shield-alt"></i>
                <span>پرداخت امن با تأیید دستی</span>
            </div>
            <div class="trust-item">
                <i class="fas fa-bolt"></i>
                <span>فعال‌سازی زیر ۲ ساعت</span>
            </div>
            <div class="trust-item">
                <i class="fas fa-headset"></i>
                <span>پشتیبانی درون‌برنامه‌ای</span>
            </div>
            <div class="trust-item">
                <i class="fas fa-undo"></i>
                <span>ضمانت بازگشت ۷ روزه</span>
            </div>
        </div>

    </div>
</section>