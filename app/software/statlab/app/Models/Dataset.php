<?php
namespace App\Software\Statlab\Models;

use App\Software\Statlab\Core\Model;

class Dataset extends Model
{
    protected $table = 'datasets';

    public function getByProject(int $projectId): array
    {
        return $this->findAll(['project_id' => $projectId], 'id ASC');
    }

    /**
     * ✅ یافتن dataset با داده‌های یکسان در همان پروژه (جلوگیری از افزونگی)
     */
    public function findMatching(int $projectId, array $data): ?array
    {
        $rows   = $this->findAll(['project_id' => $projectId], 'id ASC');
        $target = array_map('floatval', $data);
        foreach ($rows as $r) {
            $existing = array_map('floatval', json_decode($r['data_json'] ?? '[]', true) ?: []);
            if ($existing === $target) return $r;
        }
        return null;
    }

    public function getDataArray(int $id): array
    {
        $row = $this->find($id);
        if (!$row) return [];
        return array_map('floatval', json_decode($row['data_json'] ?? '[]', true) ?: []);
    }
}