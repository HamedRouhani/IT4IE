<?php

namespace App\Models;

use App\Core\Model;
use PDOException;

class WorkflowTemplate extends Model
{
    protected $table = 'workflow_templates';

    public function getPublished($toolSlug = null)
    {
        try {
            $sql = "SELECT id, slug, title, summary, tool_slug, method_code,
                           problem_type, difficulty, estimated_minutes, input_payload
                    FROM {$this->table}
                    WHERE status = 'published'";
            $params = [];

            if ($toolSlug !== null) {
                $sql .= ' AND tool_slug = :tool_slug';
                $params['tool_slug'] = $toolSlug;
            }

            $sql .= ' ORDER BY sort_order ASC, published_at DESC, id DESC';

            return $this->query($sql, $params);
        } catch (PDOException $e) {
            error_log('WorkflowTemplate::getPublished: ' . $e->getMessage());
            return [];
        }
    }

    public function findPublishedBySlug($slug)
    {
        try {
            return $this->queryOne(
                "SELECT id, slug, title, summary, tool_slug, method_code,
                        problem_type, difficulty, estimated_minutes, input_payload
                 FROM {$this->table}
                 WHERE slug = :slug AND status = 'published'
                 LIMIT 1",
                ['slug' => $slug]
            );
        } catch (PDOException $e) {
            error_log('WorkflowTemplate::findPublishedBySlug: ' . $e->getMessage());
            return null;
        }
    }
}
