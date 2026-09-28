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
    private const ALLOWED_TOOLS = ['statlab-analyzer', 'mcdm-analyzer', 'babok-analyzer', 'pmbok-analyzer', 'or-analyzer', 'hr-analyzer', 'pdm-analyzer', 'quality-analyzer'];

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
            'babok-analyzer' => 'BABOK · تحلیل کسب‌وکار',
            'pmbok-analyzer' => 'PMBOK · مدیریت پروژه',
            'or-analyzer' => 'تحقیق در عملیات',
            'hr-analyzer' => 'مدیریت منابع انسانی',
            'pdm-analyzer' => 'نگهداری و تعمیرات',
            'quality-analyzer' => 'مدیریت کیفیت',
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

        $previousHrSystem = $_SESSION['hr_active_system'] ?? null;
        $previousPdmSystem = $_SESSION['pdm_active_system'] ?? null;
        try {
            $payload = json_decode($template['input_payload'] ?? '', true, 512, JSON_THROW_ON_ERROR);
            $db = Database::getInstance();
            $db->beginTransaction();
            $userId = (int) $_SESSION['user_id'];
            $projectName = mb_substr($template['title'] . ' · ' . date('Y-m-d H:i') . ' · ' . strtoupper(bin2hex(random_bytes(2))), 0, 95, 'UTF-8');
            switch ($template['tool_slug']) {
                case 'statlab-analyzer':
                    $projectId = $this->createStatlabProject($db, $userId, $projectName, $template, $payload);
                    $target = '/software/statlab-analyzer/?controller=project&action=show&id=' . $projectId;
                    break;
                case 'mcdm-analyzer':
                    $projectId = $this->createMcdmProject($db, $userId, $projectName, $template, $payload);
                    $target = '/software/mcdm-analyzer/?controller=project&action=show&id=' . $projectId;
                    break;
                case 'babok-analyzer':
                    $projectId = $this->createBabokProject($db, $userId, $projectName, $payload);
                    $target = '/software/babok-analyzer/?route=projects_view&id=' . $projectId;
                    break;
                case 'pmbok-analyzer':
                    $projectId = $this->createPmbokProject($db, $userId, $projectName, $payload);
                    $target = '/software/pmbok-analyzer/?controller=project&action=show&id=' . $projectId;
                    break;
                case 'or-analyzer':
                    $projectId = $this->createQueueingProject($db, $userId, $projectName, $template, $payload);
                    $target = '/software/or-analyzer/?controller=queueing&action=show&id=' . $projectId;
                    break;
                case 'hr-analyzer':
                    $projectId = $this->createHrTraining($db, $userId, $projectName, $payload);
                    $target = '/software/hr-analyzer/?controller=training&action=show&id=' . $projectId;
                    break;
                case 'pdm-analyzer':
                    $projectId = $this->createPdmWorkOrder($db, $userId, $projectName, $payload);
                    $target = '/software/pdm-analyzer/?controller=workorder&action=show&id=' . $projectId;
                    break;
                case 'quality-analyzer':
                    $projectId = $this->createQualityProject($db, $userId, $projectName, $payload);
                    $target = '/software/quality-analyzer/?controller=project&action=show&id=' . $projectId;
                    break;
                default:
                    throw new \RuntimeException('Unsupported research module.');
            }

            $db->commit();
            $this->redirect($target);
        } catch (Throwable $e) {
            if (isset($db) && $db instanceof PDO && $db->inTransaction()) $db->rollBack();
            if ($previousHrSystem === null) unset($_SESSION['hr_active_system']); else $_SESSION['hr_active_system'] = $previousHrSystem;
            if ($previousPdmSystem === null) unset($_SESSION['pdm_active_system']); else $_SESSION['pdm_active_system'] = $previousPdmSystem;
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
    private function createBabokProject(PDO $db, int $userId, string $name, array $payload): int
    {
        $stmt = $db->prepare("INSERT INTO babok_projects (user_id, name, description, phase, stakeholder_count, methodology, industry) VALUES (?, ?, ?, 'analysis', 3, 'hybrid', 'education')");
        $stmt->execute([$userId, $name, $payload['problem_statement'] ?? 'پروژهٔ تمرینی BABOK؛ داده‌ها آموزشی هستند.']);
        $projectId = (int) $db->lastInsertId();
        $tasks = $db->query('SELECT id FROM babok_tasks ORDER BY id ASC LIMIT 5')->fetchAll(PDO::FETCH_COLUMN);
        if ($tasks) {
            $link = $db->prepare("INSERT INTO babok_project_tasks (user_id, project_id, task_id, status, notes) VALUES (?, ?, ?, 'not_started', ?)");
            foreach ($tasks as $taskId) $link->execute([$userId, $projectId, (int) $taskId, 'وظیفهٔ پیشنهادی از نمونهٔ آموزشی IT4IE']);
        }
        return $projectId;
    }

    private function createPmbokProject(PDO $db, int $userId, string $name, array $payload): int
    {
        $stmt = $db->prepare("INSERT INTO pmbok_projects (user_id, name, description, phase, stakeholder_count, budget, currency, methodology, industry) VALUES (?, ?, ?, 'planning', 4, 250000000, 'IRR', 'hybrid', 'education')");
        $stmt->execute([$userId, $name, $payload['problem_statement'] ?? 'پروژهٔ تمرینی PMBOK؛ داده‌ها آموزشی هستند.']);
        $projectId = (int) $db->lastInsertId();
        $tasks = $db->query('SELECT id FROM pmbok_tasks ORDER BY id ASC LIMIT 5')->fetchAll(PDO::FETCH_COLUMN);
        if ($tasks) {
            $link = $db->prepare("INSERT INTO pmbok_project_tasks (user_id, project_id, task_id, status, planned_hours, percent_complete, notes) VALUES (?, ?, ?, 'not_started', 4, 0, ?)");
            foreach ($tasks as $taskId) $link->execute([$userId, $projectId, (int) $taskId, 'وظیفهٔ پیشنهادی از نمونهٔ آموزشی IT4IE']);
        }
        $risk = $db->prepare("INSERT INTO pmbok_risks (user_id, project_id, title, description, probability, impact, risk_score, response_strategy, response_plan, status) VALUES (?, ?, ?, ?, 'medium', 'high', 12, 'mitigate', ?, 'analyzed')");
        $risk->execute([$userId, $projectId, 'ریسک تأخیر تأمین', 'تأخیر در تأمین اقلام کلیدی می‌تواند برنامهٔ زمان‌بندی را جابه‌جا کند.', 'تأمین‌کنندهٔ جایگزین شناسایی و اقلام بحرانی زودتر سفارش‌گذاری شوند.']);
        return $projectId;
    }

    private function createQueueingProject(PDO $db, int $userId, string $name, array $payload): int
    {
        $params = $payload['parameters'] ?? [];
        $modelCode = $params['model_code'] ?? 'MM1';
        $lambda = (float) ($params['lambda'] ?? 8);
        $mu = (float) ($params['mu'] ?? 10);
        $servers = max(1, (int) ($params['servers'] ?? 1));
        $capacity = isset($params['capacity']) ? (int) $params['capacity'] : null;
        $serviceStd = isset($params['service_std']) ? (float) $params['service_std'] : null;
        require_once APP_PATH . '/software/or/app/Helpers/QueueingEngine.php';
        $result = \App\Software\Or\Helpers\QueueingEngine::solve($modelCode, ['lambda' => $lambda, 'mu' => $mu, 'servers' => $servers, 'capacity' => $capacity, 'service_std' => $serviceStd]);
        if (($result['status'] ?? '') !== 'ok') throw new \RuntimeException('Queueing example parameters did not produce a stable result.');
        $stmt = $db->prepare('INSERT INTO or_queueing_projects (user_id, name, description, model_code, `lambda`, `mu`, servers, capacity, service_std, status, result_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, \'solved\', ?)');
        $stmt->execute([$userId, $name, $payload['problem_statement'] ?? null, $modelCode, $lambda, $mu, $servers, $capacity, $serviceStd, json_encode($result, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)]);
        return (int) $db->lastInsertId();
    }

    private function createHrTraining(PDO $db, int $userId, string $name, array $payload): int
    {
        $systemQuery = $db->prepare("SELECT id FROM hr_systems WHERE owner_user_id = ? ORDER BY (status = 'active') DESC, id ASC LIMIT 1");
        $systemQuery->execute([$userId]);
        $systemId = (int) $systemQuery->fetchColumn();
        if (!$systemId) {
            $db->prepare("INSERT INTO hr_systems (owner_user_id, company_name, industry, company_size, description, status) VALUES (?, 'سازمان آموزشی نمونه IT4IE', 'education', 'small', 'سیستم ساخته‌شده برای نمونه‌های آموزشی IT4IE', 'active')")->execute([$userId]);
            $systemId = (int) $db->lastInsertId();
        }
        $_SESSION['hr_active_system'] = $systemId;
        $code = 'IT4IE-SAMPLE-' . strtoupper(bin2hex(random_bytes(4)));
        $stmt = $db->prepare("INSERT INTO hr_trainings (system_id, code, title, training_type, category, provider, description, objectives, content, target_audience, duration_hours, delivery_mode, priority, start_date, end_date, max_participants, current_participants, has_certificate, status) VALUES (?, ?, ?, 'workshop', 'بهبود فرایند', 'IT4IE · دادهٔ آموزشی', ?, ?, ?, 'دانشجویان و تحلیلگران مهندسی صنایع', 8, 'online_self_paced', 'normal', DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 8 DAY), 25, 0, 0, 'planned')");
        $problem = $payload['problem_statement'] ?? 'تمرین آموزشی برای آشنایی با تحلیل و بهبود فرایند.';
        $stmt->execute([$systemId, $code, $name, $problem, 'آشنایی با مسئله، شاخص‌گذاری و طراحی اقدام بهبود', 'تعریف مسئله؛ ترسیم فرایند؛ انتخاب شاخص؛ ارزیابی راهکار']);
        return (int) $db->lastInsertId();
    }

    private function createPdmWorkOrder(PDO $db, int $userId, string $name, array $payload): int
    {
        $systemQuery = $db->prepare("SELECT id FROM pm_systems WHERE owner_user_id = ? ORDER BY (status = 'active') DESC, id ASC LIMIT 1");
        $systemQuery->execute([$userId]);
        $systemId = (int) $systemQuery->fetchColumn();
        if (!$systemId) {
            $db->prepare("INSERT INTO pm_systems (owner_user_id, company_name, industry, description, status) VALUES (?, 'کارخانهٔ آموزشی نمونه IT4IE', 'manufacturing', 'سیستم ساخته‌شده برای نمونه‌های آموزشی IT4IE', 'active')")->execute([$userId]);
            $systemId = (int) $db->lastInsertId();
        }
        $_SESSION['pdm_active_system'] = $systemId;
        $token = strtoupper(bin2hex(random_bytes(4)));
        $assetCode = 'IT4IE-SAMPLE-' . $token;
        $assetStmt = $db->prepare("INSERT INTO pm_assets (system_id, asset_code, name, manufacturer, model, criticality, status, description) VALUES (?, ?, ?, 'سازندهٔ فرضی', 'مدل آموزشی', 'high', 'active', ?)");
        $assetStmt->execute([$systemId, $assetCode, $payload['asset_name'] ?? 'پمپ فرایندی آموزشی', $payload['problem_statement'] ?? 'دارایی ساختگی برای تمرین نگهداری و تعمیرات.']);
        $assetId = (int) $db->lastInsertId();
        $orderStmt = $db->prepare("INSERT INTO pm_work_orders (system_id, wo_number, asset_id, title, description, priority, status, planned_date) VALUES (?, ?, ?, ?, ?, 'high', 'open', DATE_ADD(CURDATE(), INTERVAL 1 DAY))");
        $orderStmt->execute([$systemId, 'IT4IE-WO-' . $token, $assetId, $payload['work_order_title'] ?? $name, $payload['problem_statement'] ?? 'بازرسی پیشگیرانهٔ تجهیز نمونه.']);
        return (int) $db->lastInsertId();
    }

    private function createQualityProject(PDO $db, int $userId, string $name, array $payload): int
    {
        $systemQuery = $db->prepare("SELECT id FROM qc_systems WHERE user_id = ? AND status = 'active' ORDER BY id ASC LIMIT 1");
        $systemQuery->execute([$userId]);
        $systemId = (int) $systemQuery->fetchColumn();
        if (!$systemId) {
            $db->prepare("INSERT INTO qc_systems (user_id, company_name, industry, company_size, description, status) VALUES (?, 'کارخانهٔ آموزشی نمونه IT4IE', 'manufacturing', 'small', 'سیستم ساختگی برای نمونه‌های آموزشی IT4IE', 'active')")->execute([$userId]);
            $systemId = (int) $db->lastInsertId();
        }
        $stmt = $db->prepare("INSERT INTO qc_projects (system_id, user_id, name, description, product_name, process_name, ctq, unit, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
        $stmt->execute([$systemId, $userId, $name, $payload['problem_statement'] ?? null, $payload['product_name'] ?? 'محصول نمونهٔ آموزشی', $payload['process_name'] ?? 'فرایند نمونه', $payload['ctq'] ?? 'ویژگی کیفی بحرانی', $payload['unit'] ?? 'mm']);
        $projectId = (int) $db->lastInsertId();
        $values = $payload['values'] ?? [49.8, 50.1, 50.0, 49.9, 50.2, 50.1, 49.7, 50.3, 50.0, 49.9];
        $chartType = $payload['chart_type'] ?? 'i_mr';
        $subgroupSize = $chartType === 'xbar_r' ? 4 : null;
        $dataset = $db->prepare('INSERT INTO qc_datasets (system_id, project_id, name, chart_type, subgroup_size, spec_lsl, spec_usl, spec_target, data_json, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $dataset->execute([$systemId, $projectId, 'دادهٔ آموزشی · ' . strtoupper($chartType), $chartType, $subgroupSize, $payload['spec_lsl'] ?? 49, $payload['spec_usl'] ?? 51, $payload['spec_target'] ?? 50, json_encode($values, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), 'مقادیر ساختگی آموزشی؛ بدون اطلاعات فرایند واقعی.']);
        $datasetId = (int) $db->lastInsertId();
        $measurement = $db->prepare('INSERT INTO qc_measurements (system_id, dataset_id, subgroup_no, sample_no, value, is_defective, defect_count) VALUES (?, ?, ?, ?, ?, 0, 0)');
        if ($chartType === 'xbar_r') {
            foreach ($values as $groupIndex => $group) {
                if (!is_array($group)) throw new \RuntimeException('X-bar/R example values must be grouped by subgroup.');
                foreach ($group as $sampleIndex => $value) $measurement->execute([$systemId, $datasetId, $groupIndex + 1, $sampleIndex + 1, (float) $value]);
            }
        } else {
            foreach ($values as $index => $value) $measurement->execute([$systemId, $datasetId, $index + 1, 1, (float) $value]);
        }
        return $projectId;
    }

}
