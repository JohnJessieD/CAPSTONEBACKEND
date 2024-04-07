<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Restful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;

class UserController extends ResourceController
{
    use ResponseTrait;

    public function register()
    {
        $user = new UserModel();
        $token = $this->verification(50);
        $userRole = $this->request->getVar('role'); // Get the selected user role
    
        $data = [
            'username' => $this->request->getVar('username'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'token' => $token,
            'status' => 'active',
            'role' => $userRole, // Use the selected user role in the data array
        ];
    
        $u = $user->save($data);
        if ($u) {
            return $this->respond(['msg' => 'okay', 'token' => $token]);
        } else {
            return $this->respond(['msg' => 'failed']);
        }
    }
    

    public function verification($length)
    {
        $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($str_result), 0, $length);
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
                return $this->respond(['msg' => 'okay', 'token' => $data['token'], 'role' => $data['role']]);
            } else {
                return $this->respond(['msg' => 'error'], 200);
            }
        }
        return $this->respond(['msg' => 'userNotFound'], 404);
    }
    
    public function registerAdmin()
    {
        $user = new UserModel();
        $token = $this->verification(50);
        $data = [
            'username' => $this->request->getVar('username'),
            'password' => password_hash($this->request->getVar('password'), PASSWORD_DEFAULT),
            'token' => $token,
            'status' => 'active',
            'role' => 'admin', // Set the role as 'admin' for admin users
        ];

        $u = $user->save($data);
        if ($u) {
            return $this->respond(['msg' => 'okay', 'token' => $token]);
        } else {
            return $this->respond(['msg' => 'failed']);
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
                return $this->respond(['msg' => 'error'], 200);
            }
        } else {
            return $this->respond(['msg' => 'error'], 200);
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
            'username' => $this->request->getVar('username'), // Assuming 'name' is a field in your 'users' table
            // Add other fields as needed
        ];

        $userModel->insert($data);

        return $this->respond(['msg' => 'User created successfully']);
    }

    public function update_user($id)
    {
        $userModel = new UserModel();
        $data = [
            'username' => $this->request->getVar('username'),
            'role' => $this->request->getVar('role'), // Add other fields as needed
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
}
