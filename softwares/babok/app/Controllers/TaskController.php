<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Task;
use App\Models\KnowledgeArea;
use App\Models\Technique;
use App\Models\TaskTechnique;

class TaskController extends Controller
{
    private $taskModel;
    private $knowledgeAreaModel;
    private $techniqueModel;
    private $taskTechniqueModel;

    public function __construct()
    {
        $this->taskModel = new Task();
        $this->knowledgeAreaModel = new KnowledgeArea();
        $this->techniqueModel = new Technique();
        $this->taskTechniqueModel = new TaskTechnique();
    }

    // لیست همه وظایف
    public function index()
    {
        $tasks = $this->taskModel->getAllWithKnowledgeArea();
        $knowledgeAreas = $this->knowledgeAreaModel->getAll();
        
        $this->view('tasks/index', [
            'tasks' => $tasks,
            'knowledgeAreas' => $knowledgeAreas
        ]);
    }

    // مشاهده جزئیات یک وظیفه
    public function show($id)
    {
        $task = $this->taskModel->find($id);
        if (!$task) {
            $_SESSION['flash_error'] = 'وظیفه مورد نظر یافت نشد.';
            $this->redirect('/tasks');
        }

        $knowledgeArea = $this->knowledgeAreaModel->find($task['knowledge_area_id']);
        $techniques = $this->taskTechniqueModel->getTechniquesByTask($id);
        $allTechniques = $this->techniqueModel->getAll();

        $this->view('tasks/view', [
            'task' => $task,
            'knowledgeArea' => $knowledgeArea,
            'techniques' => $techniques,
            'allTechniques' => $allTechniques,
            'taskId' => $id
        ]);
    }

    // دریافت تکنیک‌های یک وظیفه (AJAX)
    public function techniques($id)
    {
        $techniques = $this->taskTechniqueModel->getTechniquesByTask($id);
        return $this->json(['techniques' => $techniques]);
    }

    // افزودن تکنیک به وظیفه (AJAX)
    public function addTechnique()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['error' => 'روش درخواست مجاز نیست.']);
        }

        $taskId = (int) ($_POST['task_id'] ?? 0);
        $techniqueId = (int) ($_POST['technique_id'] ?? 0);

        if (!$taskId || !$techniqueId) {
            return $this->json(['error' => 'اطلاعات ناقص است.']);
        }

        $result = $this->taskTechniqueModel->addRelation($taskId, $techniqueId);
        
        if ($result) {
            $technique = $this->techniqueModel->find($techniqueId);
            return $this->json([
                'success' => true,
                'message' => 'تکنیک با موفقیت اضافه شد.',
                'technique' => $technique
            ]);
        } else {
            return $this->json(['error' => 'خطا در افزودن تکنیک. ممکن است قبلاً اضافه شده باشد.']);
        }
    }

    // حذف تکنیک از وظیفه (AJAX)
    public function removeTechnique()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['error' => 'روش درخواست مجاز نیست.']);
        }

        $taskId = (int) ($_POST['task_id'] ?? 0);
        $techniqueId = (int) ($_POST['technique_id'] ?? 0);

        if (!$taskId || !$techniqueId) {
            return $this->json(['error' => 'اطلاعات ناقص است.']);
        }

        $result = $this->taskTechniqueModel->removeRelation($taskId, $techniqueId);
        
        if ($result) {
            return $this->json([
                'success' => true,
                'message' => 'تکنیک با موفقیت حذف شد.'
            ]);
        } else {
            return $this->json(['error' => 'خطا در حذف تکنیک.']);
        }
    }
}