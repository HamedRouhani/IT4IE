<?php
namespace App\Software\Statlab\Controllers;

use App\Software\Statlab\Core\Controller;
use App\Software\Statlab\Models\Project;
use App\Software\Statlab\Models\Dataset;
use App\Software\Statlab\Models\Result;

class ProjectController extends Controller
{
    private $projectModel;
    private $datasetModel;
    private $resultModel;

    public function __construct()
    {
        parent::__construct();
        $this->projectModel = new Project();
        $this->datasetModel = new Dataset();
        $this->resultModel  = new Result();
    }

    /**
     * لیست همه پروژه‌های کاربر
     */
    public function index(): void
    {
        $this->requireAuth();

        $projects = $this->projectModel->query(
            "SELECT p.*, 
                    (SELECT COUNT(*) FROM stat_datasets d WHERE d.project_id = p.id) as dataset_count,
                    (SELECT COUNT(*) FROM stat_results r WHERE r.project_id = p.id) as result_count
             FROM `{$this->projectModel->getTableName()}` p
             WHERE p.user_id = :uid
             ORDER BY p.updated_at DESC",
            ['uid' => $this->currentUserId]
        );

        $this->view('project/index', [
            'pageTitle'   => 'پروژه‌های آماری',
            'currentPage' => 'project',
            'projects'    => $projects,
        ]);
    }

    /**
     * ایجاد پروژه جدید (فرم)
     */
    public function create(): void
    {
        $this->requireAuth();

        $this->view('project/create', [
            'pageTitle'   => 'ایجاد پروژه جدید',
            'currentPage' => 'project',
        ]);
    }

    /**
     * ذخیره پروژه جدید
     */
    public function store(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }

        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;

            $projectId = $this->projectModel->create([
                'user_id'            => $this->currentUserId,
                'name'               => trim($payload['name'] ?? 'پروژه بدون نام'),
                'description'        => trim($payload['description'] ?? ''),
                'category_code'      => $payload['category'] ?? 'descriptive',
                'analysis_type_code' => $payload['analysis_type'] ?? null,
                'objective'          => $payload['objective'] ?? 'descriptive',
                'significance_level' => (float)($payload['alpha'] ?? 0.05),
                'status'             => 'draft',
            ]);

            $this->logActivity('create_project', 'project', $projectId);

            $this->json([
                'success'    => true,
                'project_id' => $projectId,
                'message'    => 'پروژه با موفقیت ایجاد شد.',
                'redirect'   => stat_url('controller=project&action=show&id=' . $projectId),
            ]);

        } catch (\Exception $e) {
            error_log("StatLab Store Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * نمایش جزئیات پروژه
     */
    public function show(int $id): void
    {
        $this->requireAuth();

        $project = $this->projectModel->find($id);
        if (!$project || (int)$project['user_id'] !== (int)$this->currentUserId) {
            $this->flashError('پروژه یافت نشد یا دسترسی ندارید.');
            $this->redirect('controller=project');
            return;
        }

        $datasets = $this->datasetModel->getByProject($id);
        $results  = $this->resultModel->getLatestByProject($id, 10);

        $this->view('project/show', [
            'pageTitle'   => $project['name'],
            'currentPage' => 'project',
            'project'     => $project,
            'datasets'    => $datasets,
            'results'     => $results,
        ]);
    }

    /**
     * حذف پروژه
     */
    public function delete(int $id): void
    {
        $this->requireAuth();

        $project = $this->projectModel->find($id);
        if (!$project || (int)$project['user_id'] !== (int)$this->currentUserId) {
            $this->flashError('دسترسی مجاز نیست.');
            $this->redirect('controller=project');
            return;
        }

        // حذف cascade توسط FK در دیتابیس انجام می‌شود
        $this->projectModel->delete($id);

        $this->logActivity('delete_project', 'project', $id);
        $this->flashSuccess('پروژه با موفقیت حذف شد.');
        $this->redirect('controller=project');
    }

    /**
     * فرم ویرایش پروژه
     */
    public function edit(int $id): void
    {
        $this->requireAuth();

        $project = $this->projectModel->find($id);
        if (!$project || !$this->authorizeOwnership((int)$project['user_id'])) {
            return;
        }

        // دریافت همه datasetهای پروژه
        $datasets = $this->datasetModel->query(
            "SELECT d.*, 
                    (SELECT COUNT(*) FROM stat_results r WHERE r.dataset_id = d.id) as result_count
            FROM `{$this->datasetModel->getTableName()}` d
            WHERE d.project_id = :pid
            ORDER BY d.id ASC",
            ['pid' => $id]
        );

        // دریافت نتایج تجمیعی
        $accumulatedResults = [];
        if (!empty($project['result_data'])) {
            $accumulatedResults = json_decode($project['result_data'], true) ?: [];
        }

        // دریافت همه نتایج پروژه
        $results = $this->resultModel->query(
            "SELECT r.*, ds.name as dataset_name
            FROM `{$this->resultModel->getTableName()}` r
            LEFT JOIN `{$this->datasetModel->getTableName()}` ds ON r.dataset_id = ds.id
            WHERE r.project_id = :pid
            ORDER BY r.id DESC",
            ['pid' => $id]
        );

        $this->view('project/edit', [
            'pageTitle'          => 'ویرایش: ' . $project['name'],
            'currentPage'        => 'project',
            'project'            => $project,
            'datasets'           => $datasets,
            'results'            => $results,
            'accumulatedResults' => $accumulatedResults,
        ]);
    }

    /**
     * ذخیره تغییرات پروژه (AJAX)
     */
    public function update(int $id): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }

        $project = $this->projectModel->find($id);
        if (!$project) {
            $this->json(['success' => false, 'error' => 'پروژه یافت نشد.'], 404);
            return;
        }

        if ((int)$project['user_id'] !== (int)$this->currentUserId) {
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز.'], 403);
            return;
        }

        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;

            $name        = trim($payload['name'] ?? $project['name']);
            $description = trim($payload['description'] ?? $project['description']);
            $category    = trim($payload['category'] ?? $project['category_code']);
            $objective   = trim($payload['objective'] ?? $project['objective']);
            $alpha       = (float)($payload['alpha'] ?? $project['significance_level']);
            $action      = $payload['action'] ?? 'update_info'; // نوع عملیات

            // ─── عملیات ۱: حذف یک dataset ───
            if ($action === 'delete_dataset') {
                $datasetId = (int)($payload['dataset_id'] ?? 0);
                $dataset = $this->datasetModel->find($datasetId);
                
                if ($dataset && (int)$dataset['project_id'] === $id) {
                    // حذف نتیجه‌های مربوطه
                    $this->resultModel->execute(
                        "DELETE FROM `{$this->resultModel->getTableName()}` WHERE dataset_id = ?",
                        [$datasetId]
                    );

                    // حذف dataset
                    $this->datasetModel->delete($datasetId);
                    
                    // حذف از result_data تجمیعی
                    $accumulated = json_decode($project['result_data'] ?? '{}', true) ?: [];
                    unset($accumulated[$dataset['name']]);
                    
                    $this->projectModel->update($id, [
                        'result_data' => json_encode($accumulated, JSON_UNESCAPED_UNICODE),
                    ]);
                    
                    $this->logActivity('delete_dataset', 'dataset', $datasetId);
                    $this->json(['success' => true, 'message' => 'متغیر با موفقیت حذف شد.']);
                    return;
                }
                $this->json(['success' => false, 'error' => 'متغیر یافت نشد.'], 404);
                return;
            }

            // ─── عملیات ۲: حذف یک نتیجه ───
            if ($action === 'delete_result') {
                $resultId = (int)($payload['result_id'] ?? 0);
                $result = $this->resultModel->find($resultId);
                
                if ($result && (int)$result['project_id'] === $id) {
                    $this->resultModel->delete($resultId);
                    $this->logActivity('delete_result', 'result', $resultId);
                    $this->json(['success' => true, 'message' => 'نتیجه حذف شد.']);
                    return;
                }
                $this->json(['success' => false, 'error' => 'نتیجه یافت نشد.'], 404);
                return;
            }

            // ─── عملیات ۳: ویرایش داده‌های یک dataset ───
            if ($action === 'update_dataset_data') {
                $datasetId = (int)($payload['dataset_id'] ?? 0);
                $newDataText = trim($payload['data'] ?? '');
                
                $dataset = $this->datasetModel->find($datasetId);
                if (!$dataset || (int)$dataset['project_id'] !== $id) {
                    $this->json(['success' => false, 'error' => 'متغیر یافت نشد.'], 404);
                    return;
                }
                
                // Parse داده‌های جدید (از همان تابع DescriptiveController استفاده می‌کنیم)
                $newData = $this->parseSimpleData($newDataText);
                if (count($newData) < 2) {
                    $this->json(['success' => false, 'error' => 'حداقل ۲ داده معتبر نیاز است.']);
                    return;
                }
                
                // به‌روزرسانی dataset
                $this->datasetModel->update($datasetId, [
                    'data_json'   => json_encode($newData, JSON_UNESCAPED_UNICODE),
                    'sample_size' => count($newData),
                ]);
                
                // محاسبه مجدد آمار
                $stats = \App\Software\Statlab\Helpers\StatEngine::describe($newData);
                
                // به‌روزرسانی result مربوطه
                $this->resultModel->execute(
                    "UPDATE `{$this->resultModel->getTableName()}` 
                    SET output_data = ?, interpretation_fa = ?
                    WHERE dataset_id = ?",
                    [
                        json_encode($stats, JSON_UNESCAPED_UNICODE),
                        $this->generateSimpleInterpretation($stats),
                        $datasetId,
                    ]
                );
                
                // به‌روزرسانی result_data تجمیعی
                $accumulated = json_decode($project['result_data'] ?? '{}', true) ?: [];
                if (isset($accumulated[$dataset['name']])) {
                    $accumulated[$dataset['name']]['stats'] = $stats;
                    $accumulated[$dataset['name']]['sample_size'] = count($newData);
                    $accumulated[$dataset['name']]['analyzed_at'] = date('Y-m-d H:i:s');
                }
                
                $this->projectModel->update($id, [
                    'result_data' => json_encode($accumulated, JSON_UNESCAPED_UNICODE),
                ]);
                
                $this->logActivity('update_dataset_data', 'dataset', $datasetId);
                $this->json([
                    'success' => true,
                    'message' => 'داده‌ها با موفقیت به‌روزرسانی و آمار مجدداً محاسبه شد.',
                    'stats'   => $stats,
                    'count'   => count($newData),
                ]);
                return;
            }

            // ─── عملیات ۴: ویرایش اطلاعات کلی پروژه ───
            if (mb_strlen($name) < 2) {
                $this->json(['success' => false, 'error' => 'نام پروژه باید حداقل ۲ کاراکتر باشد.']);
                return;
            }

            $this->projectModel->update($id, [
                'name'               => $name,
                'description'        => $description,
                'category_code'      => $category,
                'objective'          => $objective,
                'significance_level' => $alpha,
            ]);

            $this->logActivity('update_project', 'project', $id);

            $this->json([
                'success'  => true,
                'message'  => 'پروژه با موفقیت به‌روزرسانی شد.',
                'redirect' => stat_url('controller=project&action=show&id=' . $id),
            ]);

        } catch (\Throwable $e) {
            error_log("StatLab Update Error: " . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Parse ساده داده‌ها (برای استفاده در update)
     */
    private function parseSimpleData(string $text): array
    {
        $data = [];
        $parts = preg_split('/[\n\r,;|\t]+/', $text);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') continue;
            
            $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
            $arabic  = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
            $english = ['0','1','2','3','4','5','6','7','8','9'];
            
            $part = str_replace($persian, $english, $part);
            $part = str_replace($arabic, $english, $part);
            $part = preg_replace('/[^0-9.\-+eE]/', '', $part);
            
            if ($part !== '' && is_numeric($part)) {
                $data[] = (float)$part;
            }
        }
        
        return $data;
    }

    /**
     * تفسیر ساده برای به‌روزرسانی سریع
     */
    private function generateSimpleInterpretation(array $stats): string
    {
        $parts = [];
        $parts[] = "📊 تحلیل {$stats['count']} داده";
        $parts[] = "میانگین: " . number_format($stats['mean'], 4);
        $parts[] = "انحراف معیار: " . number_format($stats['std'], 4);
        
        if (count($stats['outliers']) > 0) {
            $parts[] = "⚠️ " . count($stats['outliers']) . " داده پرت شناسایی شد";
        }
        
        return implode(' | ', $parts);
    }

    /**
     * خروجی JSON مجموعه‌داده‌های یک پروژه (برای بارگذاری در فرم‌ها)
     */
    public function datasets(int $id): void
    {
        $this->requireAuth();

        $project = $this->projectModel->find($id);
        if (!$project || (int)$project['user_id'] !== (int)$this->currentUserId) {
            $this->json(['success' => false, 'error' => 'دسترسی به پروژه مجاز نیست.'], 403);
            return;
        }

        $datasets = $this->datasetModel->getByProject($id);
        foreach ($datasets as &$d) {
            $d['data'] = json_decode($d['data_json'] ?? '[]', true) ?: [];
            unset($d['data_json']); // حجم کم + امنیت
        }
        unset($d);

        $this->json(['success' => true, 'datasets' => $datasets]);
    }
}