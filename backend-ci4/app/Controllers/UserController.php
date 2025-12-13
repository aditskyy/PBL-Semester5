<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\RESTful\ResourceController;

class UserController extends ResourceController
{
    protected $format = 'json';

    public function loginForm()
{
    return view('login'); // file view login kamu
}

public function loginProcess()
{
    $username = $this->request->getPost('username');
    $password = $this->request->getPost('password');

    $model = new UserModel();
    $user = $model->where('username', $username)->first();

    if (!$user) {
        return redirect()->back()->with('error', 'Username tidak ditemukan');
    }

    if ($user['password'] !== $password) {
        return redirect()->back()->with('error', 'Password salah');
    }

    // Set session
    session()->set([
        'logged_in' => true,
        'user_id' => $user['id'],
        'username' => $user['username'],
        'role' => $user['role']
    ]);

    // Redirect sesuai role
    if ($user['role'] === 'admin') {
        return redirect()->to('/admin/dashboard');
    }
    else if ($user['role'] === 'operator') {
        return redirect()->to('/operator/select');
    }
    else {
        return redirect()->to('/guest/dashboard');
    }
 }
}
