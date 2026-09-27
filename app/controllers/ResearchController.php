<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Setting;
use App\Models\WorkflowTemplate;
use PDO;
use Throwable;

class ResearchController extends Controller
{
    private const ALLOWED_TOOLS = ['statlab-analyzer', 'mcdm-analyzer'];

    public function index()
    {
        $toolSlug = $_GET['tool'] ?? null;
        if (!in_array($toolSlug, self::ALLOWED_TOOLS, true)) $toolSlug = null;

        $this->render('research/index', [
            'title' => 'نمونه‌های پژوهشی مهندسی صنایع | IT4IE',
            'settings' => (new Setting())->getAll(),
            'templates' => (new WorkflowTemplate())->getPublished($toolSlug),
            'activeTool' => $toolSlug,
            'hideSidebar' => true,
            'hideFooter' => true,
        ]);
    }

    public function show($slug)
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->start($slug);
            return;
        }

        $template = (new WorkflowTemplate())->findPublishedBySlug($slug);
        if (!$template) {
            http_response_code(404);
            $this->render('errors/404', [
                'title' => 'نمونه پژوهشی پیدا نشد | IT4IE',
                'settings' => (new Setting())->getAll(),
                'hideSidebar' => true,
                'hideFooter' => true,
            ]);
            return;
        }

        $payload = json_decode($template['input_payload'] ?? '', true);
        $template['payload'] = is_array($payload) ? $payload : [];
        $toolNames = [
            'statlab-analyzer' => 'StatLab · تحلیل آماری',
            'mcdm-analyzer' => 'MCDM · تصمیم‌گیری چندمعیاره',
        ];

        $this->render('research/show', [
            'title' => htmlspecialchars($template['title'], ENT_QUOTES, 'UTF-8') . ' | نمونه پژوهشی IT4IE',
            'settings' => (new Setting())->getAll(),
            'template' => $template,
            'toolName' => $toolNames[$template['tool_slug']] ?? 'ابزار پژوهشی',
            'isLoggedIn' => isset($_SESSION['user_id']),
            'csrfField' => $this->csrfField(),
            'hideSidebar' => true,
            'hideFooter' => true,
        ]);
    }

    public function start($slug)
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            return;
        }
        if (!$this->verifyCsrf()) {
            http_response_code(403);
            $_SESSION['error'] = 'درخواست معتبر نیست. صفحه را تازه‌سازی کنید و دوباره تلاش کنید.';
            $this->redirect('/research/' . rawurlencode($slug));
        }
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_after_login'] = '/research/' . rawurlencode($slug);
            $_SESSION['auth_message'] = 'برای ساخت پروژه از نمونه، ابتدا وارد حساب خود شوید.';
            $this->redirect('/login');
        }

        $template = (new WorkflowTemplate())->findPublishedBySlug($slug);
        if (!$template || !in_array($template['tool_slug'], self::ALLOWED_TOOLS, true)) {
            http_response_code(404);
            $this->redirect('/research');
        }

        try {
            $payload = json_decode($template['input_payload'] ?? '', true, 512, JSON_THROW_ON_ERROR);
            $db = Database::getInstance();
            $db->beginTransaction();
            $userId = (int) $_SESSION['user_id'];
            $projectName = mb_substr($template['title'] . ' · ' . date('Y-m-d H:i'), 0, 180, 'UTF-8');

            if ($template['tool_slug'] === 'statlab-analyzer') {
                $projectId = $this->createStatlabProject($db, $userId, $projectName, $template, $payload);
                $target = '/software/statlab-analyzer/?controller=project&action=show&id=' . $projectId;
            } else {
                $projectId = $this->createMcdmProject($db, $userId, $projectName, $template, $payload);
                $target = '/software/mcdm-analyzer/?controller=project&action=show&id=' . $projectId;
            }

            $db->commit();
            $this->redirect($target);
        } catch (Throwable $e) {
            if (isset($db) && $db instanceof PDO && $db->inTransaction()) $db->rollBack();
            error_log('ResearchController::start: ' . $e->getMessage());
            $_SESSION['error'] = 'ساخت پروژه از نمونه انجام نشد. لطفاً دوباره تلاش کنید.';
            $this->redirect('/research/' . rawurlencode($slug));
        }
    }

    private function createStatlabProject(PDO $db, int $userId, string $name, array $template, array $payload): int
    {
        $preview = $payload['preview'] ?? [];
        $columns = $preview['columns'] ?? [];
        $rows = $preview['rows'] ?? [];
        if (!is_array($columns) || count($columns) < 2 || !is_array($rows) || !$rows) {
            throw new \RuntimeException('StatLab sample has no usable observations.');
        }
        $values = [];
        foreach ($rows as $row) {
            if (isset($row[1]) && is_numeric($row[1])) $values[] = (float) $row[1];
        }
        if (!$values) throw new \RuntimeException('StatLab sample has no numeric values.');

        $stmt = $db->prepare("INSERT INTO stat_projects (user_id, name, description, category_code, analysis_type_code, objective, significance_level, status) VALUES (?, ?, ?, 'descriptive', ?, 'descriptive', 0.05, 'draft')");
        $stmt->execute([$userId, $name, $payload['problem_statement'] ?? '', $template['method_code'] ?? 'summary_stats']);
        $projectId = (int) $db->lastInsertId();
        foreach (array_slice($columns, 1, null, true) as $columnIndex => $columnName) {
            $series = [];
            foreach ($rows as $row) {
                if (isset($row[$columnIndex]) && is_numeric($row[$columnIndex])) $series[] = (float) $row[$columnIndex];
            }
            if (!$series) continue;
            $dataset = $db->prepare("INSERT INTO stat_datasets (project_id, user_id, name, source, data_json, variables, sample_size, variables_count, has_missing, missing_count) VALUES (?, ?, ?, 'sample', ?, ?, ?, 1, 0, 0)");
            $dataset->execute([$projectId, $userId, 'داده نمونه · ' . (string) $columnName, json_encode($series, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), json_encode([(string) $columnName], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), count($series)]);
        }
        return $projectId;
    }

    private function createMcdmProject(PDO $db, int $userId, string $name, array $template, array $payload): int
    {
        $preview = $payload['preview'] ?? [];
        $columns = $preview['columns'] ?? [];
        $rows = $preview['rows'] ?? [];
        if (!is_array($columns) || count($columns) < 2 || !is_array($rows) || !$rows) {
            throw new \RuntimeException('MCDM sample has no usable decision matrix.');
        }
        $method = $db->prepare('SELECT id FROM mcdm_methods WHERE code = ? LIMIT 1');
        $method->execute([$template['method_code'] ?? 'TOPSIS']);
        $methodId = $method->fetchColumn();
        $stmt = $db->prepare("INSERT INTO mcdm_projects (user_id, name, method_id, phase, industry, alternatives_count, criteria_count) VALUES (?, ?, ?, 'evaluation', 'education', ?, ?)");
        $stmt->execute([$userId, $name, $methodId ?: null, count($rows), count($columns) - 1]);
        $projectId = (int) $db->lastInsertId();

        $criteriaIds = [];
        $criterionStmt = $db->prepare('INSERT INTO mcdm_project_criteria (project_id, name, type, weight, is_active, sort_order) VALUES (?, ?, ?, ?, 1, ?)');
        foreach (array_slice($columns, 1) as $index => $label) {
            $isCost = preg_match('/هزینه|زمان|ریسک|cost|time/i', (string) $label) === 1;
            $criterionStmt->execute([$projectId, (string) $label, $isCost ? 'cost' : 'benefit', 1 / (count($columns) - 1), $index + 1]);
            $criteriaIds[] = (int) $db->lastInsertId();
        }
        $alternativeIds = [];
        $alternativeStmt = $db->prepare('INSERT INTO mcdm_project_alternatives (project_id, name, description, is_active, sort_order) VALUES (?, ?, ?, 1, ?)');
        foreach ($rows as $index => $row) {
            if (!is_array($row) || count($row) < count($columns)) continue;
            $alternativeStmt->execute([$projectId, (string) $row[0], 'گزینه نمونه از کتابخانه پژوهشی IT4IE', $index + 1]);
            $alternativeIds[] = (int) $db->lastInsertId();
            $currentAltId = end($alternativeIds);
            foreach ($criteriaIds as $criterionIndex => $criterionId) {
                if (!is_numeric($row[$criterionIndex + 1] ?? null)) continue;
                $db->prepare('INSERT INTO mcdm_project_evaluations (project_id, criterion_id, alternative_id, value) VALUES (?, ?, ?, ?)')->execute([$projectId, $criterionId, $currentAltId, (float) $row[$criterionIndex + 1]]);
            }
        }
        if (!$alternativeIds) throw new \RuntimeException('MCDM sample has no alternatives.');
        return $projectId;
    }
}
