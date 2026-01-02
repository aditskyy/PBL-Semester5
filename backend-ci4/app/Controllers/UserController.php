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

    // 1. Validasi keberadaan User
    if (!$user) {
        return redirect()->back()->with('error', 'Username tidak ditemukan');
    }

    // 2. KEAMANAN: Cek password menggunakan password_verify (Bukan !== lagi)
    if (!password_verify($password, $user['password'])) {
        return redirect()->back()->with('error', 'Password salah');
    }

    // 3. Set session jika login sukses
    session()->set([
        'logged_in' => true,
        'user_id'   => $user['id'],
        'username'  => $user['username'],
        'role'      => $user['role']
    ]);

    // 4. LOGGING: Catat ke tabel log_antrian
    $db = \Config\Database::connect();
    $db->table('log_antrian')->insert([
        'id_antrian' => null, // Login tidak butuh ID antrean
        'user_id'    => $user['id'],
        'aksi'       => 'LOGIN', // Pastikan ENUM sudah diupdate ke 'LOGIN'
        'waktu'      => date('Y-m-d H:i:s')
    ]);

    // 5. Redirect sesuai role
    if ($user['role'] === 'admin') {
        return redirect()->to('/admin/dashboard');
    } else if ($user['role'] === 'operator') {
        return redirect()->to('/operator/select');
    } else {
        return redirect()->to('/guest/dashboard');
    }
 }

 public function registerForm()
{
    return view('register');
}

// Memproses data registrasi
public function registerProcess()
{
    $model = new UserModel();

    // 1. Ambil data dari input form
    $username = $this->request->getPost('username');
    $password = $this->request->getPost('password');
    $role     = $this->request->getPost('role');

    // 2. KEAMANAN: Hash password sebelum disimpan
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 3. Simpan ke database
    $model->insert([
        'username' => $username,
        'password' => $hashedPassword,
        'role'     => $role
    ]);

    // 4. Kembali ke login dengan pesan sukses
    return redirect()->to('/login')->with('success', 'Akun berhasil dibuat! Silakan login.');
 }

 public function logout()
{
    $db = \Config\Database::connect();
    $userId = session()->get('user_id');

    if ($userId) {
        $db->table('log_antrian')->insert([
            'id_antrian' => null,
            'user_id'    => $userId,
            'aksi'       => 'LOGOUT', 
            'waktu'      => date('Y-m-d H:i:s')
        ]);
    }

    session()->destroy();
    return redirect()->to('/login')->with('success', 'Berhasil keluar');
}
}
