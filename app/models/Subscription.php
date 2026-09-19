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
    // مدیریت طرح‌ها توسط ادمین
    // ============================================

    public function createPlan($data)
    {
        try {
            $this->query(
                "INSERT INTO plans
                (
                    name,
                    slug,
                    description,
                    features,
                    price_monthly,
                    price_yearly,
                    checklist_limit_monthly,
                    is_featured,
                    is_active,
                    sort_order
                )
                VALUES
                (
                    :name,
                    :slug,
                    :description,
                    :features,
                    :price_monthly,
                    :price_yearly,
                    :checklist_limit_monthly,
                    :is_featured,
                    :is_active,
                    :sort_order
                )",
                [
                    ':name' => $data['name'],
                    ':slug' => $data['slug'],
                    ':description' => $data['description'],
                    ':features' => $data['features'],
                    ':price_monthly' => (int)$data['price_monthly'],
                    ':price_yearly' => (int)$data['price_yearly'],
                    ':checklist_limit_monthly' => (int)$data['checklist_limit_monthly'],
                    ':is_featured' => (int)$data['is_featured'],
                    ':is_active' => (int)$data['is_active'],
                    ':sort_order' => (int)$data['sort_order'],
                ]
            );

            $rows = $this->query("SELECT LAST_INSERT_ID() AS id");

            return is_array($rows)
                ? (int)($rows[0]['id'] ?? 0)
                : 0;

        } catch (\Throwable $e) {
            error_log('Subscription::createPlan ERROR: ' . $e->getMessage());
            return 0;
        }
    }

    public function updatePlan($id, $data)
    {
        try {
            $this->query(
                "UPDATE plans
                SET
                    name = :name,
                    slug = :slug,
                    description = :description,
                    features = :features,
                    price_monthly = :price_monthly,
                    price_yearly = :price_yearly,
                    checklist_limit_monthly = :checklist_limit_monthly,
                    is_featured = :is_featured,
                    is_active = :is_active,
                    sort_order = :sort_order
                WHERE id = :id",
                [
                    ':id' => (int)$id,
                    ':name' => $data['name'],
                    ':slug' => $data['slug'],
                    ':description' => $data['description'],
                    ':features' => $data['features'],
                    ':price_monthly' => (int)$data['price_monthly'],
                    ':price_yearly' => (int)$data['price_yearly'],
                    ':checklist_limit_monthly' => (int)$data['checklist_limit_monthly'],
                    ':is_featured' => (int)$data['is_featured'],
                    ':is_active' => (int)$data['is_active'],
                    ':sort_order' => (int)$data['sort_order'],
                ]
            );

            return true;

        } catch (\Throwable $e) {
            error_log('Subscription::updatePlan ERROR: ' . $e->getMessage());
            return false;
        }
    }

    public function planSlugExists($slug, $excludeId = 0)
    {
        $sql = "SELECT id
                FROM plans
                WHERE slug = :slug";

        $params = [
            ':slug' => $slug
        ];

        if ((int)$excludeId > 0) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = (int)$excludeId;
        }

        $result = $this->query($sql, $params);

        return is_array($result) && !empty($result);
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
            $id = (int)$id;
            $refTag = $refTag !== null ? trim((string)$refTag) : null;
            $period = trim((string)$period);

            if ($id <= 0) {
                return false;
            }

            // مدت اشتراک
            // 0 = طرح رایگان / 10 سال
            $months = ($period === 'yearly')
                ? 12
                : (($period === '0') ? 120 : 1);

            /*
            * فقط اشتراک pending می‌تواند فعال شود.
            * این شرط جلوی فعال‌سازی دوباره اشتراک را می‌گیرد.
            */
            $this->query(
                "UPDATE {$this->table}
                SET
                    status = 'active',
                    ref_id = :ref,
                    starts_at = NOW(),
                    expires_at = DATE_ADD(NOW(), INTERVAL {$months} MONTH)
                WHERE id = :id
                AND status = 'pending'",
                [
                    ':ref' => $refTag,
                    ':id'  => $id
                ]
            );

            // نتیجه واقعی UPDATE را بررسی می‌کنیم
            $check = $this->query(
                "SELECT
                    id,
                    status,
                    ref_id,
                    starts_at,
                    expires_at
                FROM {$this->table}
                WHERE id = :id
                LIMIT 1",
                [
                    ':id' => $id
                ]
            );

            if (!is_array($check) || empty($check[0])) {
                return false;
            }

            return
                ($check[0]['status'] ?? '') === 'active' &&
                (int)$check[0]['id'] === $id;

        } catch (\Throwable $e) {
            error_log('Subscription::markActive ERROR: ' . $e->getMessage());
            return false;
        }
    }

    public function markFailed($id)
    {
        try {
            $id = (int)$id;

            if ($id <= 0) {
                return false;
            }

            $this->query(
                "UPDATE {$this->table}
                SET status = 'failed'
                WHERE id = :id
                AND status = 'pending'",
                [
                    ':id' => $id
                ]
            );

            $check = $this->query(
                "SELECT id, status
                FROM {$this->table}
                WHERE id = :id
                LIMIT 1",
                [
                    ':id' => $id
                ]
            );

            return is_array($check)
                && !empty($check[0])
                && ($check[0]['status'] ?? '') === 'failed';

        } catch (\Throwable $e) {
            error_log('Subscription::markFailed ERROR: ' . $e->getMessage());
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