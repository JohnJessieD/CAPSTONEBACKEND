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
        $email = $this->request->getVar('email');
        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();

        if (!$user) {
            return $this->fail('Email not found', 404);
        }

        // Generate a unique token
        $token = bin2hex(random_bytes(32));
        $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Save the token and expiration in the database
        $userModel->update($user['id'], [
            'reset_token' => $token,
            'reset_token_expiration' => $expiration
        ]);

        // Send email with reset link
        if ($this->sendPasswordResetEmail($email, $token)) {
            return $this->respond(['message' => 'Password reset instructions have been sent to your email.']);
        } else {
            return $this->fail('Failed to send email. Please try again later.', 500);
        }
    }

    private function sendPasswordResetEmail($email, $resetToken)
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

            // Reset password link and subject
            $resetLink = site_url("reset-password/$resetToken");
            $mail->Subject = 'Reset Your STMSWD Password';

            // HTML email body
            $mail->isHTML(true);
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                    <div style='max-width: 600px; margin: auto; background-color: #ffffff; padding: 20px; border-radius: 8px;'>
                        <h2 style='color: #4CAF50; text-align: center;'>San Teodoro MSWD Password Reset</h2>
                        <p style='color: #333333; text-align: center; font-size: 16px;'>
                            You have requested to reset your password. Click the button below to set a new password.
                        </p>
                        <div style='text-align: center; margin-top: 20px;'>
                            <a href='$resetLink' style='padding: 10px 20px; background-color: #4CAF50; color: #ffffff;
                            text-decoration: none; border-radius: 5px; font-size: 16px;'>Reset Password</a>
                        </div>
                        <p style='color: #555555; text-align: center; font-size: 14px; margin-top: 20px;'>
                            If you did not request a password reset, please ignore this email.
                        </p>
                    </div>
                </div>
            ";

            $mail->send();
            return true;
        } catch (Exception $e) {
            log_message('error', "Password reset email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    public function resetPasswordForm($token)
    {
        $userModel = new UserModel();
        $user = $userModel->where('reset_token', $token)->first();

        if (!$user) {
            return view('reset_password_invalid');
        }

        $tokenExpiration = strtotime($user['reset_token_expiration']);
        if (time() > $tokenExpiration) {
            return view('reset_password_expired');
        }

        return view('reset_password_form', ['token' => $token]);
    }

    public function resetPassword($token)
    {
        $newPassword = $this->request->getPost('new_password');
        $confirmPassword = $this->request->getPost('confirm_password');

        if ($newPassword !== $confirmPassword) {
            return $this->fail('Passwords do not match', 400);
        }

        $userModel = new UserModel();
        $user = $userModel->where('reset_token', $token)->first();

        if (!$user) {
            return view('reset_password_invalid');
        }

        $tokenExpiration = strtotime($user['reset_token_expiration']);
        if (time() > $tokenExpiration) {
            return view('reset_password_expired');
        }

        $userModel->update($user['id'], [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'reset_token' => null,
            'reset_token_expiration' => null
        ]);

        return $this->respond(['message' => 'Password has been reset successfully. You can now log in with your new password.']);
    }
}