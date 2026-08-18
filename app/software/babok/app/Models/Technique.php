<?php

namespace App\Software\Babok\Models;

use App\Software\Babok\Core\Model;

/**
 * مدل تکنیک‌های BABOK
 * جدول: babok_techniques (۵۰ رکورد در ۶ دسته‌بندی)
 */
class Technique extends Model
{
    protected $table = 'techniques';

    /**
     * دریافت تکنیک‌های یک دسته‌بندی
     * دسته‌ها: collaborative, research, experimental, management, strategic, modeling
     */
    public function getByCategory($category)
    {
        $sql = "SELECT * FROM babok_techniques WHERE category = ? ORDER BY name";
        return $this->query($sql, [$category]);
    }

    /**
     * دریافت لیست دسته‌بندی‌های موجود
     */
    public function getCategories()
    {
        $sql = "SELECT DISTINCT category FROM babok_techniques ORDER BY category";
        $result = $this->query($sql);
        return array_column($result, 'category');
    }

    /**
     * دریافت تعداد تکنیک‌های هر دسته‌بندی
     */
    public function getCategoriesWithCount()
    {
        $sql = "SELECT category, COUNT(*) as count
                FROM babok_techniques
                GROUP BY category
                ORDER BY count DESC";
        return $this->query($sql);
    }

    /**
     * جستجوی تکنیک‌ها بر اساس کلمه کلیدی
     */
    public function search($keyword)
    {
        $sql = "SELECT * FROM babok_techniques
                WHERE name LIKE ?
                   OR description LIKE ?
                   OR purpose LIKE ?
                ORDER BY name";
        $search = "%{$keyword}%";
        return $this->query($sql, [$search, $search, $search]);
    }

    /**
     * دریافت وظایفی که از یک تکنیک استفاده می‌کنند
     */
    public function getRelatedTasks($techniqueId)
    {
        $sql = "SELECT t.*
                FROM babok_tasks t
                JOIN babok_task_techniques tt ON t.id = tt.task_id
                WHERE tt.technique_id = ?
                ORDER BY t.code";
        return $this->query($sql, [$techniqueId]);
    }

    /**
     * جستجوی معنایی در تکنیک‌های BABOK
     */
    public function semanticSearch($query, $limit = 10)
    {
        $cleanQuery = preg_replace('/[+\-><\(\)~*\"@]+/', ' ', $query);
        $cleanQuery = trim($cleanQuery);
        
        if (empty($cleanQuery)) {
            return [];
        }

        $limit = (int)$limit;

        $sql = "SELECT id, name, description, purpose, category,
                MATCH(name, description, purpose, advantages, usage_considerations) 
                AGAINST(:query1 IN NATURAL LANGUAGE MODE) as relevance_score
                FROM babok_techniques 
                WHERE MATCH(name, description, purpose, advantages, usage_considerations) 
                AGAINST(:query2 IN NATURAL LANGUAGE MODE)
                ORDER BY relevance_score DESC
                LIMIT {$limit}";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':query1', $cleanQuery, \PDO::PARAM_STR);
        $stmt->bindValue(':query2', $cleanQuery, \PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}