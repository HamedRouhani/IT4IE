<?php
namespace App\Models;

use App\Core\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    
    public function getAll()
    {
        try {
            $sql = "SELECT * FROM {$this->table}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            $settings = [];
            while ($row = $stmt->fetch()) {
                $settings[$row['key']] = $row['value'];
            }
            return $settings;
        } catch (\Exception $e) {
            return [];
        }
    }
    
    public function getByKey($key)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE `key` = :key LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['key' => $key]);
            return $stmt->fetch();
        } catch (\Exception $e) {
            return null;
        }
    }
    
    public function updateByKey($key, $value)
    {
        try {
            $sql = "UPDATE {$this->table} SET value = :value, updated_at = NOW() WHERE `key` = :key";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute(['key' => $key, 'value' => $value]);
        } catch (\Exception $e) {
            return false;
        }
    }
}