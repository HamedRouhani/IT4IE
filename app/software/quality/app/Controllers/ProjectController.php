<?php
namespace App\Software\Quality\Controllers;

use App\Core\Controller;
use App\Software\Quality\Models\System;
use App\Software\Quality\Models\Project;
use App\Software\Quality\Models\Dataset;

class ProjectController extends Controller
{
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function requireSystem(): array
    {
        $this->requireAuth();
        $userId = (int) $_SESSION['user_id'];
        $systemModel = new System();
        $system = $systemModel->findActiveByUser($userId);
        if (!$system) {
            $this->redirect(CURRENT_MODULE_URL . '?controller=system&action=create');
        }
        return $system;
    }

    public function index(): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];

        $model = new Project();
        $projects = $model->findBySystemWithStats($systemId);

        $this->renderSoftware('project/index', [
            'title'    => 'پروژه‌های کیفیت',
            'system'   => $system,
            'projects' => $projects,
        ], 'quality');
    }

    public function create(): void
    {
        $system = $this->requireSystem();

        $this->renderSoftware('project/form', [
            'title'   => 'ایجاد پروژه جدید',
            'system'  => $system,
            'project' => null,
            'action'  => 'store',
        ], 'quality');
    }

    public function store(): void
    {
        $system = $this->requireSystem();
        $userId = (int) $_SESSION['user_id'];
        $systemId = (int) $system['id'];

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(CURRENT_MODULE_URL . '?controller=project&action=create');
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $_SESSION['flash_error'] = 'نام پروژه الزامی است';
            $this->redirect(CURRENT_MODULE_URL . '?controller=project&action=create');
        }

        $model = new Project();
        $projectId = $model->createForSystem($systemId, $userId, [
            'name'         => $name,
            'description'  => trim($_POST['description']  ?? '') ?: null,
            'product_name' => trim($_POST['product_name'] ?? '') ?: null,
            'process_name' => trim($_POST['process_name'] ?? '') ?: null,
            'ctq'          => trim($_POST['ctq']          ?? '') ?: null,
            'unit'         => trim($_POST['unit']         ?? '') ?: null,
        ]);

        if ($this->isAjax()) {
            $this->json([
                'success'    => true,
                'project_id' => $projectId,
                'redirect'   => CURRENT_MODULE_URL . '?controller=project',
            ]);
        }

        $_SESSION['flash_success'] = 'پروژه با موفقیت ایجاد شد';
        $this->redirect(CURRENT_MODULE_URL . '?controller=project');
    }

    public function show($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $model = new Project();
        $project = $model->find($id);

        if (!$project || (int) $project['system_id'] !== $systemId) {
            http_response_code(404);
            echo 'پروژه یافت نشد';
            return;
        }

        $datasetModel = new Dataset();
        $datasets = $datasetModel->findByProject($id);

        $this->renderSoftware('project/show', [
            'title'    => 'جزئیات پروژه',
            'system'   => $system,
            'project'  => $project,
            'datasets' => $datasets,
        ], 'quality');
    }

    public function edit($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $model = new Project();
        $project = $model->find($id);

        if (!$project || (int) $project['system_id'] !== $systemId) {
            http_response_code(404);
            echo 'پروژه یافت نشد';
            return;
        }

        $this->renderSoftware('project/form', [
            'title'   => 'ویرایش پروژه',
            'system'  => $system,
            'project' => $project,
            'action'  => 'update',
        ], 'quality');
    }

    public function update($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(CURRENT_MODULE_URL . '?controller=project&action=edit&id=' . $id);
        }

        $model = new Project();
        $project = $model->find($id);

        if (!$project || (int) $project['system_id'] !== $systemId) {
            http_response_code(404);
            echo 'پروژه یافت نشد';
            return;
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $_SESSION['flash_error'] = 'نام پروژه الزامی است';
            $this->redirect(CURRENT_MODULE_URL . '?controller=project&action=edit&id=' . $id);
        }

        $model->update($id, [
            'name'         => $name,
            'description'  => trim($_POST['description']  ?? '') ?: null,
            'product_name' => trim($_POST['product_name'] ?? '') ?: null,
            'process_name' => trim($_POST['process_name'] ?? '') ?: null,
            'ctq'          => trim($_POST['ctq']          ?? '') ?: null,
            'unit'         => trim($_POST['unit']         ?? '') ?: null,
        ]);

        $_SESSION['flash_success'] = 'پروژه به‌روزرسانی شد';
        $this->redirect(CURRENT_MODULE_URL . '?controller=project');
    }

    public function archive($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $model = new Project();
        $project = $model->find($id);

        if (!$project || (int) $project['system_id'] !== $systemId) {
            http_response_code(403);
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز']);
        }

        $model->archive($id);

        if ($this->isAjax()) {
            $this->json(['success' => true]);
        }

        $_SESSION['flash_success'] = 'پروژه آرشیو شد';
        $this->redirect(CURRENT_MODULE_URL . '?controller=project');
    }

    public function delete($id): void
    {
        $system = $this->requireSystem();
        $systemId = (int) $system['id'];
        $id = (int) $id;

        $model = new Project();
        $project = $model->find($id);

        if (!$project || (int) $project['system_id'] !== $systemId) {
            http_response_code(403);
            $this->json(['success' => false, 'error' => 'دسترسی غیرمجاز']);
        }

        $model->delete($id);

        if ($this->isAjax()) {
            $this->json(['success' => true]);
        }

        $_SESSION['flash_success'] = 'پروژه حذف شد';
        $this->redirect(CURRENT_MODULE_URL . '?controller=project');
    }
}