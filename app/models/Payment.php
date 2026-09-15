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
        $expected = $amount + random_int(100, 999); // رقم یکتا برای تطبیق بانکی
        try {
            $this->query(
                "INSERT INTO {$this->table} (user_id, subscription_id, method, amount, expected_amount, status)
                 VALUES (:uid, :sid, 'card_transfer', :amt, :exp, 'awaiting_ref')",
                [':uid' => (int)$userId, ':sid' => (int)$subscriptionId, ':amt' => (int)$amount, ':exp' => $expected]
            );
            $rows = $this->query("SELECT LAST_INSERT_ID() AS id");
            return is_array($rows) ? (int)($rows[0]['id'] ?? 0) : 0;
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
            $this->query(
                "UPDATE {$this->table}
                 SET ref_code = :ref, payer_card = :card, note = :note, status = 'pending_review'
                 WHERE id = :id AND status = 'awaiting_ref'",
                [':ref' => $refCode, ':card' => $payerCard, ':note' => $note, ':id' => (int)$paymentId]
            );
            return true;
        } catch (\Throwable $e) {
            error_log('Payment::submitRef ERROR: ' . $e->getMessage());
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
        $sql = "SELECT p.*, pl.name AS plan_name
                FROM {$this->table} p
                LEFT JOIN subscriptions s ON p.subscription_id = s.id
                LEFT JOIN plans pl ON s.plan_id = pl.id
                WHERE p.user_id = :uid
                ORDER BY p.id DESC LIMIT 50";
        $result = $this->query($sql, [':uid' => (int)$userId]);
        return is_array($result) ? $result : [];
    }

    public function setStatus($paymentId, $status, $adminId, $adminNote = null)
    {
        try {
            $this->query(
                "UPDATE {$this->table}
                 SET status = :st, admin_note = :note, reviewed_by = :admin, reviewed_at = NOW()
                 WHERE id = :id",
                [':st' => $status, ':note' => $adminNote, ':admin' => (int)$adminId, ':id' => (int)$paymentId]
            );
            return true;
        } catch (\Throwable $e) {
            error_log('Payment::setStatus ERROR: ' . $e->getMessage());
            return false;
        }
    }

    // ============================================
    // پیش‌فاکتور سازمانی
    // ============================================
    public function createInvoiceRequest($userId, $amount, $note)
    {
        try {
            $this->query(
                "INSERT INTO {$this->table} (user_id, method, amount, expected_amount, note, status)
                 VALUES (:uid, 'invoice', :amt, :amt, :note, 'awaiting_contact')",
                [':uid' => (int)$userId, ':amt' => (int)$amount, ':note' => $note]
            );
            return true;
        } catch (\Throwable $e) {
            error_log('Payment::createInvoiceRequest ERROR: ' . $e->getMessage());
            return false;
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