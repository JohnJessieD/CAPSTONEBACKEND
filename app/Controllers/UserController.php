<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserController extends ResourceController
{
    use ResponseTrait;

    public function register()
    {
        $user = new UserModel();
        $token = $this->generateToken(50);
        $verificationToken = $this->generateToken(32);
        $userRole = $this->request->getVar('role');
        $category = $this->request->getVar('category');
        $email = $this->request->getVar('email');
        
        $currentDateTime = date('Y-m-d H:i:s');
        
        $data = [
            'username' => $this->request->getVar('username'),
            'email' => $email,
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'token' => $token,
            'status' => 'inactive',
            'role' => $userRole,
            'category' => $category,
            'registration_date' => $currentDateTime,
            'verification_token' => $verificationToken
        ];
        
        $u = $user->save($data);
        if ($u) {
            $this->sendVerificationEmail($email, $verificationToken);
            return $this->respond(['msg' => 'Registration successful. Please check your email to verify your account.', 'token' => $token]);
        } else {
            return $this->respond(['msg' => 'Registration failed'], 500);
        }
    }
private function sendVerificationEmail($email, $verificationToken)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'stmswdapp@gmail.com';
        $mail->Password   = 'kamp eoxb tobq rplv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        // Sender details
        $mail->setFrom('stmswdapp@gmail.com', 'San Teodoro MSWD');
        $mail->addAddress($email);

        // Verification link and subject
        $verificationLink = site_url("verify-email/$verificationToken");
        $mail->Subject = 'Verify Your STMSWD Application';

        // HTML email body
        $mail->isHTML(true);
        $mail->Body = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                <div style='max-width: 600px; margin: auto; background-color: #ffffff; padding: 20px; border-radius: 8px;'>
                    <h2 style='color: #4CAF50; text-align: center;'>Welcome to San Teodoro MSWD</h2>
                    <p style='color: #333333; text-align: center; font-size: 16px;'>
                        Please verify your email to complete your application.
                    </p>
                    <div style='text-align: center; margin-top: 20px;'>
                        <a href='$verificationLink' style='padding: 10px 20px; background-color: #4CAF50; color: #ffffff;
                        text-decoration: none; border-radius: 5px; font-size: 16px;'>Verify Email</a>
                    </div>
                    <p style='color: #555555; text-align: center; font-size: 14px; margin-top: 20px;'>
                        If you did not create an account, please ignore this email.
                    </p>
                </div>
            </div>
        ";

        $mail->send();
    } catch (Exception $e) {
        log_message('error', "Verification email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }

    }

    public function verifyEmail($token)
    {
        $user = new UserModel();
        $userData = $user->where('verification_token', $token)->first();

        if ($userData) {
            $user->update($userData['id'], ['status' => 'active', 'verification_token' => null]);
            return $this->respond(['msg' => 'Email verified successfully. You can now log in.']);
        } else {
            return $this->respond(['msg' => 'Invalid verification token'], 400);
        }
    }

    private function generateToken($length)
    {
        return bin2hex(random_bytes($length / 2));
    }

 
    public function login()
    {
        $user = new UserModel();
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');
        $data = $user->where('username', $username)->first();
    
        if ($data) {
            $pass = $data['password'];
            $authenticatePassword = password_verify($password, $pass);
            if ($authenticatePassword) {
                // Fetch the category information from the database
                $category = $data['category']; // Assuming category is a column in your database table
    
                // Return the response with role and category
                return $this->respond(['msg' => 'okay', 'token' => $data['token'], 'role' => $data['role'], 'category' => $category]);
            } else {
                return $this->respond(['msg' => 'error'], 500);
            }
        }
        return $this->respond(['msg' => 'userNotFound'], 404);
    }
    


    public function logintESTING()
    {
        $user = new UserModel();
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');
        $data = $user->where('username', $username)->first();

        if ($data) {
            $pass = $data['password'];
            $authenticatePassword = password_verify($password, $pass);
            if ($authenticatePassword) {
                $sessionId = $this->generateSessionId();
                $this->storeSession($data['id'], $sessionId);

                $category = $data['category'];

                return $this->respond([
                    'status' => 'success',
                    'message' => 'Login successful',
                    'sessionId' => $sessionId,
                    'token' => $data['token'],
                    'role' => $data['role'],
                    'category' => $category
                ]);
            } else {
                return $this->respond(['status' => 'error', 'message' => 'Invalid password'], 401);
            }
        }
        return $this->respond(['status' => 'error', 'message' => 'User not found'], 404);
    }
  // Session verification method
  public function verifySession()
  {
      $sessionId = $this->request->getHeaderLine('X-Session-ID');
      
      if ($this->isValidSession($sessionId)) {
          return $this->respond(['status' => 'success', 'message' => 'Session is valid']);
      }
      
      return $this->failUnauthorized('Invalid or expired session');
  }

    private function generateSessionId()
    {
        return bin2hex(random_bytes(32));
    }

    private function storeSession($userId, $sessionId)
    {
        $session = \Config\Services::session();
        $session->set($sessionId, [
            'userId' => $userId,
            'createdAt' => time(),
            'expiresAt' => time() + (60 * 60 * 24) // 24 hours from now
        ]);
    }

    private function isValidSession($sessionId)
    {
        $session = \Config\Services::session();
        $sessionData = $session->get($sessionId);

        if ($sessionData && $sessionData['expiresAt'] > time()) {
            return true;
        }

        return false;
    }


    public function registerAdmin()
    {
        $user = new UserModel();
        $token = $this->generateToken(50);
        $data = [
            'username' => $this->request->getVar('username'),
            'email' => $this->request->getVar('email'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'token' => $token,
            'status' => 'active',
            'role' => 'admin',
        ];

        $u = $user->save($data);
        if ($u) {
            return $this->respond(['msg' => 'Admin registered successfully', 'token' => $token]);
        } else {
            return $this->respond(['msg' => 'Admin registration failed'], 500);
        }
    }

 

    public function users($id = null)
    {
        $model = new UserModel();
        $users = $model->findAll();

        return $this->respond($users);
    }

    public function create_user()
    {
        $userModel = new UserModel();
        $data = [
            'username' => $this->request->getVar('username'),
            'email' => $this->request->getVar('email'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'role' => $this->request->getVar('role'),
            'category' => $this->request->getVar('category'),
            'status' => 'active',
            'token' => $this->generateToken(50),
        ];

        $userModel->insert($data);

        return $this->respond(['msg' => 'User created successfully']);
    }

    public function update_user($id)
    {
        $userModel = new UserModel();
        $data = [
            'username' => $this->request->getVar('username'),
            'email' => $this->request->getVar('email'),
            'role' => $this->request->getVar('role'),
            'category' => $this->request->getVar('category'),
        ];

        $userModel->update($id, $data);

        return $this->respond(['msg' => 'User updated successfully']);
    }

    public function delete_user($id)
    {
        $userModel = new UserModel();
        $userModel->delete($id);

        return $this->respond(['msg' => 'User deleted successfully']);
    }
    public function forgotPassword()
    {
        $email = $this->request->getPost('email');
        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();
    
        if ($user) {
            $resetToken = bin2hex(random_bytes(16));
            $resetTokenCreatedAt = date('Y-m-d H:i:s');
            $userModel->update($user['id'], [
                'reset_token' => $resetToken,
                'reset_token_created_at' => $resetTokenCreatedAt
            ]);
    
            $this->sendPasswordResetEmail($email, $resetToken);
        }
    
        // Always return a success message to prevent email enumeration
        return $this->respond(['message' => 'If the email exists in our system, password reset instructions will be sent.']);
    }
    
    private function sendPasswordResetEmail($email, $resetToken)
    {
        $emailService = \Config\Services::email();
    
        $emailService->setFrom('stmswdapp@gmail.com', 'San Teodoro MSWD');
        $emailService->setTo($email);
    
        $emailService->setSubject('Reset Your STMSWD Password');
        $emailService->setMailType('html');
    
        $resetLink = site_url("reset-password/{$resetToken}");
        $emailContent = $this->getPasswordResetEmailBody($resetLink);
    
        $emailService->setMessage($emailContent);
    
        if (!$emailService->send()) {
            log_message('error', 'Password reset email could not be sent. Error: ' . $emailService->printDebugger(['headers']));
        }
    }
    
    private function getPasswordResetEmailBody($resetLink)
    {
        return '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Reset Your STMSWD Password</title>
        </head>
        <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 0;">
            <table role="presentation" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 0;">
                        <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                            <div style="text-align: center; margin-bottom: 20px;">
                                <img src="https://via.placeholder.com/150x50?text=STMSWD+Logo" alt="STMSWD Logo" style="max-width: 150px;">
                            </div>
                            <div style="background-color: #ffffff; border-radius: 5px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); padding: 40px;">
                                <h1 style="color: #4CAF50; font-size: 24px; margin-bottom: 20px; text-align: center;">Reset Your Password</h1>
                                <p style="margin-bottom: 30px; text-align: center;">We received a request to reset your password. Click the button below to create a new password:</p>
                                <div style="text-align: center;">
                                    <a href="' . $resetLink . '" style="display: inline-block; background-color: #4CAF50; color: #ffffff; text-decoration: none; padding: 12px 30px; border-radius: 5px; font-weight: bold;">Reset Password</a>
                                </div>
                                <p style="margin-top: 30px; font-size: 14px; color: #666666; text-align: center;">If you didn\'t request a password reset, you can safely ignore this email.</p>
                            </div>
                            <div style="margin-top: 20px; text-align: center; font-size: 12px; color: #888888;">
                                <p>&copy; ' . date('Y') . ' San Teodoro MSWD. All rights reserved.</p>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ';
    }

   public function resetPasswordForm($token)
    {
        $userModel = new UserModel();
        $user = $userModel->where('reset_token', $token)->first();

        if ($user) {
            $tokenCreatedAt = strtotime($user['reset_token_created_at']);
            if (time() - $tokenCreatedAt > 3600) {
                return view('/reset_password_expired');
            }

            return view('/reset_password_form', ['token' => $token]);
        } else {
            return view('/reset_password_invalid');
        }
    }

    public function resetPassword()
    {
        $token = $this->request->getPost('token');
        $newPassword = $this->request->getPost('new_password');

        $userModel = new UserModel();
        $user = $userModel->where('reset_token', $token)->first();

        if ($user) {
            $tokenCreatedAt = strtotime($user['reset_token_created_at']);
            if (time() - $tokenCreatedAt > 3600) {
                return $this->fail('Password reset token has expired. Please request a new one.', 400);
            }

            $userModel->update($user['id'], [
                'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                'reset_token' => null,
                'reset_token_created_at' => null
            ]);

            return $this->respond(['message' => 'Password has been reset successfully. You can now log in with your new password.']);
        } else {
            return $this->fail('Invalid reset token', 400);
        }
    }
}