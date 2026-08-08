<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Technique;
use App\Models\TaskTechnique;

class TechniqueController extends Controller
{
    private $techniqueModel;
    private $taskTechniqueModel;

    public function __construct()
    {
        $this->techniqueModel = new Technique();
        $this->taskTechniqueModel = new TaskTechnique();
    }

    // لیست همه تکنیک‌ها
    public function index()
    {
        $techniques = $this->techniqueModel->getAll();
        $categories = $this->techniqueModel->getCategories();
        
        $this->view('techniques/index', [
            'techniques' => $techniques,
            'categories' => $categories
        ]);
    }

    // مشاهده جزئیات یک تکنیک
    public function show($id)
    {
        $technique = $this->techniqueModel->find($id);
        if (!$technique) {
            $_SESSION['flash_error'] = 'تکنیک مورد نظر یافت نشد.';
            $this->redirect('/techniques');
        }

        $tasks = $this->taskTechniqueModel->getTasksByTechnique($id);

        $this->view('techniques/view', [
            'technique' => $technique,
            'tasks' => $tasks
        ]);
    }

    // دریافت تکنیک‌های یک دسته‌بندی
    public function byCategory($category)
    {
        $techniques = $this->techniqueModel->getByCategory($category);
        return $this->json(['techniques' => $techniques]);
    }

    // جستجوی تکنیک‌ها (AJAX)
    public function search()
    {
        $keyword = $_GET['keyword'] ?? '';
        if (empty($keyword)) {
            return $this->json(['error' => 'کلمه جستجو را وارد کنید.']);
        }

        $sql = "SELECT * FROM techniques 
                WHERE name LIKE ? 
                OR description LIKE ? 
                OR purpose LIKE ?
                ORDER BY name";
        $search = "%{$keyword}%";
        $results = $this->techniqueModel->query($sql, [$search, $search, $search]);
        
        return $this->json(['results' => $results]);
    }
}