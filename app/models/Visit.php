<?php

namespace App\Models;

use App\Core\Model;

/**
 * مدل بازدیدهای سایت
 */
class Visit extends Model
{
    protected $table = 'visits';

    /**
     * ثبت یک بازدید جدید
     */
    public function record()
    {
        return $this->create([
            'page_url' => $_SERVER['REQUEST_URI'] ?? '/',
            'page_title' => null,
            'ip_address' => $this->getClientIP(),
            'user_id' => $_SESSION['user_id'] ?? null,
            'session_id' => session_id(),
            'referrer' => isset($_SERVER['HTTP_REFERER']) ? substr($_SERVER['HTTP_REFERER'], 0, 255) : null,
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null,
            'visit_date' => date('Y-m-d'),
            'visit_time' => date('H:i:s')
        ]);
    }

    /**
     * آمار کلی
     */
    public function getOverviewStats()
    {
        $sql = "SELECT 
                    COUNT(*) AS total_visits,
                    COUNT(DISTINCT ip_address) AS unique_ips,
                    COUNT(DISTINCT session_id) AS unique_sessions,
                    COUNT(CASE WHEN user_id IS NOT NULL THEN 1 END) AS logged_visits,
                    SUM(CASE WHEN visit_date = CURDATE() THEN 1 ELSE 0 END) AS today_visits,
                    SUM(CASE WHEN visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS week_visits,
                    SUM(CASE WHEN visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) AS month_visits
                FROM {$this->table}";
        return $this->queryOne($sql);
    }

    /**
     * بازدید روزانه (برای نمودار)
     */
    public function getDailyStats($days = 14)
    {
        $days = (int) $days;
        $sql = "SELECT visit_date, 
                       COUNT(*) AS visits, 
                       COUNT(DISTINCT ip_address) AS unique_ips
                FROM {$this->table}
                WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL {$days} DAY)
                GROUP BY visit_date
                ORDER BY visit_date ASC";
        return $this->query($sql);
    }

    /**
     * پربازدیدترین صفحات
     */
    public function getTopPages($limit = 10)
    {
        $limit = (int) $limit;
        $sql = "SELECT page_url, COUNT(*) AS visits
                FROM {$this->table}
                GROUP BY page_url
                ORDER BY visits DESC
                LIMIT {$limit}";
        return $this->query($sql);
    }

    /**
     * آخرین بازدیدها
     */
    public function getRecentVisits($limit = 20)
    {
        $limit = (int) $limit;
        $sql = "SELECT v.*, u.name AS user_name
                FROM {$this->table} v
                LEFT JOIN users u ON v.user_id = u.id
                ORDER BY v.id DESC
                LIMIT {$limit}";
        return $this->query($sql);
    }

    /**
     * منابع ورود (Referrers)
     */
    public function getTopReferrers($limit = 5)
    {
        $limit = (int) $limit;
        $sql = "SELECT referrer, COUNT(*) AS visits
                FROM {$this->table}
                WHERE referrer IS NOT NULL AND referrer != ''
                GROUP BY referrer
                ORDER BY visits DESC
                LIMIT {$limit}";
        return $this->query($sql);
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