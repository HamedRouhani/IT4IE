<?php
// views/billing/pay.php
// مسیر: views/billing/pay.php (پوشه billing را بسازید)
$planName = '';
try {
    $planModel = new \App\Models\Subscription();
    $plan = $planModel->getPlan($payment['plan_id'] ?? 0);
    $planName = $plan['name'] ?? '';
} catch (\Throwable $e) {}

$statusText = [
    'awaiting_ref'     => 'در انتظار ثبت کد پیگیری',
    'awaiting_contact' => 'در انتظار ثبت پرداخت سازمان',
    'pending_review'   => 'در حال بررسی توسط کارشناس',
    'approved'         => 'تأیید و فعال شده',
    'rejected'         => 'رد شده',
];

$isInvoice = ($payment['method'] ?? '') === 'invoice';
?>

<section class="pricing-page">
    <div class="container" style="max-width: 680px;">
        <div class="pay-wrapper">

            <!-- مرحله‌نما -->
            <div class="pay-steps">
                <div class="step completed">
                    <div class="step-num">✓</div>
                    <span>انتخاب طرح</span>
                </div>
                <div class="step active">
                    <div class="step-num">۲</div>
                    <span>واریز وجه</span>
                </div>
                <div class="step">
                    <div class="step-num">۳</div>
                    <span>فعال‌سازی</span>
                </div>
            </div>

            <div class="pricing-card pay-card">
                <h3>💳 تکمیل پرداخت طرح <?= htmlspecialchars($planName) ?></h3>

                <?php if (
                    $payment['status'] === 'awaiting_ref' ||
                    (
                        ($payment['method'] ?? '') === 'invoice' &&
                        $payment['status'] === 'awaiting_contact'
                    )
                ): ?>

                    <div class="pay-alert">
                        <i class="fas fa-info-circle"></i>

                        <?php if ($isInvoice): ?>

                            <span>
                                مبلغ زیر را دقیقاً به حساب اعلام‌شده واریز کنید.
                                پس از واریز، کد پیگیری تراکنش را در همین صفحه ثبت کنید.
                            </span>

                        <?php else: ?>

                            <span>
                                مبلغ زیر را دقیقاً به کارت اعلام‌شده واریز کنید،
                                سپس کد پیگیری تراکنش را ثبت کنید.
                            </span>

                        <?php endif; ?>
                    </div>

                    <div class="pay-box">
                        <div class="pay-row highlight">
                            <span>مبلغ قابل واریز:</span>
                            <strong class="pay-amount"><?= number_format($payment['expected_amount']) ?> <small>تومان</small></strong>
                        </div>
                        <div class="pay-row">
                            <span>شماره کارت:</span>
                            <strong dir="ltr" class="pay-card-num"><?= htmlspecialchars($settings['payment_card_number'] ?? '-') ?></strong>
                        </div>
                        <div class="pay-row">
                            <span>به نام:</span>
                            <strong><?= htmlspecialchars($settings['payment_card_holder'] ?? '-') ?></strong>
                        </div>
                        <?php if (!empty($settings['payment_note'])): ?>
                            <p class="pay-note">
                                <i class="fas fa-lightbulb"></i>
                                <?= htmlspecialchars($settings['payment_note']) ?>
                            </p>
                        <?php endif; ?>

                        <button type="button" class="copy-btn" onclick="copyCardNumber()">
                            <i class="fas fa-copy"></i> کپی شماره کارت
                        </button>
                    </div>

                    <form method="POST" action="/billing/submit-payment" class="pay-form">
                        <input type="hidden" name="payment_id" value="<?= $payment['id'] ?>">

                        <div class="form-group">
                            <label>کد پیگیری تراکنش <span class="required">*</span></label>
                            <input type="text" name="ref_code" required inputmode="numeric"
                                   minlength="8" maxlength="20"
                                   placeholder="مثلاً 7412589630" dir="ltr">
                            <small>عدد ۸ تا ۲۰ رقمی که در رسید بانک ثبت شده است</small>
                        </div>

                        <div class="form-group">
                            <label>۴ رقم آخر کارت شما (اختیاری)</label>
                            <input type="text" name="payer_card" maxlength="4" inputmode="numeric"
                                   placeholder="1234" dir="ltr">
                        </div>

                        <div class="form-group">
                            <label>توضیح (اختیاری)</label>
                            <input type="text" name="note" placeholder="مثلاً واریز از بانک ملت">
                        </div>

                        <button type="submit" class="btn-plan primary submit-btn">
                            <i class="fas fa-paper-plane"></i>

                            <?php if ($isInvoice): ?>
                                ثبت پرداخت پیش‌فاکتور و ارسال برای بررسی
                            <?php else: ?>
                                ثبت پرداخت و ارسال برای بررسی
                            <?php endif; ?>

                        </button>
                    </form>

                <?php elseif ($payment['status'] === 'pending_review'): ?>

                    <div class="pay-status-box review">
                        <i class="fas fa-hourglass-half"></i>

                        <?php if ($isInvoice): ?>

                            <h4>پرداخت پیش‌فاکتور شما در حال بررسی است</h4>

                            <p>
                                کد پیگیری
                                <strong dir="ltr">
                                    <?= htmlspecialchars($payment['ref_code']) ?>
                                </strong>
                                ثبت شد.
                            </p>

                            <p>
                                پس از تأیید پرداخت، اشتراک سازمانی شما فعال خواهد شد.
                            </p>

                        <?php else: ?>

                            <h4>پرداخت شما در صف بررسی است</h4>

                            <p>
                                کد پیگیری
                                <strong dir="ltr">
                                    <?= htmlspecialchars($payment['ref_code']) ?>
                                </strong>
                                ثبت شد.
                            </p>

                            <p>
                                پس از تأیید پرداخت، اشتراک شما فعال خواهد شد.
                            </p>

                        <?php endif; ?>

                        <a href="/billing/my"
                        class="btn-plan ghost"
                        style="margin-top:16px;">
                            مشاهده وضعیت اشتراک
                        </a>
                    </div>

                <?php else: ?>

                    <div class="pay-status-box <?= $payment['status'] === 'approved' ? 'success' : 'error' ?>">
                        <i class="fas fa-<?= $payment['status'] === 'approved' ? 'check-circle' : 'times-circle' ?>"></i>
                        <h4><?= htmlspecialchars($statusText[$payment['status']] ?? $payment['status']) ?></h4>
                        <a href="/billing/my" class="btn-plan ghost" style="margin-top:16px;">اشتراک من</a>
                    </div>

                <?php endif; ?>
            </div>

        </div>
    </div>
</section>

<script>
function copyCardNumber() {
    const cardNum = '<?= htmlspecialchars($settings['payment_card_number'] ?? '') ?>'.replace(/-/g, '');
    navigator.clipboard.writeText(cardNum).then(() => {
        const btn = document.querySelector('.copy-btn');
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> کپی شد!';
        setTimeout(() => btn.innerHTML = original, 2000);
    });
}
</script>