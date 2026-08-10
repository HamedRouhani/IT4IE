<?php

namespace App\Software\Babok\Controllers;

use App\Software\Babok\Core\Controller;
use App\Software\Babok\Models\Task;
use App\Software\Babok\Models\KnowledgeArea;
use App\Software\Babok\Models\Technique;
use App\Software\Babok\Models\TaskTechnique;

/**
 * کنترلر مدیریت وظایف BABOK
 */
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

    /**
     * لیست همه وظایف
     */
    public function index()
    {
        $tasks = $this->taskModel->getAllWithKnowledgeArea();
        $knowledgeAreas = $this->knowledgeAreaModel->getAll();

        $this->view('tasks/index', [
            'title' => 'وظایف BABOK - BABOK Analyzer',
            'activePage' => 'tasks',
            'tasks' => $tasks,
            'knowledgeAreas' => $knowledgeAreas
        ]);
    }

    /**
     * مشاهده جزئیات یک وظیفه
     */
    public function show($id)
    {
        $task = $this->taskModel->find($id);

        if (!$task) {
            $this->flashError('وظیفه مورد نظر یافت نشد.');
            $this->redirect('tasks');
            return;
        }

        $knowledgeArea = $this->knowledgeAreaModel->find($task['knowledge_area_id']);
        $techniques = $this->taskTechniqueModel->getTechniquesByTask($id);
        $allTechniques = $this->techniqueModel->getAll();

        $this->view('tasks/view', [
            'title' => $task['name'] . ' - BABOK Analyzer',
            'activePage' => 'tasks',
            'task' => $task,
            'knowledgeArea' => $knowledgeArea,
            'techniques' => $techniques,
            'allTechniques' => $allTechniques,
            'taskId' => $id
        ]);
    }

    /**
     * دریافت تکنیک‌های یک وظیفه (AJAX)
     */
    public function techniques($id)
    {
        $techniques = $this->taskTechniqueModel->getTechniquesByTask($id);
        return $this->json(['techniques' => $techniques]);
    }

    /**
     * افزودن تکنیک به وظیفه (AJAX)
     * ⚠️ نیاز به احراز هویت دارد
     */
    public function addTechnique()
    {
        // بررسی احراز هویت
        if (!isset($_SESSION['user_id'])) {
            return $this->json([
                'error' => 'برای انجام این عملیات لطفاً وارد شوید.',
                'requires_auth' => true,
                'redirect' => '/login'
            ]);
        }
        
        // 🆕 بررسی دسترسی ادمین
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            return $this->json([
                'error' => 'شما مجوز افزودن تکنیک را ندارید. فقط مدیران می‌توانند تکنیک‌ها را ویرایش کنند.',
                'permission_denied' => true
            ]);
        }
        
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

            // ثبت لاگ فعالیت
            $this->logActivity('add_technique_to_task', 'task_technique', $taskId, null, [
                'task_id' => $taskId,
                'technique_id' => $techniqueId,
                'technique_name' => $technique['name'] ?? ''
            ]);

            return $this->json([
                'success' => true,
                'message' => 'تکنیک با موفقیت اضافه شد.',
                'technique' => $technique
            ]);
        } else {
            return $this->json(['error' => 'خطا در افزودن تکنیک. ممکن است قبلاً اضافه شده باشد.']);
        }
    }

    /**
     * حذف تکنیک از وظیفه (AJAX)
     * ⚠️ نیاز به احراز هویت دارد
     */
    public function removeTechnique()
    {
        // بررسی احراز هویت
        if (!isset($_SESSION['user_id'])) {
            return $this->json([
                'error' => 'برای انجام این عملیات لطفاً وارد شوید.',
                'requires_auth' => true,
                'redirect' => '/login'
            ]);
        }
        
        // 🆕 بررسی دسترسی ادمین
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            return $this->json([
                'error' => 'شما مجوز حذف تکنیک را ندارید. فقط مدیران می‌توانند تکنیک‌ها را ویرایش کنند.',
                'permission_denied' => true
            ]);
        }
        
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
            // ثبت لاگ فعالیت
            $this->logActivity('remove_technique_from_task', 'task_technique', $taskId, [
                'task_id' => $taskId,
                'technique_id' => $techniqueId
            ], null);

            return $this->json([
                'success' => true,
                'message' => 'تکنیک با موفقیت حذف شد.'
            ]);
        } else {
            return $this->json(['error' => 'خطا در حذف تکنیک.']);
        }
    }
}