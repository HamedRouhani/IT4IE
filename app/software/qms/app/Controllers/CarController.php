<?php

namespace App\Software\Qms\Controllers;

use App\Software\Qms\Core\Controller;

class CarController extends Controller
{
    /**
     * لیست تمام CARها
     */
    public function index()
    {
        $this->requireAuth();
        
        $cars = $this->db->query("
            SELECT cf.*, nc.nc_number, nc.title as nc_title, nc.severity as nc_severity,
                   u.name as created_by_name,
                   (SELECT COUNT(*) FROM {$this->prefix}car_actions WHERE car_form_id = cf.id) as actions_count,
                   (SELECT COUNT(*) FROM {$this->prefix}car_actions WHERE car_form_id = cf.id AND status != 'completed') as pending_actions
            FROM {$this->prefix}car_forms cf
            JOIN {$this->prefix}nonconformities nc ON cf.nc_id = nc.id
            LEFT JOIN users u ON cf.created_by = u.id
            ORDER BY cf.created_at DESC
        ")->fetchAll();

        $this->view('car/index', [
            'pageTitle' => 'فرم‌های CAR',
            'currentPage' => 'car',
            'cars' => $cars
        ]);
    }

    /**
     * مشاهده جزئیات CAR با اقدامات و تسک‌ها
     */
    public function show($id)
    {
        $this->requireAuth();
        
        // دریافت اطلاعات CAR
        $stmt = $this->db->prepare("
            SELECT cf.*, nc.nc_number, nc.title as nc_title, nc.description as nc_description,
                   nc.severity as nc_severity, nc.clause_id,
                   c.clause_number, c.title_fa as clause_title,
                   u.name as created_by_name,
                   (SELECT COUNT(*) FROM {$this->prefix}car_actions WHERE car_form_id = cf.id) as total_actions,
                   (SELECT COUNT(*) FROM {$this->prefix}car_actions WHERE car_form_id = cf.id AND status = 'completed') as completed_actions
            FROM {$this->prefix}car_forms cf
            JOIN {$this->prefix}nonconformities nc ON cf.nc_id = nc.id
            LEFT JOIN {$this->prefix}iso_clauses c ON nc.clause_id = c.id
            LEFT JOIN users u ON cf.created_by = u.id
            WHERE cf.id = ?
        ");
        $stmt->execute([$id]);
        $car = $stmt->fetch();

        if (!$car) {
            $this->flashError('فرم CAR یافت نشد.');
            $this->redirect('car');
            return;
        }

        // دریافت اقدامات CAR
        $actions = $this->db->prepare("
            SELECT ca.*, u.name as responsible_name,
                   (SELECT COUNT(*) FROM {$this->prefix}car_action_tasks WHERE action_id = ca.id) as total_tasks,
                   (SELECT COUNT(*) FROM {$this->prefix}car_action_tasks WHERE action_id = ca.id AND status = 'done') as completed_tasks,
                   (SELECT AVG(progress_percentage) FROM {$this->prefix}car_action_tasks WHERE action_id = ca.id) as avg_progress
            FROM {$this->prefix}car_actions ca
            LEFT JOIN users u ON ca.responsible_person_id = u.id
            WHERE ca.car_form_id = ?
            ORDER BY ca.action_number
        ");
        $actions->execute([$id]);
        $actions = $actions->fetchAll();

        // دریافت تمام کاربران برای انتخاب مسئول
        $users = $this->db->query("SELECT id, name FROM users WHERE is_active = 1 ORDER BY name")->fetchAll();

        // محاسبه درصد پیشرفت کلی CAR
        $totalTasks = 0;
        $completedTasks = 0;
        foreach ($actions as $action) {
            $totalTasks += $action['total_tasks'];
            $completedTasks += $action['completed_tasks'];
        }
        $overallProgress = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100) : 0;

        $this->view('car/show', [
            'pageTitle' => 'CAR: ' . $car['car_number'],
            'currentPage' => 'car',
            'car' => $car,
            'actions' => $actions,
            'users' => $users,
            'overallProgress' => $overallProgress
        ]);
    }

    /**
     * افزودن اقدام جدید به CAR
     */
    public function addAction()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('car');
            return;
        }

        $carId = $_POST['car_form_id'] ?? null;
        $actionTitle = trim($_POST['action_title'] ?? '');
        $actionDescription = trim($_POST['action_description'] ?? '');

        if (!$carId || empty($actionTitle) || empty($actionDescription)) {
            $this->flashError('عنوان و توضیحات اقدام الزامی است.');
            $this->redirect('car&action=show&id=' . $carId);
            return;
        }

        // تعیین شماره اقدام
        $stmt = $this->db->prepare("
            SELECT MAX(action_number) as max_num FROM {$this->prefix}car_actions WHERE car_form_id = ?
        ");
        $stmt->execute([$carId]);
        $maxNum = $stmt->fetch()['max_num'] ?? 0;
        $newActionNumber = $maxNum + 1;

        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}car_actions 
            (car_form_id, action_number, action_title, action_description, action_type, 
             priority, responsible_person_id, due_date, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        
        $result = $stmt->execute([
            $carId,
            $newActionNumber,
            $actionTitle,
            $actionDescription,
            $_POST['action_type'] ?? 'corrective',
            $_POST['priority'] ?? 'medium',
            $_POST['responsible_person_id'] ?? $this->currentUserId,
            $_POST['due_date'] ?? date('Y-m-d', strtotime('+14 days'))
        ]);

        if ($result) {
            $this->logActivity('add_car_action', 'car_action', $this->db->lastInsertId());
            $this->flashSuccess('اقدام با موفقیت اضافه شد.');
        } else {
            $this->flashError('خطا در افزودن اقدام.');
        }

        $this->redirect('car&action=show&id=' . $carId);
    }

    /**
     * افزودن تسک به یک اقدام
     */
    public function addTask()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('car');
            return;
        }

        $actionId = $_POST['action_id'] ?? null;
        $taskTitle = trim($_POST['task_title'] ?? '');

        if (!$actionId || empty($taskTitle)) {
            $this->flashError('عنوان تسک الزامی است.');
            $this->redirect('car&action=show&id=' . ($_POST['car_id'] ?? 0));
            return;
        }

        $stmt = $this->db->prepare("
            INSERT INTO {$this->prefix}car_action_tasks 
            (action_id, task_title, task_description, assigned_to, due_date, status, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, 'todo', ?, NOW())
        ");
        
        $result = $stmt->execute([
            $actionId,
            $taskTitle,
            $_POST['task_description'] ?? '',
            $_POST['assigned_to'] ?? $this->currentUserId,
            $_POST['due_date'] ?? date('Y-m-d', strtotime('+7 days')),
            $this->currentUserId
        ]);

        if ($result) {
            $this->logActivity('add_car_task', 'car_task', $this->db->lastInsertId());
            $this->flashSuccess('تسک با موفقیت اضافه شد.');
        } else {
            $this->flashError('خطا در افزودن تسک.');
        }

        $this->redirect('car&action=show&id=' . ($_POST['car_id'] ?? 0));
    }

    /**
     * به‌روزرسانی وضعیت تسک
     */
    public function updateTaskStatus()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('car');
            return;
        }

        $taskId = $_POST['task_id'] ?? null;
        $newStatus = $_POST['status'] ?? null;
        $progress = intval($_POST['progress'] ?? 0);

        if (!$taskId || !$newStatus) {
            $this->flashError('اطلاعات نامعتبر است.');
            $this->redirect('car');
            return;
        }

        $validStatuses = ['todo', 'in_progress', 'done', 'blocked', 'cancelled'];
        if (!in_array($newStatus, $validStatuses)) {
            $this->flashError('وضعیت نامعتبر است.');
            $this->redirect('car');
            return;
        }

        $stmt = $this->db->prepare("
            UPDATE {$this->prefix}car_action_tasks 
            SET status = ?, progress_percentage = ?, 
                completion_date = CASE WHEN ? = 'done' THEN NOW() ELSE NULL END,
                updated_at = NOW()
            WHERE id = ?
        ");
        $result = $stmt->execute([$newStatus, $progress, $newStatus, $taskId]);

        if ($result) {
            $this->logActivity('update_car_task_status', 'car_task', $taskId);
            $this->flashSuccess('وضعیت تسک به‌روزرسانی شد.');
        } else {
            $this->flashError('خطا در به‌روزرسانی وضعیت.');
        }

        $this->redirect('car&action=show&id=' . ($_POST['car_id'] ?? 0));
    }

    /**
     * تأیید اثربخشی CAR (توسط ممیز)
     */
    public function verifyEffectiveness()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('car');
            return;
        }

        $carId = $_POST['car_id'] ?? null;
        $isEffective = isset($_POST['is_effective']) ? 1 : 0;
        $effectivenessCheck = trim($_POST['effectiveness_check'] ?? '');

        if (!$carId || empty($effectivenessCheck)) {
            $this->flashError('توضیحات اثربخشی الزامی است.');
            $this->redirect('car&action=show&id=' . $carId);
            return;
        }

        $stmt = $this->db->prepare("
            UPDATE {$this->prefix}car_forms 
            SET effectiveness_check = ?, 
                effectiveness_verified_by = ?,
                effectiveness_verified_at = NOW(),
                is_effective = ?,
                status = CASE WHEN ? = 1 THEN 'verified' ELSE 'rejected' END,
                updated_at = NOW()
            WHERE id = ?
        ");
        $result = $stmt->execute([
            $effectivenessCheck,
            $this->currentUserId,
            $isEffective,
            $isEffective,
            $carId
        ]);

        if ($result) {
            $this->logActivity('verify_car_effectiveness', 'car_form', $carId);
            $this->flashSuccess($isEffective ? 'CAR تأیید و بسته شد.' : 'CAR رد شد و نیاز به اقدام مجدد دارد.');
            
            // اگر CAR رد شد، NC را به وضعیت open برگردان
            if (!$isEffective) {
                $stmt = $this->db->prepare("
                    UPDATE {$this->prefix}nonconformities 
                    SET status = 'open', updated_at = NOW()
                    WHERE car_form_id = ?
                ");
                $stmt->execute([$carId]);
            }
        } else {
            $this->flashError('خطا در تأیید اثربخشی.');
        }

        $this->redirect('car&action=show&id=' . $carId);
    }

    /**
     * حذف اقدام
     */
    public function deleteAction()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('car');
            return;
        }

        $actionId = $_POST['action_id'] ?? null;
        $carId = $_POST['car_id'] ?? null;

        if (!$actionId || !$carId) {
            $this->flashError('اطلاعات نامعتبر است.');
            $this->redirect('car');
            return;
        }

        // فقط مدیر یا creator می‌تواند حذف کند
        $stmt = $this->db->prepare("SELECT car_form_id FROM {$this->prefix}car_actions WHERE id = ?");
        $stmt->execute([$actionId]);
        $action = $stmt->fetch();

        if (!$action || $action['car_form_id'] != $carId) {
            $this->flashError('اقدام یافت نشد.');
            $this->redirect('car&action=show&id=' . $carId);
            return;
        }

        $stmt = $this->db->prepare("DELETE FROM {$this->prefix}car_actions WHERE id = ?");
        $result = $stmt->execute([$actionId]);

        if ($result) {
            $this->logActivity('delete_car_action', 'car_action', $actionId);
            $this->flashSuccess('اقدام حذف شد.');
        } else {
            $this->flashError('خطا در حذف اقدام.');
        }

        $this->redirect('car&action=show&id=' . $carId);
    }
}