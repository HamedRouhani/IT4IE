<?php

namespace App\Software\Mcdm\Controllers;

use App\Software\Mcdm\Core\Controller;
use App\Software\Mcdm\Models\Project;
use App\Software\Mcdm\Models\Method;
use App\Software\Mcdm\Models\Industry;

class ProjectController extends Controller
{
    private $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Project();
    }

    public function index()
    {
        $this->requireAuth();
        $projects = $this->model->getWithMethod($this->currentUserId);

        $this->view('project/index', [
            'pageTitle'   => 'پروژه‌های تصمیم‌گیری',
            'currentPage' => 'project',
            'projects'    => $projects,
        ]);
    }

    public function create()
    {
        $this->requireAuth();

        $methodModel = new Method();
        $industryModel = new Industry();

        $this->view('project/create', [
            'pageTitle'   => 'ایجاد پروژه جدید',
            'currentPage' => 'project',
            'methods'     => $methodModel->getWithKnowledgeArea(),
            'industries'  => $industryModel->findAll([], 'name_fa ASC'),
        ]);
    }

    public function store()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('controller=project&action=create');
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->flashError('نام پروژه الزامی است.');
            $this->redirect('controller=project&action=create');
        }

        $methodId = (int)($_POST['method_id'] ?? 0);

        $projectId = $this->model->create([
            'user_id'     => $this->currentUserId,
            'name'        => $name,
            'description' => trim($_POST['description'] ?? ''),
            'method_id'   => $methodId > 0 ? $methodId : null,
            'industry'    => trim($_POST['industry'] ?? 'general'),
            'phase'       => 'definition',
        ]);

        $this->logActivity('create', 'project', $projectId);
        $this->flashSuccess('پروژه با موفقیت ایجاد شد.');
        $this->redirect('controller=project&action=show&id=' . (int)$projectId);
    }

    public function show($id)
    {
        $this->requireAuth();

        $project = $this->model->getWithDetails((int)$id);
        if (!$project) {
            $this->flashError('پروژه یافت نشد.');
            $this->redirect('controller=project');
        }

        if (!$this->authorizeOwnership($project['user_id'])) {
            return;
        }

        $tab = $_GET['tab'] ?? 'info';
        $allowedTabs = ['info', 'criteria', 'alternatives', 'matrix', 'ahp', 'results'];
        if (!in_array($tab, $allowedTabs, true)) $tab = 'info';

        $this->view('project/view', [
            'pageTitle'    => $project['name'],
            'currentPage'  => 'project',
            'project'      => $project,
            'tab'          => $tab,
            'criteria'     => $this->model->getCriteria((int)$id),
            'alternatives' => $this->model->getAlternatives((int)$id),
            'results'      => $this->model->getResults((int)$id),
        ]);
    }

    public function addCriterion($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('controller=project&action=show&id=' . (int)$id);
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->flashError('نام معیار الزامی است.');
            $this->redirect('controller=project&action=show&id=' . (int)$id . '&tab=criteria');
        }

        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO mcdm_project_criteria (project_id, name, type, sort_order) VALUES (?, ?, ?, 0)"
        );
        $stmt->execute([(int)$id, $name, $_POST['type'] ?? 'benefit']);

        $this->logActivity('add_criterion', 'project', (int)$id);
        $this->flashSuccess('معیار اضافه شد.');
        $this->redirect('controller=project&action=show&id=' . (int)$id . '&tab=criteria');
    }

    public function addAlternative($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('controller=project&action=show&id=' . (int)$id);
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->flashError('نام گزینه الزامی است.');
            $this->redirect('controller=project&action=show&id=' . (int)$id . '&tab=alternatives');
        }

        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO mcdm_project_alternatives (project_id, name, description, sort_order) VALUES (?, ?, ?, 0)"
        );
        $stmt->execute([(int)$id, $name, trim($_POST['description'] ?? '')]);

        $this->logActivity('add_alternative', 'project', (int)$id);
        $this->flashSuccess('گزینه اضافه شد.');
        $this->redirect('controller=project&action=show&id=' . (int)$id . '&tab=alternatives');
    }

    public function setEvaluation($id)
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Method not allowed'], 405);
        }

        $criterionId = (int)($_POST['criterion_id'] ?? 0);
        $alternativeId = (int)($_POST['alternative_id'] ?? 0);
        $value = (float)($_POST['value'] ?? 0);

        if ($criterionId && $alternativeId) {
            $this->model->setEvaluation((int)$id, $criterionId, $alternativeId, $value);
        }

        $this->json(['success' => true]);
    }

    public function delete($id)
    {
        $this->requireAuth();

        $project = $this->model->find((int)$id);
        if ($project && $this->authorizeOwnership($project['user_id'])) {
            $this->model->delete((int)$id);
            $this->logActivity('delete', 'project', (int)$id);
            $this->flashSuccess('پروژه حذف شد.');
        }

        $this->redirect('controller=project');
    }
}