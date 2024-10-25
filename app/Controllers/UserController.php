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
            if ($data['status'] !== 'active') {
                return $this->respond(['msg' => 'Please verify your email before logging in'], 403);
            }

            $pass = $data['password'];
            $authenticatePassword = password_verify($password, $pass);
            if ($authenticatePassword) {
                $category = $data['category'];
                return $this->respond([
                    'msg' => 'okay',
                    'token' => $data['token'],
                    'role' => $data['role'],
                    'category' => $category
                ]);
            } else {
                return $this->respond(['msg' => 'Invalid credentials'], 401);
            }
        }
        return $this->respond(['msg' => 'User not found'], 404);
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

    public function loginAdmin()
    {
        $user = new UserModel();
        $username = $this->request->getVar('username');
        $password = $this->request->getVar('password');
        $data = $user->where('username', $username)->where('role', 'admin')->first();

        if ($data) {
            $pass = $data['password'];
            $authenticatePassword = password_verify($password, $pass);
            if ($authenticatePassword) {
                return $this->respond(['msg' => 'okay', 'token' => $data['token']]);
            } else {
                return $this->respond(['msg' => 'Invalid credentials'], 401);
            }
        } else {
            return $this->respond(['msg' => 'Admin not found'], 404);
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
        $user = new UserModel();
        $userData = $user->where('email', $email)->first();

        if ($userData) {
            $resetToken = $this->generateToken(32);
            $resetTokenCreatedAt = date('Y-m-d H:i:s');
            $user->update($userData['id'], [
                'reset_token' => $resetToken,
                'reset_token_created_at' => $resetTokenCreatedAt
            ]);

            $this->sendPasswordResetEmail($email, $resetToken);

            return $this->respond(['msg' => 'Password reset instructions have been sent to your email.']);
        } else {
            return $this->respond(['msg' => 'If the email exists in our system, password reset instructions will be sent.']);
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
    
            // Reset link and subject
            $resetLink = site_url("reset-password/$resetToken");
            $mail->Subject = 'Reset Your STMSWD Password';
    
            // HTML email body
            $mail->isHTML(true);
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4;'>
                    <div style='max-width: 600px; margin: auto; background-color: #ffffff; padding: 20px; border-radius: 8px;'>
                        <h2 style='color: #4CAF50; text-align: center;'>Password Reset Request</h2>
                        <p style='color: #333333; text-align: center; font-size: 16px;'>
                            We received a request to reset your password. Click the link below to proceed.
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
        } catch (Exception $e) {
            log_message('error', "Password reset email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
    public function resetPasswordForm($token)
    {
        $user = new UserModel();
        $userData = $user->where('reset_token', $token)->first();

        if ($userData) {
            $tokenCreatedAt = strtotime($userData['reset_token_created_at']);
            if (time() - $tokenCreatedAt > 3600) {
                return view('reset_password_expired');
            }

            return view('reset_password_form', ['token' => $token]);
        } else {
            return view('reset_password_invalid');
        }
    }

    public function resetPassword($token)
    {
        $user = new UserModel();
        $userData = $user->where('reset_token', $token)->first();

        if ($userData) {
            $tokenCreatedAt = strtotime($userData['reset_token_created_at']);
            if (time() - $tokenCreatedAt > 3600) {
                return $this->respond(['msg' => 'Password reset token has expired. Please request a new one.'], 400);
            }

            $newPassword = $this->request->getVar('new_password');
            $user->update($userData['id'], [
                'password' => password_hash($newPassword, PASSWORD_DEFAULT),
                'reset_token' => null,
                'reset_token_created_at' => null
            ]);

            return $this->respond(['msg' => 'Password has been reset successfully. You can now log in with your new password.']);
        } else {
            return $this->respond(['msg' => 'Invalid reset token'], 400);
        }
    }
}