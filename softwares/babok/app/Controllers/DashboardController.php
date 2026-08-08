<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\KnowledgeArea;
use App\Models\Project;
use App\Models\Technique;
use App\Models\Task;
use App\Models\ProjectTask;

class DashboardController extends Controller
{
    public function index()
    {
        $knowledgeAreaModel = new KnowledgeArea();
        $projectModel = new Project();
        $techniqueModel = new Technique();
        $taskModel = new Task();
        $projectTaskModel = new ProjectTask();

        // =============================================
        // دریافت داده‌های حوزه‌های دانشی با تعداد وظایف
        // =============================================
        $knowledgeAreas = $knowledgeAreaModel->getAllWithCount();
        
        // اگر متد getAllWithCount وجود ندارد، از روش زیر استفاده کنید:
        // $knowledgeAreas = $knowledgeAreaModel->getAll();
        // foreach ($knowledgeAreas as &$area) {
        //     $tasks = $taskModel->getByKnowledgeArea($area['id']);
        //     $area['task_count'] = count($tasks);
        // }

        // =============================================
        // آمار کلی
        // =============================================
        $totalTasks = count($taskModel->getAll());
        $totalTechniques = count($techniqueModel->getAll());
        $projects = $projectModel->getAll();
        $totalProjects = count($projects);

        // =============================================
        // پروژه‌های فعال با پیشرفت
        // =============================================
        $activeProjects = [];
        foreach ($projects as $project) {
            if ($project['phase'] !== 'evaluation') {
                $progress = $projectModel->getProgress($project['id']);
                $project['progress'] = $progress['completion_percentage'] ?? 0;
                $project['total_tasks'] = $progress['total'] ?? 0;
                $project['completed_tasks'] = $progress['completed'] ?? 0;
                $activeProjects[] = $project;
            }
        }

        // =============================================
        // آخرین فعالیت‌ها
        // =============================================
        $recentActivities = [];
        if (!empty($projects)) {
            $latestProject = $projects[0];
            $recentActivities = $projectTaskModel->getRecentCompleted($latestProject['id'], 5);
        }

        // =============================================
        // توزیع تکنیک‌ها بر اساس دسته‌بندی
        // =============================================
        $techniques = $techniqueModel->getAll();
        $categoryStats = [];
        foreach ($techniques as $tech) {
            $cat = $tech['category'] ?? 'other';
            if (!isset($categoryStats[$cat])) {
                $categoryStats[$cat] = 0;
            }
            $categoryStats[$cat]++;
        }

        // =============================================
        // ارسال داده‌ها به ویو
        // =============================================
        $this->view('dashboard/index', [
            'knowledgeAreas' => $knowledgeAreas,        // ← این داده باید به ویو برود
            'totalTasks' => $totalTasks,
            'totalTechniques' => $totalTechniques,
            'totalProjects' => $totalProjects,
            'activeProjects' => $activeProjects,
            'activeProjectsCount' => count($activeProjects),
            'recentActivities' => $recentActivities,
            'categoryStats' => $categoryStats
        ]);
    }
}