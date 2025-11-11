<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class UserController extends ResourceController
{
    protected $format = 'json';

    public function login()
    {
        $model = new UserModel();
        $request = $this->request->getJSON();

        $username = $request->username ?? '';
        $password = $request->password ?? '';

        $user = $model->where('username', $username)->first();

        if (!$user) {
            return $this->respond(['status' => 'error', 'message' => 'User tidak ditemukan'], 404);
        }

        if ($user['password'] !== $password) { // nanti bisa diganti password_verify() kalau pakai hash
            return $this->respond(['status' => 'error', 'message' => 'Password salah'], 401);
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'role' => $user['role'] ?? 'operator'
            ]
        ]);
    }
}
