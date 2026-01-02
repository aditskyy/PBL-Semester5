<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\LoketModel;
use App\Models\AntrianModel;
use App\Models\LogAntrianModel;
use App\Models\JenisLoketModel;
use CodeIgniter\Controller;

class OperatorController extends Controller
{

    private function emitSocket($event, $data)
    {
        $client = \Config\Services::curlrequest();
        try {
            $client->post('http://192.168.1.7:5000/api/emit', [
                'json' => [
                    'event' => $event,
                    'data'  => $data
                ],
                'timeout' => 2
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Socket emit gagal: ' . $e->getMessage());
        }
    }
    
public function auth()
{
    $session = session();
    $userModel = new UserModel();

    $username = $this->request->getPost('username');
    $password = $this->request->getPost('password');

    $user = $userModel->where('username', $username)->first();

    // PERBAIKAN: Gunakan password_verify untuk mengecek hash
    if ($user && password_verify($password, $user['password'])) {

        // Simpan ke session
        $session->set([
            'logged_in' => true,
            'username'  => $user['username'],
            'user_id'   => $user['id'],
            'role'      => $user['role'] // Tambahkan role untuk filter akses
        ]);

        // 📝 LOG: Tambahkan pencatatan LOGIN di sini agar konsisten
        $db = \Config\Database::connect();
        $db->table('log_antrian')->insert([
            'user_id' => $user['id'],
            'aksi'    => 'LOGIN',
            'waktu'   => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/operator/select');
    }

    $session->setFlashdata('error', 'Username atau password salah.');
    return redirect()->back();
}

public function select()
{
    $session = session();

    // Cek apakah user sudah login
    if (!$session->get('logged_in')) {
        return redirect()->to('/operator');
    }

    // Panggil model JenisLoketModel untuk ambil data jenis layanan
    $jenisModel = new \App\Models\JenisLoketModel();
    $data['jenis'] = $jenisModel->findAll();

    // Kirim data ke view operator_select
    return view('/operator/operator_select', $data);
}

public function getLoketByJenis($kodeJenis)
{
    $loketModel = new \App\Models\LoketModel();

    // Ambil langsung dari tabel loket sesuai kode_jenis
    $loketList = $loketModel
        ->select('kode_loket, nama_loket')
        ->where('kode_jenis', $kodeJenis)
        ->findAll();

    return $this->response->setJSON($loketList);
}

   public function setOperatorSession()
{
    $session = session();

    $kodeJenis = $this->request->getPost('kode_jenis');
    $kodeLoket = $this->request->getPost('kode_loket');

    // Cek apakah kedua input terisi
    if (empty($kodeJenis) || empty($kodeLoket)) {
        return redirect()->back()->with('error', 'Pilih jenis layanan dan loket terlebih dahulu.');
    }

    // Simpan ke session
    $session->set([
        'kode_jenis' => $kodeJenis,
        'kode_loket' => $kodeLoket,
        'isOperatorLoggedIn' => true
    ]);

    // Redirect ke dashboard operator
    return redirect()->to(site_url('operator/dashboard'));
}

public function dashboard()
{
    $session = session();

    // 1. Cek login
    if (!$session->get('logged_in')) {
        return redirect()->to('/operator');
    }

    // 2. PERUBAHAN UTAMA: Ambil dari URL (?jenis=A&loket=A-01) 
    // Jika tidak ada di URL, baru ambil dari Session (fallback)
    $kodeJenis = $this->request->getGet('jenis') ?: $session->get('kode_jenis');
    $kodeLoket = $this->request->getGet('loket') ?: $session->get('kode_loket');

    // Tambahan validasi jika keduanya kosong
    if (!$kodeJenis || !$kodeLoket) {
        return redirect()->to('/operator/select')->with('error', 'Silakan pilih jenis layanan dan loket.');
    }

    $antrianModel = new \App\Models\AntrianModel();
    $loketModel = new \App\Models\LoketModel();
    $detailLoket = $loketModel->where('kode_loket', $kodeLoket)->first();

    // 3. Ambil antrian sedang dipanggil (Berdasarkan parameter yang ditangkap di atas)
    $antrianSekarang = $antrianModel
        ->where('kode_jenis', $kodeJenis)
        ->where('kode_loket', $kodeLoket)
        ->where('status', 'Dipanggil')
        ->orderBy('id_antrian', 'DESC')
        ->first();

    // 4. Ambil antrian berikutnya (Berdasarkan parameter yang ditangkap di atas)
    $antrianBerikut = $antrianModel
        ->where('kode_jenis', $kodeJenis)
        ->where('status', 'Menunggu')
        ->orderBy('id_antrian', 'ASC')
        ->first();

    // 5. Loket aktif sesuai jenis
    $loket = $loketModel
        ->where('kode_jenis', $kodeJenis)
        ->findAll();

    // 6. Kirim data ke view
    return view('/operator/operator_dashboard', [
        'nama_loket'        => $detailLoket['nama_loket'] ?? 'Loket Tidak Terdaftar', 
        'kode_jenis'        => $kodeJenis,
        'kode_loket'        => $kodeLoket,
        'loket'             => $loket, // <--- SESUAIKAN DI SINI (Ganti $semuaLoket jadi $loket)
        'antrianSekarang'   => $antrianSekarang,
        'antrianBerikut'    => $antrianBerikut,
    ]);
}

public function panggilSelanjutnya()
{
    $session = session();

    // 1. Prioritas ambil dari POST agar identitas tab tidak tertukar
    $kodeJenis = $this->request->getPost('kode_jenis') ?: $session->get('kode_jenis');
    $kodeLoket = $this->request->getPost('kode_loket') ?: $session->get('kode_loket');
    $userId    = $session->get('user_id');

    // Cek login
    if (!$userId) {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Sesi habis, silakan login kembali'], 401);
    }

    $antrianModel = new \App\Models\AntrianModel();
    $logModel     = new \App\Models\LogAntrianModel();
    $loketModel   = new \App\Models\LoketModel();

    // 2. Cari antrean yang sedang 'Dipanggil' di loket ini untuk diselesaikan
    $antrianLama = $antrianModel->where('kode_loket', $kodeLoket)
                                ->where('status', 'Dipanggil')
                                ->first();

    if ($antrianLama) {
        // Update jadi Selesai
        $antrianModel->update($antrianLama['id_antrian'], [
            'status'     => 'Selesai',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // 📝 LOG: Catat antrean sebelumnya telah SELESAI
        $logModel->insert([
            'id_antrian' => $antrianLama['id_antrian'],
            'aksi'       => 'SELESAI',
            'user_id'    => $userId,
            'waktu'      => date('Y-m-d H:i:s')
        ]);
    }

    // 3. Cari antrean berikutnya (Menunggu)
    $antrianBerikut = $antrianModel
        ->where('kode_jenis', $kodeJenis)
        ->where('status', 'Menunggu')
        ->orderBy('id_antrian', 'ASC')
        ->first();

    if ($antrianBerikut) {
        // Update jadi Dipanggil
        $antrianModel->update($antrianBerikut['id_antrian'], [
            'status'     => 'Dipanggil',
            'kode_loket' => $kodeLoket,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // 📝 LOG: Catat antrean baru sedang DIPANGGIL
        $logModel->insert([
            'id_antrian' => $antrianBerikut['id_antrian'],
            'aksi'       => 'PANGGIL',
            'user_id'    => $userId,
            'waktu'      => date('Y-m-d H:i:s')
        ]);

        // Ambil data warna loket
        $dataLoket = $loketModel->where('kode_loket', $kodeLoket)->first();

        // 🔥 Socket Emit ke Flask/TV
        $this->emitSocket('panggil_antrean', [
            'nomor'      => $antrianBerikut['kode_jenis'] . str_pad($antrianBerikut['nomor'], 3, '0', STR_PAD_LEFT),
            'kode_loket' => $kodeLoket,
            'color'      => $dataLoket['warna'] ?? '#1E88E5'
        ]);

        return $this->response->setJSON(['status' => 'success', 'data' => $antrianBerikut]);
    }

    return $this->response->setJSON(['status' => 'error', 'message' => 'Antrean sudah habis']);
}

public function panggilUlang()
{
    $session = session();
    // Gunakan POST agar identitas tab tidak tertukar
    $kodeJenis = $this->request->getPost('kode_jenis') ?: $session->get('kode_jenis');
    $kodeLoket = $this->request->getPost('kode_loket') ?: $session->get('kode_loket');
    $userId    = $session->get('user_id');

    $antrian = $this->antrianModel
        ->where('kode_jenis', $kodeJenis)
        ->where('status', 'Dipanggil')
        ->where('kode_loket', $kodeLoket)
        ->first();

    if ($antrian) {
        // Tambahkan Log Recall (Opsional tapi disarankan)
        $this->logModel->insert([
            'id_antrian' => $antrian['id_antrian'],
            'aksi'       => 'RECALL',
            'user_id'    => $userId,
            'waktu'      => date('Y-m-d H:i:s')
        ]);

        // 🔥 SOCKET EMIT RECALL
        $this->emitSocket('panggil_ulang', [
            'nomor'      => $antrian['kode_jenis'] . str_pad($antrian['nomor'], 3, '0', STR_PAD_LEFT),
            'kode_loket' => $kodeLoket
        ]);
        
        return $this->response->setJSON(['status' => 'success', 'message' => 'Panggilan ulang dikirim']);
    }

    return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak ada antrian aktif'], 404);
}

public function selesai()
{
    $session = session();
    // Gunakan POST agar identitas tab tidak tertukar
    $kodeJenis = $this->request->getPost('kode_jenis') ?: $session->get('kode_jenis');
    $kodeLoket = $this->request->getPost('kode_loket') ?: $session->get('kode_loket');
    $userId    = $session->get('user_id');

    $antrianDipanggil = $this->antrianModel
        ->where('kode_jenis', $kodeJenis)
        ->where('status', 'Dipanggil')
        ->where('kode_loket', $kodeLoket)
        ->first();

    if ($antrianDipanggil) {
        $this->antrianModel->update($antrianDipanggil['id_antrian'], [
            'status'     => 'Selesai',
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $this->logModel->insert([
            'id_antrian' => $antrianDipanggil['id_antrian'],
            'aksi'       => 'SELESAI',
            'user_id'    => $userId,
            'waktu'      => date('Y-m-d H:i:s')
        ]);

        // 🔥 SOCKET EMIT
        $this->emitSocket('selesai_antrean', [
            'nomor'      => $antrianDipanggil['kode_jenis'] . str_pad($antrianDipanggil['nomor'], 3, '0', STR_PAD_LEFT),
            'kode_loket' => $kodeLoket
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Antrian selesai']);
    }

    return $this->response->setJSON(['status' => 'error', 'message' => 'Tidak ada antrian untuk diselesaikan'], 404);
}

    public function resetAntrian()
    {
        $antrianModel = new AntrianModel();

        // Ubah semua antrian "Selesai" jadi "Menunggu"
        $update = $antrianModel
            ->where('status', 'Selesai')
            ->set([
                'status' => 'Menunggu',
                'updated_at' => date('Y-m-d H:i:s')
            ])
            ->update();

        if ($update) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Semua data antrian berhasil direset ke status Menunggu.'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Tidak ada data antrian yang perlu direset.'
        ]);
    }

public function index()
{
    // Jika user mengakses /operator, langsung lempar ke halaman login utama
    return redirect()->to(base_url('login'));
}

public function logoutOperator()
{
    $session = session();
    $db = \Config\Database::connect();
    $userId = $session->get('user_id');

    if ($userId) {
        // 📝 LOG: Catat aktivitas LOGOUT
        $db->table('log_antrian')->insert([
            'user_id' => $userId,
            'aksi'    => 'LOGOUT',
            'waktu'   => date('Y-m-d H:i:s')
        ]);
    }
    
    $session->destroy();
    return redirect()->to(base_url('login'))->with('success', 'Berhasil logout.');
}
}

