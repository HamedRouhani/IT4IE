<?php

namespace App\Core;

use PDO;

abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct()
    {
        try {
            $this->db = Database::getInstance();
        } catch (\Exception $e) {
            $this->db = null;
        }
    }

    public function findAll($conditions = [], $orderBy = '', $limit = '')
    {
        if (!$this->db) return [];
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = :$key";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        if (!$this->db) return null;
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        if (!$this->db) return false;
        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        if (!$this->db) return false;
        $fields = [];
        foreach (array_keys($data) as $field) {
            $fields[] = "$field = :$field";
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = :id";
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        if (!$this->db) return false;
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    // ============================================
    // متدهای جدید برای پشتیبانی از نرم‌افزارهای ماژولار
    // ============================================

    /**
     * اجرای query دلخواه و بازگرداندن تمام نتایج
     */
    public function query($sql, $params = [])
    {
        if (!$this->db) return [];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * اجرای query دلخواه و بازگرداندن یک نتیجه
     */
    public function queryOne($sql, $params = [])
    {
        if (!$this->db) return null;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * دریافت تمام رکوردها با ترتیب مشخص
     */
    public function getAll()
    {
        if (!$this->db) return [];
        $sql = "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * شمارش رکوردها
     */
    public function count($conditions = [])
    {
        if (!$this->db) return 0;
        $sql = "SELECT COUNT(*) as count FROM {$this->table}";
        $params = [];
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = :$key";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] ?? 0;
    }

    /**
     * دریافت آخرین رکوردها
     */
    public function getLatest($limit = 10)
    {
        return $this->findAll([], "{$this->primaryKey} DESC", $limit);
    }

    /**
     * بررسی وجود رکورد با شرایط مشخص
     */
    public function exists($conditions = [])
    {
        return $this->count($conditions) > 0;
    }

    /**
     * دریافت آخرین ID اضافه شده
     */
    public function getLastInsertId()
    {
        return $this->db ? $this->db->lastInsertId() : null;
    }

    /**
     * شروع تراکنش
     */
    public function beginTransaction()
    {
        if ($this->db) return $this->db->beginTransaction();
        return false;
    }

    /**
     * تایید تراکنش
     */
    public function commit()
    {
        if ($this->db) return $this->db->commit();
        return false;
    }

    /**
     * بازگشت تراکنش
     */
    public function rollback()
    {
        if ($this->db) return $this->db->rollBack();
        return false;
    }
}