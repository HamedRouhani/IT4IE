<?php

namespace App\Software\Babok\Controllers;

use App\Software\Babok\Core\Controller;
use App\Software\Babok\Services\NotificationService;

/**
 * کنترلر مدیریت اعلان‌ها و یادآوری‌ها
 */
class NotificationController extends Controller
{
    private $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }

    /**
     * صفحه اصلی اعلان‌ها
     */
    public function index()
    {
        $this->requireAuth();

        // تولید اعلان‌های هوشمند قبل از نمایش
        $this->notificationService->generateSmartNotifications();

        $filter = $_GET['filter'] ?? 'all';
        $notifications = $this->notificationService->getAllForUser($filter, 50);
        $unreadCount = $this->notificationService->getUnreadCount();

        $this->view('notifications/index', [
            'title' => 'اعلان‌ها و یادآوری‌ها - BABOK Analyzer',
            'activePage' => 'notifications',
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'currentFilter' => $filter
        ]);
    }

    /**
     * دریافت اعلان‌ها به صورت AJAX (برای bell icon)
     */
    public function ajax()
    {
        $this->requireAuth();

        // تولید اعلان‌های هوشمند
        $this->notificationService->generateSmartNotifications();

        $notifications = $this->notificationService->getRecentNotifications(5);
        $unreadCount = $this->notificationService->getUnreadCount();

        return $this->json([
            'success' => true,
            'unread_count' => $unreadCount,
            'notifications' => $notifications
        ]);
    }

    /**
     * علامت‌گذاری یک اعلان به عنوان خوانده‌شده (AJAX)
     */
    public function markAsRead()
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->json(['error' => 'روش درخواست مجاز نیست.'], 405);
        }

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            return $this->json(['error' => 'شناسه نامعتبر است.']);
        }

        $result = $this->notificationService->markAsRead($id);
        
        return $this->json([
            'success' => $result,
            'unread_count' => $this->notificationService->getUnreadCount()
        ]);
    }

    /**
     * علامت‌گذاری همه به عنوان خوانده‌شده
     */
    public function markAllAsRead()
    {
        $this->requireAuth();

        $result = $this->notificationService->markAllAsRead();
        
        if ($result) {
            $this->flashSuccess('تمام اعلان‌ها به عنوان خوانده‌شده علامت‌گذاری شدند.');
        } else {
            $this->flashError('خطا در به‌روزرسانی اعلان‌ها.');
        }

        $this->redirect('notifications');
    }

    /**
     * حذف یک اعلان
     */
    public function delete()
    {
        $this->requireAuth();

        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$id) {
            $this->flashError('شناسه نامعتبر است.');
            $this->redirect('notifications');
            return;
        }

        $result = $this->notificationService->delete($id);
        
        if ($result) {
            $this->flashSuccess('اعلان با موفقیت حذف شد.');
        } else {
            $this->flashError('خطا در حذف اعلان.');
        }

        $this->redirect('notifications');
    }
}