<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\UserModel;

class Auth extends ResourceController
{
    public function login()
    {
        $userModel = new UserModel();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $userModel->where('username', $username)->first();

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Username atau password salah'
            ], 401);
        }

        // return info operator dan loketnya
        return $this->respond([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user_id' => $user['id'],
                'username' => $user['username'],
                'kode_loket' => $user['kode_loket'],
                'kode_jenis' => $user['kode_jenis']
            ]
        ]);
    }
}
