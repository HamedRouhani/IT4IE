<?php
namespace App\Software\Or\Controllers;

use App\Software\Or\Core\Controller;
use App\Software\Or\Models\ILPProject;

require_once __DIR__ . '/../Helpers/ILPEngine.php';
require_once __DIR__ . '/../Helpers/DualSimplexEngine.php';

use App\Software\Or\Helpers\ILPEngine;
use App\Software\Or\Helpers\DualSimplexEngine;

class ILPController extends Controller
{
    private $ilpModel;

    public function __construct()
    {
        parent::__construct();
        $this->ilpModel = new ILPProject();
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->view('ilp/index', [
            'pageTitle'   => 'برنامه‌ریزی خطی عدد صحیح (ILP)',
            'currentPage' => 'ilp',
            'projects'    => $this->ilpModel->getByUser($this->currentUserId, 50),
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
            $integer_vars = $payload['integer_vars'] ?? [];
            $sense = $payload['sense'] ?? 'maximize';
            $save = !empty($payload['save']);
            $name = trim($payload['name'] ?? '');
            $project_id = (int)($payload['project_id'] ?? 0); // برای ویرایش

            if (empty($c) || empty($A) || empty($b)) {
                throw new \Exception('ضرایب تابع هدف، ماتریس محدودیت‌ها و مقادیر سمت راست الزامی هستند.');
            }

            if (empty($integer_vars)) {
                throw new \Exception('حداقل یک متغیر باید به‌عنوان عدد صحیح انتخاب شود.');
            }

            $result = ILPEngine::solve($c, $A, $b, $constraints_types, $integer_vars, $sense);

            if ($result['status'] !== 'optimal') {
                throw new \Exception($result['message'] ?? 'مسئله قابل حل نیست.');
            }

            $projectId = null;
            if ($save) {
                $projectData = [
                    'user_id'             => $this->currentUserId,
                    'name'                => $name ?: ("ILP - " . date('Y-m-d H:i')),
                    'description'         => $payload['description'] ?? null,
                    'objective_coeffs'    => json_encode($c, JSON_UNESCAPED_UNICODE),
                    'constraint_matrix'   => json_encode($A, JSON_UNESCAPED_UNICODE),
                    'rhs_values'          => json_encode($b, JSON_UNESCAPED_UNICODE),
                    'constraints_types'   => json_encode($constraints_types, JSON_UNESCAPED_UNICODE),
                    'integer_vars'        => json_encode($integer_vars, JSON_UNESCAPED_UNICODE),
                    'sense'               => $sense,
                    'result_json'         => json_encode($result, JSON_UNESCAPED_UNICODE),
                ];

                if ($project_id > 0) {
                    // ویرایش پروژه موجود
                    $this->ilpModel->updateILP($project_id, $this->currentUserId, $projectData);
                    $projectId = $project_id;
                    $this->logActivity('update_ilp', 'ilp', $projectId);
                } else {
                    // ایجاد پروژه جدید
                    $projectId = $this->ilpModel->createILP($projectData);
                    $this->logActivity('create_ilp', 'ilp', $projectId);
                }
            }

            ob_end_clean();
            $this->json(['success' => true, 'result' => $result, 'project_id' => $projectId]);

        } catch (\Throwable $e) {
            $errorOutput = ob_get_clean();
            error_log('OR ILP Error: ' . $e->getMessage());
            $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * نمایش جزئیات پروژه
     */
    public function show(int $id): void
    {
        $this->requireAuth();
        $project = $this->ilpModel->findOwned($id, $this->currentUserId);
        if (!$project) {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('controller=ilp');
            return;
        }

        $this->view('ilp/show', [
            'pageTitle'         => 'گزارش ILP: ' . $project['name'],
            'currentPage'       => 'ilp',
            'project'           => $project,
            'result'            => json_decode($project['result_json'] ?? '{}', true) ?: [],
            'c'                 => json_decode($project['objective_coeffs'] ?? '[]', true) ?: [],
            'A'                 => json_decode($project['constraint_matrix'] ?? '[]', true) ?: [],
            'b'                 => json_decode($project['rhs_values'] ?? '[]', true) ?: [],
            'constraints_types' => json_decode($project['constraints_types'] ?? '[]', true) ?: [],
            'integer_vars'      => json_decode($project['integer_vars'] ?? '[]', true) ?: [],
        ]);
    }

    /**
     * فرم ویرایش پروژه
     */
    public function edit(int $id): void
    {
        $this->requireAuth();
        $project = $this->ilpModel->findOwned($id, $this->currentUserId);
        if (!$project) {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('controller=ilp');
            return;
        }

        $this->view('ilp/edit', [
            'pageTitle'         => 'ویرایش پروژه ILP: ' . $project['name'],
            'currentPage'       => 'ilp',
            'project'           => $project,
            'c'                 => json_decode($project['objective_coeffs'] ?? '[]', true) ?: [],
            'A'                 => json_decode($project['constraint_matrix'] ?? '[]', true) ?: [],
            'b'                 => json_decode($project['rhs_values'] ?? '[]', true) ?: [],
            'constraints_types' => json_decode($project['constraints_types'] ?? '[]', true) ?: [],
            'integer_vars'      => json_decode($project['integer_vars'] ?? '[]', true) ?: [],
        ]);
    }

    public function delete(int $id): void
    {
        $this->requireAuth();
        if ($this->ilpModel->deleteOwned($id, $this->currentUserId)) {
            $this->logActivity('delete_ilp', 'ilp', $id);
            $this->flashSuccess('پروژه حذف شد.');
        } else {
            $this->flashError('پروژه یافت نشد.');
        }
        $this->redirect('controller=ilp');
    }
}