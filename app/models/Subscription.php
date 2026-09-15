<?php
namespace App\Models;

use App\Core\Model;

class Subscription extends Model
{
    protected $table = 'subscriptions';

    // ============================================
    // تعرفه‌ها
    // ============================================
    public function getAllPlans($onlyActive = true)
    {
        $sql = "SELECT * FROM plans";
        if ($onlyActive) $sql .= " WHERE is_active = 1";
        $sql .= " ORDER BY sort_order ASC";
        $result = $this->query($sql);
        return is_array($result) ? $result : [];
    }

    public function getPlan($id)
    {
        $result = $this->query("SELECT * FROM plans WHERE id = :id", [':id' => (int)$id]);
        return is_array($result) ? ($result[0] ?? null) : null;
    }

    // ============================================
    // اشتراک‌ها
    // ============================================
    public function getActiveSubscription($userId)
    {
        $sql = "SELECT s.*, p.name AS plan_name, p.slug AS plan_slug, p.checklist_limit_monthly
                FROM {$this->table} s
                JOIN plans p ON s.plan_id = p.id
                WHERE s.user_id = :uid AND s.status = 'active' AND s.expires_at > NOW()
                ORDER BY s.id DESC LIMIT 1";
        $result = $this->query($sql, [':uid' => (int)$userId]);
        return is_array($result) ? ($result[0] ?? null) : null;
    }

    public function getSubscription($id)
    {
        $sql = "SELECT s.*, p.name AS plan_name FROM {$this->table} s
                JOIN plans p ON s.plan_id = p.id WHERE s.id = :id";
        $result = $this->query($sql, [':id' => (int)$id]);
        return is_array($result) ? ($result[0] ?? null) : null;
    }

    public function createPending($userId, $planId, $period, $amount)
    {
        try {
            $this->query(
                "INSERT INTO {$this->table} (user_id, plan_id, period, amount, status)
                 VALUES (:uid, :pid, :period, :amount, 'pending')",
                [':uid' => (int)$userId, ':pid' => (int)$planId, ':period' => $period, ':amount' => (int)$amount]
            );
            $rows = $this->query("SELECT LAST_INSERT_ID() AS id");
            return is_array($rows) ? (int)($rows[0]['id'] ?? 0) : 0;
        } catch (\Throwable $e) {
            error_log('Subscription::createPending ERROR: ' . $e->getMessage());
            return 0;
        }
    }

    public function markActive($id, $refTag, $period)
    {
        try {
            // '0' = طرح رایگان (۱۰ سال)
            $months = ($period === 'yearly') ? 12 : (($period === '0') ? 120 : 1);
            $this->query(
                "UPDATE {$this->table}
                 SET status = 'active', ref_id = :ref, starts_at = NOW(),
                     expires_at = DATE_ADD(NOW(), INTERVAL :m MONTH)
                 WHERE id = :id",
                [':ref' => $refTag, ':m' => $months, ':id' => (int)$id]
            );
            return true;
        } catch (\Throwable $e) {
            error_log('Subscription::markActive ERROR: ' . $e->getMessage());
            return false;
        }
    }

    public function markFailed($id)
    {
        try {
            $this->query("UPDATE {$this->table} SET status = 'failed' WHERE id = :id", [':id' => (int)$id]);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    // ============================================
    // سقف Freemium
    // ============================================
    public function getMonthlyChecklistLimit($userId)
    {
        $sub = $this->getActiveSubscription($userId);
        return $sub ? (int)$sub['checklist_limit_monthly'] : 3;
    }

    public function countMonthlySubmissions($userId)
    {
        $sql = "SELECT COUNT(*) AS c FROM checklist_submissions
                WHERE user_id = :uid AND created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')";
        $result = $this->query($sql, [':uid' => (int)$userId]);
        return is_array($result) ? (int)($result[0]['c'] ?? 0) : 0;
    }

    // ============================================
    // امتیازدهی لید بر اساس ریسک و اطلاعات تماس
    // ============================================

    public function scoreSubmission($submissionId, $riskLevel, $company, $phone)
    {
        $map = ['critical' => 100, 'high' => 75, 'medium' => 50, 'low' => 25];
        $score = $map[$riskLevel] ?? 20;
        
        // امتیاز اضافی برای اطلاعات تماس کامل
        if (!empty($company) && $company !== 'تعریف نشده') {
            $score = min(100, $score + 10);
        }
        if (!empty($phone) && $phone !== 'ندارد') {
            $score = min(100, $score + 5);
        }
        
        try {
            $this->query(
                "UPDATE checklist_submissions SET lead_score = :score WHERE id = :id",
                [':score' => $score, ':id' => (int)$submissionId]
            );
        } catch (\Throwable $e) {
            error_log('Subscription::scoreSubmission ERROR: ' . $e->getMessage());
        }
        
        return $score;
    }
}