<?php
// views/profile/billing.php - نسخه تمیز بدون استایل
$statusLabels = [

    'awaiting_ref' => [
        'متن'  => 'در انتظار ثبت کد پیگیری',
        'رنگ' => 'warning'
    ],

    'pending_review' => [
        'متن'  => 'در حال بررسی',
        'رنگ' => 'info'
    ],

    'approved' => [
        'متن'  => 'تأیید شده',
        'رنگ' => 'success'
    ],

    'rejected' => [
        'متن'  => 'رد شده',
        'رنگ' => 'danger'
    ],

    'awaiting_contact' => [
        'متن'  => 'در انتظار بررسی پیش‌فاکتور',
        'رنگ' => 'info'
    ],
];
$methodLabels = [
    'card_transfer' => 'کارت‌به‌کارت',
    'voucher'       => 'کد اشتراک',
    'invoice'       => 'پیش‌فاکتور سازمانی',
];
$daysLeft = $currentSub ? ceil((strtotime($currentSub['expires_at']) - time()) / 86400) : 0;
?>

<div class="profile-page-wrapper">
    <div class="container">

        <!-- Breadcrumb -->
        <nav class="breadcrumb-nav">
            <a href="/"><i class="fas fa-home"></i> خانه</a>
            <span class="separator">/</span>
            <a href="/profile">پروفایل</a>
            <span class="separator">/</span>
            <span>اشتراک و پرداخت‌ها</span>
        </nav>

        <div class="profile-layout">

            <!-- سایدبار داخلی -->
            <aside class="profile-sidebar">
                <div class="sidebar-card">
                    <div class="sidebar-user">
                        <div class="user-avatar-lg">
                            <?php echo mb_substr($_SESSION['user_name'] ?? 'ک', 0, 1); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'کاربر'); ?></h3>
                        <p><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></p>
                    </div>

                    <nav class="sidebar-menu">
                        <a href="/profile" class="menu-item">
                            <i class="fas fa-user"></i> اطلاعات کاربری
                        </a>
                        <a href="/profile/edit" class="menu-item">
                            <i class="fas fa-edit"></i> ویرایش پروفایل
                        </a>
                        <a href="/profile/password" class="menu-item">
                            <i class="fas fa-key"></i> تغییر رمز عبور
                        </a>
                        <a href="/billing/my" class="menu-item active">
                            <i class="fas fa-credit-card"></i> اشتراک و پرداخت‌ها
                        </a>
                        <a href="/checklist/history" class="menu-item">
                            <i class="fas fa-clipboard-list"></i> تاریخچه چک‌لیست‌ها
                        </a>
                        <a href="/profile/messages" class="menu-item">
                            <i class="fas fa-envelope"></i> پیام‌ها
                        </a>
                        <hr>
                        <a href="/logout" class="menu-item logout">
                            <i class="fas fa-sign-out-alt"></i> خروج
                        </a>
                    </nav>
                </div>
            </aside>

            <!-- محتوای اصلی -->
            <div class="profile-content">

                <!-- کارت اشتراک فعال -->
                <div class="profile-card">
                    <div class="card-header-billing">
                        <h2><i class="fas fa-crown"></i> اشتراک فعلی</h2>
                        <?php if ($currentSub): ?>
                            <span class="status-pill active">فعال</span>
                        <?php else: ?>
                            <span class="status-pill free">رایگان</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($currentSub): ?>
                        <div class="subscription-hero">
                            <div class="sub-info">
                                <h3><?= htmlspecialchars($currentSub['plan_name']) ?></h3>
                                <p class="sub-period">
                                    دوره: <?= $currentSub['period'] === 'yearly' ? 'سالانه' : 'ماهانه' ?>
                                    &nbsp;•&nbsp;
                                    اعتبار تا <?= jdate($currentSub['expires_at'], 'j F Y') ?>
                                </p>
                                <div class="sub-expiry">
                                    <?php if ($daysLeft <= 7): ?>
                                        <span class="expiry-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <?= $daysLeft ?> روز تا پایان اعتبار
                                        </span>
                                    <?php else: ?>
                                        <span class="expiry-normal">
                                            <i class="fas fa-calendar-check"></i>
                                            <?= $daysLeft ?> روز باقی‌مانده
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <a href="/pricing" class="btn-upgrade">
                                <i class="fas fa-arrow-up"></i> ارتقا / تمدید
                            </a>
                        </div>

                        <div class="info-grid">
                            <div class="info-item">
                                <label>سقف ارزیابی چک‌لیست</label>
                                <p><?= $limit > 900 ? 'نامحدود' : $limit . ' مورد در ماه' ?></p>
                            </div>
                            <div class="info-item">
                                <label>استفاده این ماه</label>
                                <div class="progress-inline">
                                    <span><?= $used ?> مورد</span>
                                    <?php if ($limit <= 900): ?>
                                        <div class="progress-bar">
                                            <div style="width: <?= min(100, ($used / max(1, $limit)) * 100) ?>%"></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    <?php else: ?>
                        <div class="free-plan-box">
                            <i class="fas fa-paper-plane"></i>
                            <div>
                                <h4>طرح رایگان</h4>
                                <p>در حال استفاده از <?= $limit ?> ارزیابی چک‌لیست در ماه.</p>
                                <p class="used-info">استفاده این ماه: <strong><?= $used ?></strong> از <?= $limit ?></p>
                            </div>
                            <a href="/pricing" class="btn-plan primary">ارتقا به طرح حرفه‌ای</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- تاریخچه پرداخت‌ها -->
                <div class="profile-card">
                    <h2><i class="fas fa-receipt"></i> تاریخچه پرداخت‌ها و درخواست‌ها</h2>

                    <?php if (empty($payments)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <p>تاکنون پرداختی ثبت نکرده‌اید.</p>
                            <a href="/pricing" class="btn-plan ghost">مشاهده تعرفه‌ها</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="billing-table">
                                <thead>
                                    <tr>
                                        <th>تاریخ</th>
                                        <th>طرح</th>
                                        <th>مبلغ</th>
                                        <th>روش</th>
                                        <th>وضعیت</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($payments as $p):
                                    $s = $statusLabels[$p['status']] ?? ['متن' => $p['status'], 'رنگ' => 'secondary'];
                                ?>
                                    <tr>
                                        <td><?= jdate($p['created_at'], 'j F Y') ?></td>
                                        <td><?= htmlspecialchars($p['plan_name'] ?? '-') ?></td>
                                        <td><?= number_format($p['amount']) ?> <small>تومان</small></td>
                                        <td><?= htmlspecialchars($methodLabels[$p['method']] ?? $p['method']) ?></td>
                                        <td>
                                            <span class="status-pill <?= $s['رنگ'] ?>">
                                                <?= htmlspecialchars($s['متن']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (
                                                ($p['method'] ?? '') === 'invoice'
                                                && ($p['status'] ?? '') === 'awaiting_contact'
                                            ): ?>

                                                <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-start;">

                                                    <a
                                                        href="/billing/invoice/<?= (int)$p['id'] ?>"
                                                        class="btn-mini"
                                                    >
                                                        <i class="fas fa-file-invoice"></i>
                                                        مشاهده پیش‌فاکتور
                                                    </a>

                                                    <a
                                                        href="/billing/pay/<?= (int)$p['id'] ?>"
                                                        class="btn-mini"
                                                    >
                                                        <i class="fas fa-credit-card"></i>
                                                        ثبت پرداخت
                                                    </a>

                                                </div>

                                            <?php elseif (
                                                ($p['method'] ?? '') === 'invoice'
                                                && ($p['status'] ?? '') === 'pending_review'
                                            ): ?>

                                                <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-start;">

                                                    <a
                                                        href="/billing/invoice/<?= (int)$p['id'] ?>"
                                                        class="btn-mini"
                                                    >
                                                        <i class="fas fa-file-invoice"></i>
                                                        مشاهده پیش‌فاکتور
                                                    </a>

                                                    <span class="text-muted">
                                                        <i class="fas fa-clock"></i>
                                                        در انتظار تأیید پرداخت
                                                    </span>

                                                </div>

                                            <?php elseif (
                                                ($p['method'] ?? '') === 'invoice'
                                                && ($p['status'] ?? '') === 'approved'
                                            ): ?>

                                                <div style="display:flex; flex-direction:column; gap:6px; align-items:flex-start;">

                                                    <a
                                                        href="/billing/invoice/<?= (int)$p['id'] ?>"
                                                        class="btn-mini"
                                                    >
                                                        <i class="fas fa-file-invoice"></i>
                                                        مشاهده پیش‌فاکتور
                                                    </a>

                                                    <?php if (!empty($p['ref_code'])): ?>
                                                        <span class="ref-code" dir="ltr">
                                                            #<?= htmlspecialchars($p['ref_code']) ?>
                                                        </span>
                                                    <?php endif; ?>

                                                </div>

                                            <?php elseif (($p['status'] ?? '') === 'awaiting_ref'): ?>

                                                <a
                                                    href="/billing/pay/<?= (int)$p['id'] ?>"
                                                    class="btn-mini"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                    ثبت کد
                                                </a>

                                            <?php elseif (
                                                ($p['status'] ?? '') === 'approved'
                                                && !empty($p['ref_code'])
                                            ): ?>

                                                <span class="ref-code" dir="ltr">
                                                    #<?= htmlspecialchars($p['ref_code']) ?>
                                                </span>

                                            <?php else: ?>

                                                <span class="text-muted">-</span>

                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>