<?php
/**
 * پیش‌فاکتور سازمانی IT4IE
 */

$invoice = $invoice ?? [];

$paymentId = (int)($invoice['id'] ?? 0);

$planName = trim(
    (string)($invoice['plan_name'] ?? 'طرح سازمانی')
);

$amount = (int)($invoice['amount'] ?? 0);

$period = ($invoice['period'] ?? '') === 'yearly'
    ? 'سالانه (۱۲ ماه)'
    : 'ماهانه';

$statusLabels = [
    'awaiting_contact' => [
        'text'  => 'در انتظار بررسی و تماس',
        'class' => 'info'
    ],

    'pending_review' => [
        'text'  => 'در حال بررسی',
        'class' => 'warning'
    ],

    'approved' => [
        'text'  => 'تأیید شده',
        'class' => 'success'
    ],

    'rejected' => [
        'text'  => 'رد شده',
        'class' => 'danger'
    ],
];

$status = $invoice['status'] ?? '';

$statusInfo = $statusLabels[$status] ?? [
    'text'  => $status !== '' ? $status : 'نامشخص',
    'class' => 'secondary'
];

/*
 * اطلاعات سازمان داخل note ذخیره شده است.
 *
 * مثال:
 * شرکت: شرکت نمونه | شناسه ملی: 123 | تماس: 0912...
 */
$note = trim(
    (string)($invoice['note'] ?? '')
);

$company = '';
$nationalId = '';
$contact = '';

if ($note !== '') {

    if (preg_match('/شرکت:\s*(.*?)(?:\s*\|\s*شناسه ملی:|$)/u', $note, $m)) {
        $company = trim($m[1]);
    }

    if (preg_match('/شناسه ملی:\s*(.*?)(?:\s*\|\s*تماس:|$)/u', $note, $m)) {
        $nationalId = trim($m[1]);
    }

    if (preg_match('/تماس:\s*(.*?)(?:\s*\|\s*طرح:|$)/u', $note, $m)) {
        $contact = trim($m[1]);
    }
}

$createdAt = $invoice['created_at'] ?? null;
?>

<div class="profile-page-wrapper">
    <div class="container">

        <nav class="breadcrumb-nav">
            <a href="/">
                <i class="fas fa-home"></i>
                خانه
            </a>

            <span class="separator">/</span>

            <a href="/billing/my">
                اشتراک و پرداخت‌ها
            </a>

            <span class="separator">/</span>

            <span>پیش‌فاکتور سازمانی</span>
        </nav>


        <div class="profile-content" style="max-width: 900px; margin: 0 auto;">

            <div class="profile-card">

                <!-- سربرگ -->
                <div style="
                    display:flex;
                    justify-content:space-between;
                    align-items:flex-start;
                    gap:20px;
                    margin-bottom:30px;
                    padding-bottom:20px;
                    border-bottom:1px solid #eee;
                ">

                    <div>
                        <h1 style="margin:0 0 8px;">
                            <i class="fas fa-file-invoice"></i>
                            پیش‌فاکتور سازمانی
                        </h1>

                        <p style="margin:0;color:#777;">
                            IT4IE
                        </p>
                    </div>

                    <div style="text-align:left;">

                        <div style="margin-bottom:8px;">
                            <strong>
                                شماره درخواست:
                            </strong>

                            #<?= $paymentId ?>
                        </div>

                        <?php if ($createdAt): ?>
                            <div>
                                <strong>
                                    تاریخ:
                                </strong>

                                <?= jdate($createdAt, 'j F Y') ?>
                            </div>
                        <?php endif; ?>

                    </div>

                </div>


                <!-- وضعیت -->
                <div style="
                    margin-bottom:30px;
                    padding:15px 18px;
                    border-radius:10px;
                    background:#f8f9fa;
                ">

                    <strong>
                        وضعیت درخواست:
                    </strong>

                    <span class="status-pill <?= htmlspecialchars($statusInfo['class']) ?>">
                        <?= htmlspecialchars($statusInfo['text']) ?>
                    </span>

                </div>


                <!-- اطلاعات سازمان -->
                <div style="margin-bottom:30px;">

                    <h3 style="margin-bottom:18px;">
                        <i class="fas fa-building"></i>
                        اطلاعات سازمان
                    </h3>

                    <div class="info-grid">

                        <div class="info-item">
                            <label>نام شرکت</label>

                            <p>
                                <?= htmlspecialchars(
                                    $company !== '' ? $company : '-'
                                ) ?>
                            </p>
                        </div>

                        <div class="info-item">
                            <label>شناسه ملی</label>

                            <p dir="ltr">
                                <?= htmlspecialchars(
                                    $nationalId !== '' ? $nationalId : '-'
                                ) ?>
                            </p>
                        </div>

                        <div class="info-item">
                            <label>شماره تماس</label>

                            <p dir="ltr">
                                <?= htmlspecialchars(
                                    $contact !== '' ? $contact : '-'
                                ) ?>
                            </p>
                        </div>

                        <div class="info-item">
                            <label>ایمیل</label>

                            <p dir="ltr">
                                <?= htmlspecialchars(
                                    $invoice['user_email'] ?? '-'
                                ) ?>
                            </p>
                        </div>

                    </div>

                </div>


                <!-- جزئیات خرید -->
                <div style="margin-bottom:30px;">

                    <h3 style="margin-bottom:18px;">
                        <i class="fas fa-shopping-cart"></i>
                        جزئیات اشتراک
                    </h3>

                    <div class="table-responsive">

                        <table class="billing-table">

                            <thead>
                                <tr>
                                    <th>شرح</th>
                                    <th>مدت</th>
                                    <th>مبلغ</th>
                                </tr>
                            </thead>

                            <tbody>

                                <tr>

                                    <td>
                                        اشتراک
                                        <?= htmlspecialchars($planName) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($period) ?>
                                    </td>

                                    <td>
                                        <strong>
                                            <?= number_format($amount) ?>
                                        </strong>

                                        <small>
                                            تومان
                                        </small>
                                    </td>

                                </tr>

                            </tbody>

                            <tfoot>

                                <tr>

                                    <td colspan="2">
                                        <strong>
                                            مبلغ کل
                                        </strong>
                                    </td>

                                    <td>
                                        <strong style="font-size:20px;">
                                            <?= number_format($amount) ?>
                                        </strong>

                                        <small>
                                            تومان
                                        </small>
                                    </td>

                                </tr>

                            </tfoot>

                        </table>

                    </div>

                </div>


                <!-- توضیح -->
                <div style="
                    padding:18px;
                    background:#f8f9fa;
                    border-radius:10px;
                    line-height:2;
                    margin-bottom:30px;
                ">

                    <strong>
                        توضیحات:
                    </strong>

                    <p style="margin:8px 0 0;">
                        این صفحه تأیید درخواست پیش‌فاکتور سازمانی
                        شما برای اشتراک
                        <?= htmlspecialchars($planName) ?>
                        است.
                        مدت اشتراک این درخواست
                        <?= htmlspecialchars($period) ?>
                        است.
                    </p>

                    <?php if ($status === 'awaiting_contact'): ?>

                        <p style="margin:8px 0 0;">
                            کارشناسان IT4IE پس از بررسی اطلاعات سازمان
                            برای هماهنگی ادامه فرآیند با شما تماس خواهند گرفت.
                        </p>

                    <?php elseif ($status === 'approved'): ?>

                        <p style="margin:8px 0 0;">
                            این درخواست توسط مدیریت تأیید شده است.
                        </p>

                    <?php elseif ($status === 'rejected'): ?>

                        <p style="margin:8px 0 0;">
                            این درخواست تأیید نشده است.
                            در صورت نیاز با پشتیبانی تماس بگیرید.
                        </p>

                    <?php endif; ?>

                </div>


                <!-- عملیات -->
                <div style="
                    display:flex;
                    gap:10px;
                    flex-wrap:wrap;
                ">

                    <a
                        href="/billing/my"
                        class="btn-plan ghost"
                    >
                        <i class="fas fa-arrow-right"></i>
                        بازگشت به تاریخچه
                    </a>

                    <?php if ($status === 'awaiting_contact'): ?>

                        <a
                            href="/contact"
                            class="btn-plan primary"
                        >
                            <i class="fas fa-headset"></i>
                            تماس با پشتیبانی
                        </a>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>
</div>