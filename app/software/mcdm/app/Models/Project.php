<?php

namespace App\Software\Mcdm\Models;

use App\Software\Mcdm\Core\Model;

class Project extends Model
{
    protected $table = 'projects';

    public function getWithMethod($userId = null)
    {
        $table = $this->getTableName();
        $mTable = $this->tablePrefix . 'methods';

        $sql = "SELECT p.*, m.name as method_name, m.code as method_code, m.category as method_category
                FROM {$table} p
                LEFT JOIN {$mTable} m ON p.method_id = m.id";
        $params = [];

        if ($userId) {
            $sql .= " WHERE p.user_id = ?";
            $params[] = $userId;
        }

        $sql .= " ORDER BY p.updated_at DESC";
        return $this->query($sql, $params);
    }

    public function getWithDetails($id)
    {
        $table = $this->getTableName();
        $mTable = $this->tablePrefix . 'methods';

        $sql = "SELECT p.*, m.name as method_name, m.code as method_code, m.category as method_category
                FROM {$table} p
                LEFT JOIN {$mTable} m ON p.method_id = m.id
                WHERE p.id = ?";

        return $this->queryOne($sql, [$id]);
    }

    public function getCriteria($projectId)
    {
        $table = $this->tablePrefix . 'project_criteria';
        $sql = "SELECT * FROM {$table} WHERE project_id = ? AND is_active = 1 ORDER BY sort_order ASC, id ASC";
        return $this->query($sql, [$projectId]);
    }

    public function getAlternatives($projectId)
    {
        $table = $this->tablePrefix . 'project_alternatives';
        $sql = "SELECT * FROM {$table} WHERE project_id = ? AND is_active = 1 ORDER BY sort_order ASC, id ASC";
        return $this->query($sql, [$projectId]);
    }

    public function getEvaluations($projectId)
    {
        $table = $this->tablePrefix . 'project_evaluations';
        $sql = "SELECT * FROM {$table} WHERE project_id = ?";
        return $this->query($sql, [$projectId]);
    }

    public function setEvaluation($projectId, $criterionId, $alternativeId, $value)
    {
        $table = $this->tablePrefix . 'project_evaluations';
        $sql = "INSERT INTO {$table} (project_id, criterion_id, alternative_id, value)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE value = ?";
        return $this->query($sql, [$projectId, $criterionId, $alternativeId, $value, $value]);
    }

    public function updateCriterionWeight($criterionId, $weight)
    {
        $table = $this->tablePrefix . 'project_criteria';
        $sql = "UPDATE {$table} SET weight = ? WHERE id = ?";
        return $this->query($sql, [$weight, $criterionId]);
    }

    public function saveResults($projectId, array $results)
    {
        $table = $this->tablePrefix . 'project_results';
        $this->query("DELETE FROM {$table} WHERE project_id = ?", [$projectId]);

        foreach ($results as $r) {
            $this->query(
                "INSERT INTO {$table} (project_id, alternative_id, score, rank) VALUES (?, ?, ?, ?)",
                [$projectId, $r['alternative_id'], $r['score'], $r['rank']]
            );
        }
        return true;
    }

    public function getResults($projectId)
    {
        $table = $this->tablePrefix . 'project_results';
        $altTable = $this->tablePrefix . 'project_alternatives';
        $sql = "SELECT r.*, a.name as alternative_name
                FROM {$table} r
                LEFT JOIN {$altTable} a ON r.alternative_id = a.id
                WHERE r.project_id = ?
                ORDER BY r.rank ASC";
        return $this->query($sql, [$projectId]);
    }
}