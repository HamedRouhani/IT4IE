<?php

namespace App\Software\Babok\Controllers;

use App\Software\Babok\Core\Controller;
use App\Software\Babok\Models\Technique;
use App\Software\Babok\Models\TaskTechnique;

/**
 * کنترلر مدیریت تکنیک‌های BABOK
 * جدول: babok_techniques (۵۰ تکنیک در ۶ دسته)
 */
class TechniqueController extends Controller
{
    private $techniqueModel;
    private $taskTechniqueModel;

    public function __construct()
    {
        $this->techniqueModel = new Technique();
        $this->taskTechniqueModel = new TaskTechnique();
    }

    /**
     * لیست همه تکنیک‌ها
     */
    public function index()
    {
        $techniques = $this->techniqueModel->getAll();
        $categories = $this->techniqueModel->getCategories();

        $this->view('techniques/index', [
            'title' => 'تکنیک‌های BABOK - BABOK Analyzer',
            'activePage' => 'techniques',
            'techniques' => $techniques,
            'categories' => $categories
        ]);
    }

    /**
     * مشاهده جزئیات یک تکنیک
     */
    public function show($id)
    {
        $technique = $this->techniqueModel->find($id);

        if (!$technique) {
            $this->flashError('تکنیک مورد نظر یافت نشد.');
            $this->redirect('techniques');
            return;
        }

        $tasks = $this->taskTechniqueModel->getTasksByTechnique($id);

        $this->view('techniques/view', [
            'title' => $technique['name'] . ' - BABOK Analyzer',
            'activePage' => 'techniques',
            'technique' => $technique,
            'tasks' => $tasks
        ]);
    }

    /**
     * دریافت تکنیک‌های یک دسته‌بندی (AJAX)
     */
    public function byCategory($category)
    {
        $techniques = $this->techniqueModel->getByCategory($category);
        return $this->json(['techniques' => $techniques]);
    }

    /**
     * جستجوی تکنیک‌ها (AJAX)
     */
    public function search()
    {
        $keyword = $_GET['keyword'] ?? '';

        if (empty($keyword)) {
            return $this->json(['error' => 'کلمه جستجو را وارد کنید.']);
        }

        $results = $this->techniqueModel->search($keyword);
        return $this->json(['results' => $results]);
    }
}