<?php
namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    // =============================================
    // متدهای جستجو
    // =============================================
    
    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
    
    /**
     * Find user by ID
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
    
    /**
     * Find user by verification token
     */
    public function findByVerificationToken($token)
    {
        $sql = "SELECT * FROM {$this->table} WHERE verification_token = :token LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token' => $token]);
        return $stmt->fetch();
    }
    
    /**
     * Find user by reset token
     */
    public function findByResetToken($token)
    {
        $sql = "SELECT * FROM {$this->table} WHERE reset_token = :token LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token' => $token]);
        return $stmt->fetch();
    }
    
    /**
     * Find user by remember token
     */
    public function findByRememberToken($token)
    {
        $sql = "SELECT * FROM {$this->table} WHERE remember_token = :token AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['token' => $token]);
        return $stmt->fetch();
    }
    
    // =============================================
    // متدهای احراز هویت و ثبت‌نام
    // =============================================
    
    /**
     * Create new user
     */
    public function createUser($data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['verification_token'] = bin2hex(random_bytes(32));
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->create($data);
    }
    
    /**
     * Verify user email
     */
    public function verifyUser($userId)
    {
        return $this->update($userId, [
            'email_verified' => 1,
            'verification_token' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Update last login
     */
    public function updateLastLogin($userId)
    {
        return $this->update($userId, [
            'last_login' => date('Y-m-d H:i:s')
        ]);
    }
    
    // =============================================
    // متدهای بازیابی رمز عبور (منطبق با دیتابیس جدید)
    // =============================================
    
    /**
     * Generate and store reset token for user
     * مطابق با ساختار دیتابیس جدید با فیلدهای reset_token و reset_token_expires
     */
    public function generateResetToken($email)
    {
        $user = $this->findByEmail($email);
        if (!$user) {
            return false;
        }
        
        // Generate a secure random token
        $token = bin2hex(random_bytes(32));
        
        // Set expiration time (1 hour from now)
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Update user with reset token and expiration
        $sql = "UPDATE {$this->table} 
                SET reset_token = :token, 
                    reset_token_expires = :expires,
                    updated_at = NOW()
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'token' => $token,
            'expires' => $expires,
            'id' => $user['id']
        ]);
        
        return $result ? $token : false;
    }
    
    /**
     * Reset password using token
     * مطابق با ساختار دیتابیس جدید
     */
    public function resetPassword($token, $newPassword)
    {
        // Find user by reset token
        $user = $this->findByResetToken($token);
        if (!$user) {
            return [
                'success' => false,
                'message' => 'لینک بازیابی نامعتبر است.'
            ];
        }
        
        // Check if token is expired
        if (strtotime($user['reset_token_expires']) < time()) {
            return [
                'success' => false,
                'message' => 'لینک بازیابی منقضی شده است. لطفاً مجدداً درخواست دهید.'
            ];
        }
        
        // Hash the new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update user password and clear reset token
        $sql = "UPDATE {$this->table} 
                SET password = :password, 
                    reset_token = NULL, 
                    reset_token_expires = NULL,
                    updated_at = NOW()
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'password' => $hashedPassword,
            'id' => $user['id']
        ]);
        
        if ($result) {
            return [
                'success' => true,
                'message' => 'رمز عبور با موفقیت تغییر کرد.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'خطا در تغییر رمز عبور. لطفاً مجدداً تلاش کنید.'
            ];
        }
    }
    
    /**
     * Check if reset token is valid
     */
    public function isValidResetToken($token)
    {
        $user = $this->findByResetToken($token);
        if (!$user) {
            return false;
        }
        
        // Check if token is expired
        if (strtotime($user['reset_token_expires']) < time()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Clear reset token (for security after failed attempts)
     */
    public function clearResetToken($token)
    {
        $sql = "UPDATE {$this->table} 
                SET reset_token = NULL, 
                    reset_token_expires = NULL,
                    updated_at = NOW()
                WHERE reset_token = :token";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute(['token' => $token]);
    }
    
    // =============================================
    // متدهای Remember Me
    // =============================================
    
    /**
     * Store remember token for user
     */
    public function storeRememberToken($userId, $token, $expires)
    {
        return $this->update($userId, [
            'remember_token' => $token,
            'remember_expires' => date('Y-m-d H:i:s', $expires),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Clear remember token
     */
    public function clearRememberToken($userId)
    {
        return $this->update($userId, [
            'remember_token' => null,
            'remember_expires' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}