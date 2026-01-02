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

    // 1. Verifikasi User dan Hash Password
    if (!$user || !password_verify($password, $user['password'])) {
        return $this->respond([
            'status' => 'error',
            'message' => 'Username atau password salah'
        ], 401);
    }

    // 2. CATAT KE LOG_ANTRIAN (Integrasi Database)
    $db = \Config\Database::connect();
    $db->table('log_antrian')->insert([
        'id_antrian' => null,      // Tidak terkait antrean spesifik
        'user_id'    => $user['id'],
        'aksi'       => 'LOGIN',    // Pastikan ENUM di DB sudah diubah ke 'LOGIN'
        'waktu'      => date('Y-m-d H:i:s')
    ]);

    // 3. Return info operator dan loketnya
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

public function register()
{
    $userModel = new UserModel();

    // 1. Ambil input dari request API
    $username = $this->request->getPost('username');
    $password = $this->request->getPost('password');
    $role     = $this->request->getPost('role') ?? 'operator'; // default ke operator
    $kodeLoket = $this->request->getPost('kode_loket');
    $kodeJenis = $this->request->getPost('kode_jenis');

    // 2. Cek apakah username sudah terpakai (Validasi)
    if ($userModel->where('username', $username)->first()) {
        return $this->respond([
            'status' => 'error',
            'message' => 'Username sudah terdaftar'
        ], 400);
    }

    // 3. Simpan data dengan password_hash
    $userModel->insert([
        'username'   => $username,
        'password'   => password_hash($password, PASSWORD_DEFAULT), // Enkripsi hash
        'role'       => $role,
        'kode_loket' => $kodeLoket,
        'kode_jenis' => $kodeJenis
    ]);

    return $this->respond([
        'status' => 'success',
        'message' => 'Registrasi berhasil'
    ], 201);
}

public function logout()
{
    $db = \Config\Database::connect();
    $userId = $this->request->getPost('user_id');

    if ($userId) {
        $db->table('log_antrian')->insert([
            'id_antrian' => null,
            'user_id'    => $userId,
            'aksi'       => 'LOGOUT',
            'waktu'      => date('Y-m-d H:i:s')
        ]);
        
        return $this->respond(['status' => 'success', 'message' => 'Logout tercatat']);
    }

    return $this->respond(['status' => 'error', 'message' => 'User ID tidak ditemukan'], 400);
}
}
