<?php
namespace App\Software\Or\Controllers;

use App\Software\Or\Core\Controller;
use App\Software\Or\Models\QueueingProject;
use App\Software\Or\Helpers\QueueingEngine as QE;

class QueueingController extends Controller
{
    private $queueModel;

    public function __construct()
    {
        parent::__construct();
        $this->queueModel = new QueueingProject();
    }

    /** لیست + فرم حل سریع */
    public function index(): void
    {
        $this->requireAuth();
        $this->view('queueing/index', [
            'pageTitle'   => 'نظریه صف (Queueing Theory)',
            'currentPage' => 'queueing',
            'models'      => QE::list(),
            'projects'    => $this->queueModel->getByUser($this->currentUserId, 30),
        ]);
    }

    /** حل مدل (AJAX) */
    public function solve(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر'], 405);
            return;
        }
        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            $code    = $payload['model'] ?? 'MM1';
            $params  = $this->normalizeParams($code, $payload);
            $result  = QE::solve($code, $params);

            $sensitivity = [];
            if ($result['status'] === 'ok') {
                $sensitivity = QE::sensitivity($code, $params);
            }

            $projectId = null;
            if (!empty($payload['save']) && $result['status'] === 'ok') {
                $projectId = $this->queueModel->createQueueing([
                    'user_id'     => $this->currentUserId,
                    'name'        => trim($payload['name'] ?? '') ?: (QE::list()[$code]['name'] . ' - ' . date('Y-m-d H:i')),
                    'description' => $payload['description'] ?? null,
                    'model_code'  => $code,
                    'lambda'      => $params['lambda'],
                    'mu'          => $params['mu'],
                    'servers'     => $params['servers'],
                    'capacity'    => $params['capacity'],
                    'service_std' => $params['service_std'],
                    'result_json' => json_encode($result, JSON_UNESCAPED_UNICODE),
                ]);
                $this->logActivity('create_queueing', 'queueing', $projectId);
            }

            $this->json(['success' => true, 'result' => $result, 'sensitivity' => $sensitivity, 'project_id' => $projectId]);
        } catch (\Throwable $e) {
            error_log('OR Queueing Error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /** ✅ مشاهده جزئیات پروژه */
    public function show(int $id): void
    {
        $this->requireAuth();
        $project = $this->queueModel->findOwned($id, $this->currentUserId);
        if (!$project) {
            $this->flashError('پروژه صف یافت نشد یا دسترسی ندارید.');
            $this->redirect('controller=queueing');
            return;
        }

        $tab = $_GET['tab'] ?? 'result';
        $params = [
            'lambda'      => (float)$project['lambda'],
            'mu'          => (float)$project['mu'],
            'servers'     => (int)$project['servers'],
            'capacity'    => $project['capacity'] !== null ? (int)$project['capacity'] : null,
            'service_std' => (float)($project['service_std'] ?? 0),
        ];

        // ✅ محاسبه تازه از پارامترهای ذخیره‌شده (پروژه‌های قدیمی هم نمودار می‌گیرند)
        $result = json_decode($project['result_json'] ?? '{}', true) ?: [];
        try {
            $fresh = QE::solve($project['model_code'], $params);
            if (($fresh['status'] ?? '') === 'ok') $result = $fresh;
        } catch (\Throwable $e) { /* نتیجه ذخیره‌شده نمایش داده می‌شود */ }

        $sensitivity = [];
        try {
            $sensitivity = QE::sensitivity($project['model_code'], $params);
        } catch (\Throwable $e) { $sensitivity = []; }

        $this->view('queueing/show', [
            'pageTitle'   => 'گزارش صف: ' . $project['name'],
            'currentPage' => 'queueing',
            'project'     => $project,
            'result'      => $result,
            'sensitivity' => $sensitivity,
            'tab'         => $tab,
            'modelName'   => QE::list()[$project['model_code']]['name'] ?? $project['model_code'],
        ]);
    }

    /** ✅ فرم ویرایش پروژه */
    public function edit(int $id): void
    {
        $this->requireAuth();
        $project = $this->queueModel->findOwned($id, $this->currentUserId);
        if (!$project) {
            $this->flashError('پروژه صف یافت نشد یا دسترسی ندارید.');
            $this->redirect('controller=queueing');
            return;
        }
        $this->view('queueing/edit', [
            'pageTitle'   => 'ویرایش پروژه صف',
            'currentPage' => 'queueing',
            'project'     => $project,
            'models'      => QE::list(),
        ]);
    }

    /** ✅ ذخیره ویرایش (حل مجدد خودکار) */
    public function update(int $id): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('controller=queueing');
            return;
        }
        $project = $this->queueModel->findOwned($id, $this->currentUserId);
        if (!$project) {
            $this->flashError('پروژه صف یافت نشد.');
            $this->redirect('controller=queueing');
            return;
        }

        try {
            $code   = $_POST['model'] ?? $project['model_code'];
            $params = $this->normalizeParams($code, $_POST);
            $result = QE::solve($code, $params);

            if ($result['status'] !== 'ok') {
                $this->flashError($result['message'] ?? 'پارامترها منجر به سیستم ناپایدار می‌شوند؛ ذخیره نشد.');
                $this->redirect('controller=queueing&action=edit&id=' . $id);
                return;
            }

            $this->queueModel->updateQueueing($id, [
                'name'        => trim($_POST['name'] ?? '') ?: $project['name'],
                'description' => $_POST['description'] ?? null,
                'model_code'  => $code,
                'lambda'      => $params['lambda'],
                'mu'          => $params['mu'],
                'servers'     => $params['servers'],
                'capacity'    => $params['capacity'],
                'service_std' => $params['service_std'],
                'status'      => 'solved',
                'result_json' => json_encode($result, JSON_UNESCAPED_UNICODE),
            ]);

            $this->logActivity('update_queueing', 'queueing', $id);
            $this->flashSuccess('پروژه به‌روزرسانی و مجدداً حل شد.');
            $this->redirect('controller=queueing&action=show&id=' . $id);

        } catch (\Throwable $e) {
            $this->flashError($e->getMessage());
            $this->redirect('controller=queueing&action=edit&id=' . $id);
        }
    }

    /** حذف پروژه */
    public function delete(int $id): void
    {
        $this->requireAuth();
        $row = $this->queueModel->findOwned($id, $this->currentUserId);
        if (!$row) {
            $this->flashError('پروژه یافت نشد یا دسترسی ندارید.');
            $this->redirect('controller=queueing');
            return;
        }
        $this->queueModel->deleteOwned($id, $this->currentUserId);
        $this->logActivity('delete_queueing', 'queueing', $id);
        $this->flashSuccess('پروژه صف با موفقیت حذف شد.');
        $this->redirect('controller=queueing');
    }

    /** نرمال‌سازی پارامترها بر اساس مدل */
    private function normalizeParams(string $code, array $in): array
    {
        $lambda = (float)($in['lambda'] ?? 0);
        $mu     = (float)($in['mu'] ?? 0);
        $servers = in_array($code, ['MMc', 'MMcK']) ? max(1, (int)($in['servers'] ?? 1)) : 1;
        $capacity = in_array($code, ['MM1K', 'MMcK']) ? max(1, (int)($in['capacity'] ?? 2)) : null;
        $std = ($code === 'MG1') ? max(0, (float)($in['service_std'] ?? 0)) : 0.0;
        return [
            'lambda' => $lambda, 'mu' => $mu,
            'servers' => $servers, 'capacity' => $capacity, 'service_std' => $std,
        ];
    }
}