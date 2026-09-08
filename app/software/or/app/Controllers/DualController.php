<?php
namespace App\Software\Or\Controllers;

use App\Software\Or\Core\Controller;
use App\Software\Or\Models\DualProject;

require_once __DIR__ . '/../Helpers/DualSimplexEngine.php';
use App\Software\Or\Helpers\DualSimplexEngine;

class DualController extends Controller
{
    private $dualModel;

    public function __construct()
    {
        parent::__construct();
        $this->dualModel = new DualProject();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('dual/index', [
            'pageTitle'   => 'روش دوگان و تحلیل اقتصادی',
            'currentPage' => 'dual',
            'projects'    => $this->dualModel->getByUser($this->currentUserId, 20),
        ]);
    }

    public function solve(): void
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'درخواست نامعتبر است.'], 405);
            return;
        }

        ob_start();
        try {
            $payload = json_decode(file_get_contents('php://input'), true) ?: $_POST;
            
            $c = $payload['c'] ?? [];
            $A = $payload['A'] ?? [];
            $b = $payload['b'] ?? [];
            $constraints_types = $payload['constraints_types'] ?? array_fill(0, count($A), '<=');
            $sense = $payload['sense'] ?? 'maximize';
            $save = !empty($payload['save']);
            $name = trim($payload['name'] ?? '');

            if (empty($c) || empty($A) || empty($b)) {
                throw new \Exception('ضرایب تابع هدف، ماتریس محدودیت‌ها و مقادیر سمت راست الزامی هستند.');
            }

            if (count($A) !== count($b) || count($A) !== count($constraints_types)) {
                throw new \Exception('تعداد محدودیت‌ها با تعداد مقادیر سمت راست یا انواع محدودیت‌ها همخوانی ندارد.');
            }

            $result = DualSimplexEngine::solve($c, $A, $b, $constraints_types, $sense);

            if (!isset($result['status']) || $result['status'] !== 'optimal') {
                throw new \Exception($result['message'] ?? 'مسئله قابل حل نیست (ممکن است ناموجه یا کران‌دار نباشد).');
            }

            $projectId = null;
            if ($save) {
                $projectId = $this->dualModel->createDual([
                    'user_id'             => $this->currentUserId,
                    'name'                => $name ?: ("تحلیل دوگان - " . date('Y-m-d H:i')),
                    'description'         => $payload['description'] ?? null,
                    'objective_coeffs'    => json_encode($c, JSON_UNESCAPED_UNICODE),
                    'constraint_matrix'   => json_encode($A, JSON_UNESCAPED_UNICODE),
                    'rhs_values'          => json_encode($b, JSON_UNESCAPED_UNICODE),
                    'constraints_types'   => json_encode($constraints_types, JSON_UNESCAPED_UNICODE),
                    'sense'               => $sense,
                    'result_json'         => json_encode($result, JSON_UNESCAPED_UNICODE),
                ]);
                $this->logActivity('create_dual', 'dual', $projectId);
            }

            ob_end_clean();
            $this->json(['success' => true, 'result' => $result, 'project_id' => $projectId]);

        } catch (\Throwable $e) {
            $errorOutput = ob_get_clean();
            error_log('OR Dual Error: ' . $e->getMessage() . ' | Output: ' . $errorOutput);
            
            $errorMsg = $e->getMessage();
            if (!empty($errorOutput)) {
                $errorMsg .= ' | جزئیات فنی: ' . strip_tags($errorOutput);
            }
            
            $this->json(['success' => false, 'error' => $errorMsg], 500);
        }
    }

    public function show(int $id): void
    {
        $this->requireAuth();
        $project = $this->dualModel->findOwned($id, $this->currentUserId);
        if (!$project) {
            $this->flashError('پروژه یافت نشد یا دسترسی ندارید.');
            $this->redirect('controller=dual');
            return;
        }

        $this->view('dual/show', [
            'pageTitle'         => 'گزارش تحلیل دوگان: ' . $project['name'],
            'currentPage'       => 'dual',
            'project'           => $project,
            'result'            => json_decode($project['result_json'] ?? '{}', true) ?: [],
            'c'                 => json_decode($project['objective_coeffs'] ?? '[]', true) ?: [],
            'A'                 => json_decode($project['constraint_matrix'] ?? '[]', true) ?: [],
            'b'                 => json_decode($project['rhs_values'] ?? '[]', true) ?: [],
            'constraints_types' => json_decode($project['constraints_types'] ?? '[]', true) ?: [],
        ]);
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        if ($this->dualModel->deleteOwned($id, $this->currentUserId)) {
            $this->logActivity('delete_dual', 'dual', $id);
            $this->flashSuccess('پروژه با موفقیت حذف شد.');
        } else {
            $this->flashError('پروژه یافت نشد.');
        }
        $this->redirect('controller=dual');
    }
}