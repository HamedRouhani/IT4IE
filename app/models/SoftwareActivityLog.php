<?php

namespace App\Models;

use App\Core\Model;

/**
 * مدل لاگ فعالیت‌های نرم‌افزارهای ماژولار
 */
class SoftwareActivityLog extends Model
{
    protected $table = 'software_activity_logs';

    /** SQL predicate for excluding owner/admin rows from queries using alias `l`. */
    public const EXCLUDED_ACTIVITY_CONDITION = "(
        LOWER(TRIM(COALESCE(l.user_name, ''))) = 'hamed yahoo'
        OR EXISTS (
            SELECT 1
            FROM users excluded_user
            WHERE excluded_user.id = l.user_id
              AND (
                  LOWER(TRIM(COALESCE(excluded_user.role, ''))) = 'admin'
                  OR LOWER(TRIM(COALESCE(excluded_user.name, ''))) = 'hamed yahoo'
              )
        )
    )";

    /**
     * ثبت یک فعالیت جدید
     */
    public function log($softwareSlug, $action, $recordType = null, $recordId = null, $oldValue = null, $newValue = null)
    {
        if ($this->shouldExcludeCurrentUser()) {
            return false;
        }

        return $this->create([
            'software_slug' => $softwareSlug,
            'action' => $action,
            'record_type' => $recordType,
            'record_id' => $recordId,
            'user_id' => $_SESSION['user_id'] ?? null,
            'user_name' => $_SESSION['user_name'] ?? null,
            'ip_address' => $this->getClientIP(),
            'old_value' => $oldValue ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : null,
            'new_value' => $newValue ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : null
        ]);
    }

    /**
     * دریافت لاگ‌های یک نرم‌افزار
     */
    public function getBySoftware($softwareSlug, $limit = 50, $offset = 0)
    {
        $limit = (int) $limit;
        $offset = (int) $offset;

        $sql = "SELECT l.*, u.name as user_name_from_db, u.email as user_email
                FROM {$this->table} l
                LEFT JOIN users u ON l.user_id = u.id
                WHERE l.software_slug = ?
                  AND NOT " . self::EXCLUDED_ACTIVITY_CONDITION . "
                ORDER BY l.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        return $this->query($sql, [$softwareSlug]);
    }

    /**
     * آمار کلی
     */
    public function getStats($softwareSlug = null)
    {
        $where = "WHERE NOT " . self::EXCLUDED_ACTIVITY_CONDITION;
        $params = $softwareSlug ? [$softwareSlug] : [];
        if ($softwareSlug) $where .= " AND l.software_slug = ?";

        $sql = "SELECT 
                    COUNT(*) AS total_activities,
                    COUNT(DISTINCT l.user_id) AS unique_users,
                    COUNT(DISTINCT l.ip_address) AS unique_ips,
                    SUM(CASE WHEN l.user_id IS NULL THEN 1 ELSE 0 END) AS guest_activities
                FROM {$this->table} l {$where}";
        return $this->queryOne($sql, $params);
    }

    /**
     * آمار بر اساس نوع فعالیت
     */
    public function getStatsByAction($softwareSlug = null)
    {
        $where = "WHERE NOT " . self::EXCLUDED_ACTIVITY_CONDITION;
        $params = $softwareSlug ? [$softwareSlug] : [];
        if ($softwareSlug) $where .= " AND l.software_slug = ?";

        $sql = "SELECT l.action, COUNT(*) as count
                FROM {$this->table} l {$where}
                GROUP BY l.action
                ORDER BY count DESC
                LIMIT 20";
        return $this->query($sql, $params);
    }

    /**
     * دریافت IP کاربر
     */
    private function getClientIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private function shouldExcludeCurrentUser(): bool
    {
        if (strtolower(trim((string) ($_SESSION['user_role'] ?? ''))) === 'admin') {
            return true;
        }

        $sessionName = strtolower(trim((string) ($_SESSION['user_name'] ?? '')));
        if ($sessionName === 'hamed yahoo') {
            return true;
        }

        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId <= 0) {
            return false;
        }

        return (bool) $this->queryOne(
            "SELECT 1
             FROM users
             WHERE id = ?
               AND (
                   LOWER(TRIM(COALESCE(role, ''))) = 'admin'
                   OR LOWER(TRIM(COALESCE(name, ''))) = 'hamed yahoo'
               )
             LIMIT 1",
            [$userId]
        );
    }
}
