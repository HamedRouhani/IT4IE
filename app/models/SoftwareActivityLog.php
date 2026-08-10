<?php

namespace App\Models;

use App\Core\Model;

/**
 * مدل لاگ فعالیت‌های نرم‌افزارهای ماژولار
 */
class SoftwareActivityLog extends Model
{
    protected $table = 'software_activity_logs';

    /**
     * ثبت یک فعالیت جدید
     */
    public function log($softwareSlug, $action, $recordType = null, $recordId = null, $oldValue = null, $newValue = null)
    {
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
                ORDER BY l.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        return $this->query($sql, [$softwareSlug]);
    }

    /**
     * آمار کلی
     */
    public function getStats($softwareSlug = null)
    {
        $where = $softwareSlug ? "WHERE software_slug = ?" : "";
        $params = $softwareSlug ? [$softwareSlug] : [];

        $sql = "SELECT 
                    COUNT(*) AS total_activities,
                    COUNT(DISTINCT user_id) AS unique_users,
                    COUNT(DISTINCT ip_address) AS unique_ips,
                    SUM(CASE WHEN user_id IS NULL THEN 1 ELSE 0 END) AS guest_activities
                FROM {$this->table} {$where}";
        return $this->queryOne($sql, $params);
    }

    /**
     * آمار بر اساس نوع فعالیت
     */
    public function getStatsByAction($softwareSlug = null)
    {
        $where = $softwareSlug ? "WHERE software_slug = ?" : "";
        $params = $softwareSlug ? [$softwareSlug] : [];

        $sql = "SELECT action, COUNT(*) as count
                FROM {$this->table} {$where}
                GROUP BY action
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
}