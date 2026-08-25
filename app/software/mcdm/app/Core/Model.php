<?php

namespace App\Software\Mcdm\Core;

use PDO;

/**
 * مدل پایه ماژول MCDM
 * از Database اصلی IT4IE استفاده می‌کند
 * پیشوند جداول: mcdm_
 */
class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $tablePrefix = 'mcdm_';
    protected $fillable = [];
    protected $timestamps = true;

    public function __construct()
    {
        try {
            // ✅ Database::getInstance() مستقیماً PDO برمی‌گرداند
            $this->db = \App\Core\Database::getInstance();
        } catch (\Exception $e) {
            error_log("MCDM Model - Database connection error: " . $e->getMessage());
            $this->db = null;
        }
    }

    /**
     * نام کامل جدول با پیشوند
     */
    protected function getTableName()
    {
        return $this->tablePrefix . $this->table;
    }

    // ============================================
    // متدهای query (هم‌الگو با App\Core\Model)
    // ============================================

    public function query($sql, $params = [])
    {
        if (!$this->db) return [];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function queryOne($sql, $params = [])
    {
        if (!$this->db) return null;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findAll($conditions = [], $orderBy = '', $limit = '')
    {
        if (!$this->db) return [];
        $table = $this->getTableName();
        $sql = "SELECT * FROM {$table}";
        $params = [];

        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $key => $value) {
                $where[] = "$key = :$key";
                $params[$key] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $where);
        }
        if ($orderBy) $sql .= " ORDER BY $orderBy";
        if ($limit)   $sql .= " LIMIT $limit";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        if (!$this->db) return null;
        $table = $this->getTableName();
        $sql = "SELECT * FROM {$table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create($data)
    {
        if (!$this->db) return false;
        $table = $this->getTableName();
        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        $sql = "INSERT INTO {$table} (" . implode(', ', $fields) . ") VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        if (!$this->db) return false;
        $table = $this->getTableName();
        $fields = [];
        foreach (array_keys($data) as $field) {
            $fields[] = "$field = :$field";
        }
        $sql = "UPDATE {$table} SET " . implode(', ', $fields) . " WHERE {$this->primaryKey} = :id";
        $data['id'] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function delete($id)
    {
        if (!$this->db) return false;
        $table = $this->getTableName();
        $sql = "DELETE FROM {$table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    public function count($conditions = [])
    {
        if (!$this->db) return 0;
        $table = $this->getTableName();
        $sql = "SELECT COUNT(*) as count FROM {$table}";
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
}