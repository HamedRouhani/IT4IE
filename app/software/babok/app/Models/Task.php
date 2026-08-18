<?php

namespace App\Software\Babok\Models;

use App\Software\Babok\Core\Model;

/**
 * مدل وظایف BABOK
 * جدول: babok_tasks
 */
class Task extends Model
{
    protected $table = 'tasks';

    /**
     * دریافت همه وظایف به همراه حوزه دانشی
     */
    public function getAllWithKnowledgeArea()
    {
        $sql = "SELECT t.*, ka.name as knowledge_area_name, ka.code as knowledge_area_code
                FROM babok_tasks t
                LEFT JOIN babok_knowledge_areas ka ON t.knowledge_area_id = ka.id
                ORDER BY t.code";
        return $this->query($sql);
    }

    /**
     * دریافت یک وظیفه با جزئیات کامل
     */
    public function find($id)
    {
        $sql = "SELECT t.*, ka.name as knowledge_area_name, ka.code as knowledge_area_code
                FROM babok_tasks t
                LEFT JOIN babok_knowledge_areas ka ON t.knowledge_area_id = ka.id
                WHERE t.id = ?";
        return $this->queryOne($sql, [$id]);
    }

    // ============================================================
    // 🌟 جستجوی معنایی پیشرفته و بهینه‌شده (Semantic Search)
    // ============================================================

    /**
     * جستجوی معنایی هوشمند با امتیازدهی چندلایه و پشتیبانی کامل از فارسی
     */
    public function semanticSearch($query, $limit = 10)
    {
        $originalQuery = trim($query);
        if (mb_strlen($originalQuery) < 2) {
            return [];
        }

        // ۱. نرمال‌سازی متن (حیاتی برای یکسان‌سازی ی/ک و حذف اعراب)
        $normalizedQuery = $this->normalizePersian($originalQuery);
        
        // ۲. تقسیم به کلمات و حذف کلمات تک‌حرفی (مثل "و"، "در" که باعث خطای FULLTEXT می‌شوند)
        $words = preg_split('/\s+/', $normalizedQuery);
        $validWords = array_filter($words, fn($w) => mb_strlen($w) >= 2);
        
        if (empty($validWords)) {
            // اگر فقط کلمات کوتاه بود، fallback به LIKE ساده
            return $this->fallbackLikeSearch($originalQuery, $limit);
        }

        // ۳. ساخت کوئری Boolean: +کلمه1 +کلمه2
        $booleanQuery = '+' . implode(' +', $validWords);
        $likePhrase = '%' . $normalizedQuery . '%';

        // ۴. دریافت مترادف‌ها برای کلمه اول (برای گسترش هوشمند جستجو)
        $synonyms = $this->getSynonyms($validWords[0]);
        $synonymConditions = [];
        $synonymParams = [];
        foreach ($synonyms as $syn) {
            $synonymConditions[] = "(t.name LIKE ? OR t.description LIKE ?)";
            $synonymParams[] = '%' . $syn . '%';
            $synonymParams[] = '%' . $syn . '%';
        }
        $synonymWhere = !empty($synonymConditions) ? ' OR ' . implode(' OR ', $synonymConditions) : '';

        // ۵. کوئری اصلی با امتیازدهی دقیق
        $sql = "
            SELECT 
                t.id, t.code, t.name, t.description, t.inputs, t.outputs, t.stakeholders, t.knowledge_area_id,
                ka.name AS knowledge_area_name, ka.code AS knowledge_area_code,
                
                -- لایه ۱: تطابق کامل عبارت (بالاترین اولویت)
                (CASE 
                    WHEN t.name LIKE ? THEN 100
                    WHEN t.description LIKE ? THEN 50
                    ELSE 0
                END) AS phrase_score,
                
                -- لایه ۲: حضور تمام کلمات جستجو (Boolean)
                (CASE 
                    WHEN MATCH(t.name, t.description, t.inputs, t.outputs) AGAINST(? IN BOOLEAN MODE) THEN 30
                    ELSE 0
                END) AS boolean_score,
                
                -- لایه ۳: امتیاز هوشمند ذاتی MySQL (Natural Language)
                MATCH(t.name, t.description, t.inputs, t.outputs) AGAINST(? IN NATURAL LANGUAGE MODE) AS natural_score
                
            FROM babok_tasks t
            LEFT JOIN babok_knowledge_areas ka ON t.knowledge_area_id = ka.id
            WHERE 
                MATCH(t.name, t.description, t.inputs, t.outputs) AGAINST(? IN BOOLEAN MODE)
                OR t.name LIKE ?
                OR t.description LIKE ?
                {$synonymWhere}
            HAVING (phrase_score + boolean_score + natural_score) > 0
            ORDER BY (phrase_score + boolean_score + natural_score) DESC
            LIMIT ?
        ";

        // ۶. بایند کردن دقیق پارامترها (به ترتیب ظاهر شدن ? در کوئری)
        $params = array_merge(
            [
                $likePhrase, $likePhrase,          // phrase_score (name, desc)
                $booleanQuery,                      // boolean_score
                $normalizedQuery,                   // natural_score
                $booleanQuery,                      // WHERE boolean
                $likePhrase, $likePhrase,           // WHERE LIKE (name, desc)
            ],
            $synonymParams,                         // WHERE Synonyms
            [(int)$limit]                           // LIMIT
        );

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // محاسبه امتیاز نهایی در PHP برای ارسال به فرانت‌اند
            foreach ($results as &$result) {
                $result['relevance_score'] = round(
                    $result['phrase_score'] + 
                    $result['boolean_score'] + 
                    $result['natural_score'], 
                    2
                );
            }
            
            return $results;
        } catch (\Exception $e) {
            error_log("Semantic Search Error: " . $e->getMessage() . " | Query: " . $normalizedQuery);
            // Fallback: اگر FULLTEXT شکست خورد، حداقل LIKE را امتحان کن
            return $this->fallbackLikeSearch($originalQuery, $limit);
        }
    }

    /**
     * جستجوی جایگزین ساده (زمانی که FULLTEXT به دلیل کلمات کوتاه یا خطا کار نکند)
     */
    private function fallbackLikeSearch($query, $limit)
    {
        $likePhrase = '%' . $query . '%';
        $sql = "
            SELECT t.id, t.code, t.name, t.description, t.inputs, t.outputs, t.stakeholders, t.knowledge_area_id,
                   ka.name AS knowledge_area_name, ka.code AS knowledge_area_code,
                   10 AS relevance_score
            FROM babok_tasks t
            LEFT JOIN babok_knowledge_areas ka ON t.knowledge_area_id = ka.id
            WHERE t.name LIKE ? OR t.description LIKE ? OR t.code LIKE ?
            ORDER BY t.code ASC
            LIMIT ?
        ";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$likePhrase, $likePhrase, $likePhrase, (int)$limit]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * نرمال‌سازی متن فارسی برای جستجوی بهتر
     */
    private function normalizePersian($text)
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = str_replace(['ي', 'ك', 'ى'], ['ی', 'ک', 'ی'], $text);
        $text = preg_replace('/[\x{064B}-\x{0652}\x{0670}\x{0640}]/u', '', $text);
        $text = str_replace("\u{200C}", ' ', $text);
        $text = preg_replace('/[+\-><\(\)~*\"@]+/', ' ', $text);
        return preg_replace('/\s+/', ' ', trim($text));
    }

    /**
     * دریافت مترادف‌ها از جدول babok_synonyms
     */
    private function getSynonyms($term)
    {
        try {
            $checkSql = "SHOW TABLES LIKE 'babok_synonyms'";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                return [];
            }

            $sql = "SELECT canonical_term, synonym_fa 
                    FROM babok_synonyms 
                    WHERE synonym_fa = ? OR canonical_term = ?
                    LIMIT 5";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$term, $term]);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $synonyms = [];
            foreach ($rows as $row) {
                if ($row['synonym_fa'] !== $term) $synonyms[] = $row['synonym_fa'];
                if ($row['canonical_term'] !== $term) $synonyms[] = $row['canonical_term'];
            }
            
            return array_unique($synonyms);
        } catch (\Exception $e) {
            return [];
        }
    }
}