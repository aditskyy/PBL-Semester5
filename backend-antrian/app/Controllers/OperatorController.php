<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LoketModel;
use App\Models\AntrianModel;
use CodeIgniter\Controller;

class OperatorController extends Controller
{
    public function login()
    {
        return view('operator/login');
    }

public function auth()
{
    $session = session();
    $userModel = new UserModel();

    $username = $this->request->getPost('username');
    $password = $this->request->getPost('password');

    $user = $userModel->where('username', $username)->first();

    if ($user && $user['password'] === $password) {

        // Simpan ke session
        $session->set([
            'logged_in' => true,
            'username' => $user['username'],
            'kode_jenis' => $user['kode_jenis'], // A / B / C
        ]);

        return redirect()->to('/operator/dashboard');
    }

    $session->setFlashdata('error', 'Username atau password salah.');
    return redirect()->back();
}

public function dashboard()
{
    $session = session();

    if (!$session->get('logged_in')) {
        return redirect()->to('/operator/login');
    }

    $kodeJenis = $session->get('kode_jenis');

    $antrianModel = new \App\Models\AntrianModel();
    $loketModel = new \App\Models\LoketModel();

    // Antrian sedang dipanggil
    $antrianSekarang = $antrianModel
        ->where('kode_jenis', $kodeJenis)
        ->where('status', 'Dipanggil')   // ← HURUF SAMA DENGAN DATABASE
        ->orderBy('id_antrian', 'DESC')
        ->first();

    // Antrian berikutnya (menunggu)
    $antrianBerikut = $antrianModel
        ->where('kode_jenis', $kodeJenis)
        ->where('status', 'Menunggu')   // ← HURUF SAMA DENGAN DATABASE
        ->orderBy('id_antrian', 'ASC')
        ->first();

    // Loket aktif sesuai jenis operator
    $loket = $loketModel
        ->where('kode_jenis', $kodeJenis)
        ->findAll();

    return view('operator/dashboard', [
        'username' => $session->get('username'),
        'role' => $session->get('role'),
        'kode_jenis' => $kodeJenis,
        'loket' => $loket,
        'antrianSekarang' => $antrianSekarang,
        'antrianBerikut' => $antrianBerikut
    ]);
}

public function panggil_ulang()
{
    $id = $this->request->getPost('id_antrian');
    $model = new AntrianModel();

    if($id) {
        // bisa ditambah logika untuk kirim notifikasi suara/display
        $model->update($id, ['status' => 'Dipanggil']);
    }

    return redirect()->to(base_url('operator/dashboard'));
}

public function selesai()
{
    $id = $this->request->getPost('id_antrian');
    $model = new AntrianModel();
    if($id) {
        $model->update($id, ['status' => 'Selesai']);
    }
    return redirect()->to(base_url('operator/dashboard'));
 }
}
