<?php

namespace App\Software\Babok\Controllers;

use App\Software\Babok\Core\Controller;
use App\Software\Babok\Services\RecommendationService;
use App\Software\Babok\Models\Task;
use App\Software\Babok\Models\Project;
use App\Software\Babok\Models\Technique;
use App\Software\Babok\Models\KnowledgeArea;

/**
 * کنترلر پیشنهاد هوشمند تکنیک‌های BABOK
 * وابسته به: RecommendationService (مرحله ۸)
 */
class RecommendationController extends Controller
{
    private $recommendationService;
    private $taskModel;
    private $projectModel;
    private $techniqueModel;
    private $knowledgeAreaModel;

    public function __construct()
    {
        $this->recommendationService = new RecommendationService();
        $this->taskModel = new Task();
        $this->projectModel = new Project();
        $this->techniqueModel = new Technique();
        $this->knowledgeAreaModel = new KnowledgeArea();
    }

    /**
     * پیشنهاد تکنیک‌ها برای یک وظیفه در پروژه (AJAX)
     */
    public function forTask()
    {
        $taskId = $_GET['task_id'] ?? null;
        $projectId = $_GET['project_id'] ?? null;

        if (!$taskId) {
            return $this->json(['error' => 'شناسه وظیفه الزامی است.']);
        }

        $task = $this->taskModel->find($taskId);

        if (!$task) {
            return $this->json(['error' => 'وظیفه مورد نظر یافت نشد.']);
        }

        // دریافت تنظیمات زمینه‌ای از پروژه
        $context = [];
        if ($projectId) {
            $project = $this->projectModel->find($projectId);
            if ($project) {
                $context = [
                    'methodology' => $project['methodology'],
                    'phase' => $project['phase'],
                    'stakeholder_count' => $project['stakeholder_count']
                ];
            }
        }

        $techniques = $this->recommendationService->recommendForTask($taskId, $context);
        $allTechniques = $this->techniqueModel->getAll();

        return $this->json([
            'task' => $task,
            'context' => $context,
            'recommended' => $techniques,
            'all' => $allTechniques
        ]);
    }

    /**
     * پیشنهاد تکنیک‌ها برای کل پروژه (AJAX)
     */
    public function forProject($projectId)
    {
        $project = $this->projectModel->find($projectId);

        if (!$project) {
            return $this->json(['error' => 'پروژه مورد نظر یافت نشد.']);
        }

        $recommendations = $this->recommendationService->recommendForProject($projectId);

        return $this->json([
            'project' => $project,
            'recommendations' => $recommendations
        ]);
    }

    /**
     * صفحه پیشنهادات برای یک وظیفه (HTML)
     */
    public function showTaskRecommendations($taskId)
    {
        $task = $this->taskModel->find($taskId);

        if (!$task) {
            $this->flashError('وظیفه مورد نظر یافت نشد.');
            $this->redirect('tasks');
            return;
        }

        // دریافت حوزه دانشی
        $knowledgeArea = $this->knowledgeAreaModel->find($task['knowledge_area_id']);
        $task['knowledge_area_code'] = $knowledgeArea['code'] ?? '';

        // دریافت تکنیک‌های پیشنهادی با استفاده از سرویس
        $recommendedTechniques = $this->recommendationService->recommendForTask($taskId, [
            'methodology' => 'hybrid',
            'phase' => 'analysis'
        ]);

        // دریافت همه تکنیک‌ها
        $allTechniques = $this->techniqueModel->getAll();

        $this->view('recommendations/task', [
            'title' => 'پیشنهاد تکنیک برای: ' . $task['name'] . ' - BABOK Analyzer',
            'activePage' => 'tasks',
            'task' => $task,
            'recommendedTechniques' => $recommendedTechniques,
            'allTechniques' => $allTechniques,
            'taskId' => $taskId
        ]);
    }

    /**
     * پیشنهاد بر اساس تحلیل متن نیازمندی (AJAX)
     */
    public function analyzeText()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['error' => 'روش درخواست مجاز نیست.']);
        }

        $text = $_POST['requirement_text'] ?? '';

        if (empty($text)) {
            return $this->json(['error' => 'متن نیازمندی را وارد کنید.']);
        }

        $recommendations = $this->recommendationService->recommendForRequirements($text);

        return $this->json([
            'text' => $text,
            'recommendations' => $recommendations
        ]);
    }

    /**
     * صفحه تحلیل متن نیازمندی (HTML)
     */
    public function analyzer()
    {
        $this->view('requirement-analyzer/index', [
            'title' => 'تحلیل نیازمندی با هوش مصنوعی - BABOK Analyzer',
            'activePage' => 'requirement'
        ]);
    }
}