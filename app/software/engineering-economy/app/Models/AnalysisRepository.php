<?php

declare(strict_types=1);

namespace App\Software\EngineeringEconomy\Models;

use App\Core\Database;

/**
 * ثبت نسخهٔ تحلیل‌ها (بازبینی‌پذیری گزارش).
 */
final class AnalysisRepository
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * @param array<string,mixed> $payload
     */
    public function save(int $projectId, ?int $alternativeId, string $method, array $payload, ?string $note = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ee_analyses (project_id, alternative_id, scenario_id, method, payload_json, note)
             VALUES (?,?,NULL,?,?,?)'
        );
        $stmt->execute([
            $projectId,
            $alternativeId,
            $method,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR),
            $note,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /** @return array<int,array<string,mixed>> */
    public function listForProject(int $projectId, int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        $stmt = $this->db->prepare(
            'SELECT id, project_id, alternative_id, method, note, computed_at
             FROM ee_analyses WHERE project_id=? ORDER BY computed_at DESC, id DESC LIMIT ' . $limit
        );
        $stmt->execute([$projectId]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
