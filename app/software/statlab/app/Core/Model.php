<?php
namespace App\Software\Statlab\Core;

use PDO;

class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $tablePrefix = 'stat_';

    public function __construct()
    {
        try {
            $this->db = \App\Core\Database::getInstance();
        } catch (\Exception $e) {
            error_log("StatLab Model DB Error: " . $e->getMessage());
            $this->db = null;
        }
    }

    public function getTableName(): string
    {
        return $this->tablePrefix . $this->table;
    }

    public function query(string $sql, array $params = []): array
    {
        if (!$this->db) return [];
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function queryOne(string $sql, array $params = [])
    {
        if (!$this->db) return null;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findAll(array $conditions = [], string $orderBy = '', string $limit = ''): array
    {
        if (!$this->db) return [];
        $t = $this->getTableName();
        $sql = "SELECT * FROM {$t}";
        $params = [];
        if (!empty($conditions)) {
            $w = [];
            foreach ($conditions as $k => $v) {
                $w[] = "$k = :$k";
                $params[$k] = $v;
            }
            $sql .= " WHERE " . implode(' AND ', $w);
        }
        if ($orderBy) $sql .= " ORDER BY $orderBy";
        if ($limit) $sql .= " LIMIT $limit";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id)
    {
        if (!$this->db) return null;
        $t = $this->getTableName();
        $stmt = $this->db->prepare("SELECT * FROM {$t} WHERE {$this->primaryKey} = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function create(array $data)
    {
        if (!$this->db) return false;
        $t = $this->getTableName();
        $f = array_keys($data);
        $p = ':' . implode(', :', $f);
        $stmt = $this->db->prepare("INSERT INTO {$t} (" . implode(', ', $f) . ") VALUES ($p)");
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }

    public function update($id, array $data)
    {
        if (!$this->db) return false;
        $t = $this->getTableName();
        $f = [];
        foreach (array_keys($data) as $field) $f[] = "$field = :$field";
        $sql = "UPDATE {$t} SET " . implode(', ', $f) . " WHERE {$this->primaryKey} = :id";
        $data['id'] = $id;
        return $this->db->prepare($sql)->execute($data);
    }

    public function delete($id)
    {
        if (!$this->db) return false;
        $t = $this->getTableName();
        return $this->db->prepare("DELETE FROM {$t} WHERE {$this->primaryKey} = :id")->execute(['id' => $id]);
    }

    public function count(array $conditions = []): int
    {
        if (!$this->db) return 0;
        $t = $this->getTableName();
        $sql = "SELECT COUNT(*) as count FROM {$t}";
        $params = [];
        if (!empty($conditions)) {
            $w = [];
            foreach ($conditions as $k => $v) {
                $w[] = "$k = :$k";
                $params[$k] = $v;
            }
            $sql .= " WHERE " . implode(' AND ', $w);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return ($stmt->fetch(PDO::FETCH_ASSOC))['count'] ?? 0;
    }

    public function getAll(): array
    {
        if (!$this->db) return [];
        $t = $this->getTableName();
        $stmt = $this->db->prepare("SELECT * FROM {$t} ORDER BY {$this->primaryKey}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatest(int $limit = 10): array
    {
        return $this->findAll([], "{$this->primaryKey} DESC", (string)$limit);
    }

    /**
     * اجرای کوئری‌های بدون خروجی (DELETE / UPDATE)
     */
    public function execute(string $sql, array $params = []): int
    {
        if (!$this->db) return 0;
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}