<?php

namespace App\Software\Pmbok\Services;

use App\Software\Pmbok\Core\Model; // یا کلاس پایه‌ای که در پروژه شما برای دیتابیس استفاده می‌شود

class IndustryRecommendationService
{
    private $db;

    public function __construct()
    {
        $this->db = \App\Core\Database::getInstance();
    }

    /**
     * دریافت فرآیندهای پیشنهادی بر اساس صنعت انتخابی
     */
    public function getRecommendedProcesses($industryCode, $limit = 10)
    {
        $sql = "
            SELECT 
                p.id AS process_id,
                p.code AS process_code,
                p.name_fa AS process_name,
                ka.name_fa AS knowledge_area,
                ip.priority,
                ip.relevance_score,
                ip.customization_notes
            FROM pmbok_industry_processes ip
            JOIN pmbok_processes p ON ip.process_id = p.id
            JOIN pmbok_knowledge_areas ka ON p.knowledge_area_id = ka.id
            JOIN pmbok_industries i ON ip.industry_id = i.id
            WHERE i.code = ?
            ORDER BY 
                FIELD(ip.priority, 'critical', 'high', 'medium', 'low'),
                ip.relevance_score DESC
            LIMIT ?
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$industryCode, (int)$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * دریافت تکنیک‌های پیشنهادی بر اساس صنعت و حوزه دانشی
     */
    public function getRecommendedTechniques($industryCode, $knowledgeAreaId = null)
    {
        // نگاشت ساده صنایع به دسته‌بندی تکنیک‌های پرکاربرد
        $industryTechniqueMap = [
            'OG' => ['management', 'strategic'], // نفت و گاز: مدیریت و استراتژیک
            'MFG' => ['modeling', 'management'], // تولیدی: مدل‌سازی و مدیریت
            'STL' => ['management', 'research'], // فولاد: مدیریت و تحقیقات
            'FMCG' => ['collaborative', 'experimental'], // FMCG: مشارکتی و آزمایشی
            'SVC' => ['collaborative', 'strategic'] // خدماتی: مشارکتی و استراتژیک
        ];

        $categories = $industryTechniqueMap[$industryCode] ?? ['collaborative', 'management'];
        $placeholders = implode(',', array_fill(0, count($categories), '?'));

        $sql = "
            SELECT t.id, t.name_fa, t.category, t.description
            FROM pmbok_techniques t
            WHERE t.category IN ($placeholders)
        ";
        
        $params = $categories;
        if ($knowledgeAreaId) {
            $sql .= " AND t.knowledge_area_id = ?"; // اگر در جدول تکنیک‌ها این ستون را دارید
            $params[] = $knowledgeAreaId;
        }

        $sql .= " LIMIT 5";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}