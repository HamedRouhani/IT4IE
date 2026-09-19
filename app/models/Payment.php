<?php
namespace App\Models;

use App\Core\Model;

class Payment extends Model
{
    protected $table = 'payments';

    // ============================================
    // پرداخت کارت‌به‌کارت
    // ============================================
    public function createForSubscription($userId, $subscriptionId, $amount)
    {
        $amount = (int)$amount;
        $expected = $amount;

        try {
            $this->query(
                "INSERT INTO {$this->table} 
                (user_id, subscription_id, method, amount, expected_amount, status)
                VALUES (:uid, :sid, 'card_transfer', :amt, :exp, 'awaiting_ref')",
                [
                    ':uid' => (int)$userId,
                    ':sid' => (int)$subscriptionId,
                    ':amt' => $amount,
                    ':exp' => $expected
                ]
            );

            $rows = $this->query("SELECT LAST_INSERT_ID() AS id");

            return is_array($rows)
                ? (int)($rows[0]['id'] ?? 0)
                : 0;

        } catch (\Throwable $e) {
            error_log('Payment::createForSubscription ERROR: ' . $e->getMessage());
            return 0;
        }
    }

    public function getPayment($id)
    {
        $sql = "SELECT p.*, s.period, s.plan_id, u.name AS user_name, u.email AS user_email
                FROM {$this->table} p
                LEFT JOIN subscriptions s ON p.subscription_id = s.id
                LEFT JOIN users u ON p.user_id = u.id
                WHERE p.id = :id";
        $result = $this->query($sql, [':id' => (int)$id]);
        return is_array($result) ? ($result[0] ?? null) : null;
    }

    public function submitRef($paymentId, $refCode, $payerCard, $note)
    {
        try {
            $paymentId = (int)$paymentId;
            $refCode = trim((string)$refCode);
            $payerCard = trim((string)$payerCard);
            $note = trim((string)$note);

            if ($paymentId <= 0 || $refCode === '') {
                return false;
            }

            /*
            * بررسی رکورد پرداخت
            */
            $rows = $this->query(
                "SELECT id, method, status
                FROM {$this->table}
                WHERE id = :id
                LIMIT 1",
                [
                    ':id' => $paymentId
                ]
            );

            if (!is_array($rows) || empty($rows[0])) {
                return false;
            }

            $payment = $rows[0];

            $method = (string)($payment['method'] ?? '');
            $status = (string)($payment['status'] ?? '');

            /*
            * وضعیت‌های مجاز برای ثبت پرداخت
            */
            $allowed =
                ($method === 'card_transfer' && $status === 'awaiting_ref') ||
                ($method === 'invoice' && $status === 'awaiting_contact');

            if (!$allowed) {
                return false;
            }

            /*
            * جلوگیری از استفاده مجدد از کد پیگیری
            */
            $duplicate = $this->query(
                "SELECT id
                FROM {$this->table}
                WHERE ref_code = :ref
                AND id <> :id
                LIMIT 1",
                [
                    ':ref' => $refCode,
                    ':id'  => $paymentId
                ]
            );

            if (is_array($duplicate) && !empty($duplicate)) {
                return false;
            }

            /*
            * ثبت پرداخت
            */
            $this->query(
                "UPDATE {$this->table}
                SET
                    ref_code = :ref,
                    payer_card = :card,
                    note = :note,
                    status = 'pending_review'
                WHERE id = :id",
                [
                    ':ref'  => $refCode,
                    ':card' => $payerCard,
                    ':note' => $note,
                    ':id'   => $paymentId
                ]
            );

            /*
            * اطمینان از ثبت موفق
            */
            $check = $this->query(
                "SELECT id, status, ref_code
                FROM {$this->table}
                WHERE id = :id
                LIMIT 1",
                [
                    ':id' => $paymentId
                ]
            );

            if (!is_array($check) || empty($check[0])) {
                return false;
            }

            return
                ($check[0]['status'] ?? '') === 'pending_review' &&
                (string)($check[0]['ref_code'] ?? '') === $refCode;

        } catch (\Throwable $e) {
            error_log(
                'Payment::submitRef ERROR: ' .
                $e->getMessage()
            );

            return false;
        }
    }

    public function getAllPayments($status = '')
    {
        $sql = "SELECT p.*, u.name AS user_name, u.email AS user_email, pl.name AS plan_name
                FROM {$this->table} p
                LEFT JOIN users u ON p.user_id = u.id
                LEFT JOIN subscriptions s ON p.subscription_id = s.id
                LEFT JOIN plans pl ON s.plan_id = pl.id";
        $params = [];
        if ($status !== '') {
            $sql .= " WHERE p.status = :st";
            $params[':st'] = $status;
        }
        $sql .= " ORDER BY p.created_at DESC LIMIT 200";
        $result = $this->query($sql, $params);
        return is_array($result) ? $result : [];
    }

    public function getUserPayments($userId)
    {
        $sql = "
            SELECT
                p.*,

                s.period,
                s.plan_id,

                pl.name AS plan_name,
                pl.slug AS plan_slug

            FROM {$this->table} p

            LEFT JOIN subscriptions s
                ON p.subscription_id = s.id

            LEFT JOIN plans pl
                ON s.plan_id = pl.id

            WHERE p.user_id = :uid

            ORDER BY p.id DESC

            LIMIT 50
        ";

        $result = $this->query(
            $sql,
            [
                ':uid' => (int)$userId
            ]
        );

        return is_array($result)
            ? $result
            : [];
    }

    public function setStatus($paymentId, $status, $adminId, $adminNote = null)
    {
        try {
            $paymentId = (int)$paymentId;
            $adminId = (int)$adminId;
            $status = trim((string)$status);
            $adminNote = trim((string)$adminNote);

            if ($paymentId <= 0 || $adminId <= 0) {
                return false;
            }

            if (!in_array($status, ['approved', 'rejected'], true)) {
                return false;
            }

            // اطلاعات فعلی پرداخت
            $rows = $this->query(
                "SELECT id, method, status, ref_code
                FROM {$this->table}
                WHERE id = :id
                LIMIT 1",
                [
                    ':id' => $paymentId
                ]
            );

            if (!is_array($rows) || empty($rows[0])) {
                return false;
            }

            $payment = $rows[0];

            $currentStatus = (string)($payment['status'] ?? '');
            $method = (string)($payment['method'] ?? '');
            $refCode = trim((string)($payment['ref_code'] ?? ''));

            /*
            * کارت‌به‌کارت:
            *
            * awaiting_ref
            *      ↓
            * pending_review
            *      ↓
            * approved / rejected
            */
            if ($method === 'card_transfer') {

                if ($currentStatus !== 'pending_review') {
                    return false;
                }

                // پرداخت کارت‌به‌کارت بدون کد پیگیری
                // قابل تأیید نیست.
                if ($status === 'approved' && $refCode === '') {
                    return false;
                }
            }

            /*
            * پیش‌فاکتور سازمانی:
            *
            * awaiting_contact
            *      ↓
            * pending_review
            *      ↓
            * approved / rejected
            *
            * در وضعیت awaiting_contact فقط رد کردن مجاز است.
            * تأیید فقط بعد از ثبت پرداخت و ورود به pending_review مجاز است.
            */
            elseif ($method === 'invoice') {

                if (
                    $currentStatus !== 'pending_review'
                    && $currentStatus !== 'awaiting_contact'
                ) {
                    return false;
                }

                /*
                * درخواست پیش‌فاکتور بدون ثبت پرداخت
                * قابل تأیید نیست.
                */
                if (
                    $currentStatus === 'awaiting_contact'
                    && $status === 'approved'
                ) {
                    return false;
                }

                /*
                * تأیید پیش‌فاکتور فقط با کد پیگیری
                * امکان‌پذیر است.
                */
                if (
                    $currentStatus === 'pending_review'
                    && $status === 'approved'
                    && $refCode === ''
                ) {
                    return false;
                }
            }

            // روش پرداخت ناشناخته
            else {
                return false;
            }

            // تغییر وضعیت فقط در صورت باقی‌بودن همان وضعیت فعلی
            $this->query(
                "UPDATE {$this->table}
                SET
                    status = :status,
                    admin_note = :note,
                    reviewed_by = :admin,
                    reviewed_at = NOW()
                WHERE id = :id
                AND status = :current_status",
                [
                    ':status' => $status,
                    ':note' => $adminNote !== '' ? $adminNote : null,
                    ':admin' => $adminId,
                    ':id' => $paymentId,
                    ':current_status' => $currentStatus
                ]
            );

            // تأیید نهایی نتیجه
            $check = $this->query(
                "SELECT status
                FROM {$this->table}
                WHERE id = :id
                LIMIT 1",
                [
                    ':id' => $paymentId
                ]
            );

            return is_array($check)
                && !empty($check[0])
                && ($check[0]['status'] ?? '') === $status;

        } catch (\Throwable $e) {
            error_log('Payment::setStatus ERROR: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================
    // پیش‌فاکتور سازمانی
    // ============================================

    public function createInvoiceRequest($userId, $subscriptionId, $amount, $note)
    {
        try {
            $amount = (int)$amount;

            if (
                (int)$userId <= 0 ||
                (int)$subscriptionId <= 0 ||
                $amount <= 0
            ) {
                return 0;
            }

            $this->query(
                "INSERT INTO {$this->table}
                (
                    user_id,
                    subscription_id,
                    method,
                    amount,
                    expected_amount,
                    note,
                    status
                )
                VALUES
                (
                    :uid,
                    :sid,
                    'invoice',
                    :amt,
                    :expected,
                    :note,
                    'awaiting_contact'
                )",
                [
                    ':uid'      => (int)$userId,
                    ':sid'      => (int)$subscriptionId,
                    ':amt'      => $amount,
                    ':expected' => $amount,
                    ':note'     => trim((string)$note),
                ]
            );

            $rows = $this->query(
                "SELECT LAST_INSERT_ID() AS id"
            );

            return is_array($rows)
                ? (int)($rows[0]['id'] ?? 0)
                : 0;

        } catch (\Throwable $e) {
            error_log(
                'Payment::createInvoiceRequest ERROR: ' .
                $e->getMessage()
            );

            return 0;
        }
    }


    /**
     * دریافت اطلاعات کامل پیش‌فاکتور
     *
     * فقط Paymentهایی که method آنها invoice است.
     */
    public function getInvoice($paymentId, $userId = null)
    {
        try {

            $sql = "
                SELECT
                    p.*,
                    u.name AS user_name,
                    u.email AS user_email,
                    s.period,
                    s.plan_id,
                    s.status AS subscription_status,
                    pl.name AS plan_name,
                    pl.slug AS plan_slug,
                    pl.description AS plan_description
                FROM {$this->table} p
                LEFT JOIN subscriptions s
                    ON s.id = p.subscription_id
                LEFT JOIN plans pl
                    ON pl.id = s.plan_id
                LEFT JOIN users u
                    ON u.id = p.user_id
                WHERE p.id = :payment_id
                AND p.method = 'invoice'
            ";

            $params = [
                ':payment_id' => (int)$paymentId
            ];

            /*
            * اگر userId ارسال شده باشد،
            * فقط همان کاربر اجازه مشاهده دارد.
            *
            * اگر userId null باشد،
            * متد برای ادمین استفاده می‌شود.
            */
            if ($userId !== null) {
                $sql .= " AND p.user_id = :user_id";
                $params[':user_id'] = (int)$userId;
            }

            $sql .= " LIMIT 1";

            $rows = $this->query($sql, $params);

            return is_array($rows) && !empty($rows)
                ? $rows[0]
                : null;

        } catch (\Throwable $e) {

            error_log(
                'Payment::getInvoice ERROR: ' .
                $e->getMessage()
            );

            return null;
        }
    }

    // ============================================
    // کدهای اشتراک
    // ============================================
    public function generateVouchers($planId, $period, $count, $adminId, $expireDays = 90)
    {
        $codes = [];
        $count = max(1, min(50, (int)$count));
        for ($i = 0; $i < $count; $i++) {
            $code = 'IT4IE-' . (($period === 'yearly') ? '1Y' : '1M') . '-' . strtoupper(bin2hex(random_bytes(3)));
            try {
                $this->query(
                    "INSERT INTO vouchers (code, plan_id, period, expires_at, created_by)
                     VALUES (:code, :pid, :period, DATE_ADD(NOW(), INTERVAL :days DAY), :admin)",
                    [':code' => $code, ':pid' => (int)$planId, ':period' => $period, ':days' => (int)$expireDays, ':admin' => (int)$adminId]
                );
                $codes[] = $code;
            } catch (\Throwable $e) {
                error_log('Payment::generateVouchers ERROR: ' . $e->getMessage());
            }
        }
        return $codes;
    }

    public function getVouchers()
    {
        $sql = "SELECT v.*, pl.name AS plan_name, u.name AS used_by_name
                FROM vouchers v
                LEFT JOIN plans pl ON v.plan_id = pl.id
                LEFT JOIN users u ON v.used_by = u.id
                ORDER BY v.id DESC LIMIT 200";
        $result = $this->query($sql);
        return is_array($result) ? $result : [];
    }

    public function redeemVoucher($code, $userId, $subscriptionModel)
    {
        $rows = $this->query(
            "SELECT * FROM vouchers WHERE code = :code AND status = 'unused' LIMIT 1",
            [':code' => strtoupper(trim($code))]
        );
        $voucher = is_array($rows) ? ($rows[0] ?? null) : null;

        if (!$voucher) return ['ok' => false, 'msg' => 'کد معتبر نیست یا قبلاً استفاده شده.'];
        if (!empty($voucher['expires_at']) && strtotime($voucher['expires_at']) < time()) {
            return ['ok' => false, 'msg' => 'تاریخ انقضای کد گذشته است.'];
        }

        $subId = $subscriptionModel->createPending($userId, $voucher['plan_id'], $voucher['period'], 0);
        if (!$subId) return ['ok' => false, 'msg' => 'خطا در ایجاد اشتراک.'];

        $subscriptionModel->markActive($subId, 'VOUCHER:' . $voucher['code'], $voucher['period']);

        $this->query(
            "UPDATE vouchers SET status = 'used', used_by = :uid, used_at = NOW() WHERE id = :id",
            [':uid' => (int)$userId, ':id' => (int)$voucher['id']]
        );
        return ['ok' => true, 'msg' => '✅ کد فعال شد؛ اشتراک شما اکنون فعال است.'];
    }
}