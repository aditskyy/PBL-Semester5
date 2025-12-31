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

    if ($user && $user['password'] === $password) {

        // Simpan ke session
        $session->set([
            'logged_in' => true,
            'username' => $user['username'],
            'user_id' => $user['id'],  
            //'kode_jenis' => $user['kode_jenis'], 
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

    // Cek apakah operator sudah memilih jenis & loket
    if (!$session->get('logged_in')) {
        return redirect()->to('/operator');
    }

    $kodeJenis = $session->get('kode_jenis');
    $kodeLoket = $session->get('kode_loket');

    $antrianModel = new \App\Models\AntrianModel();
    $loketModel = new \App\Models\LoketModel();

    // Ambil antrian sedang dipanggil (status = Dipanggil)
    $antrianSekarang = $antrianModel
        ->where('kode_jenis', $kodeJenis)
        ->where('kode_loket', $kodeLoket)
        ->where('status', 'Dipanggil')
        ->orderBy('id_antrian', 'DESC')
        ->first();

    // Ambil antrian berikutnya (status = Menunggu)
    $antrianBerikut = $antrianModel
    ->where('kode_jenis', $kodeJenis)
    ->where('kode_loket', $kodeLoket)
    ->where('status', 'Menunggu')
    ->orderBy('id_antrian', 'ASC')
    ->first();

    // Loket aktif sesuai jenis operator
    $loket = $loketModel
        ->where('kode_jenis', $kodeJenis)
        ->findAll();

    // Kirim data ke view
    return view('/operator/operator_dashboard', [
        'kode_jenis'        => $kodeJenis,
        'kode_loket'        => $kodeLoket,
        'loket'             => $loket,
        'antrianSekarang'   => $antrianSekarang,
        'antrianBerikut'    => $antrianBerikut,
    ]);
}

public function panggilSelanjutnya()
{
    $session = session();

    // Cek login dan kelengkapan session operator
    if (!$session->get('logged_in') || !$session->get('kode_jenis') || !$session->get('kode_loket')) {
        return redirect()->to('/operator');
    }

    $kodeJenis = $session->get('kode_jenis');
    $kodeLoket = $session->get('kode_loket'); // Pastikan kode_loket diambil dari session
    $userId    = $session->get('user_id');

    $antrianModel = new \App\Models\AntrianModel();
    $logModel     = new \App\Models\LogAntrianModel();
    $loketModel   = new \App\Models\LoketModel();

    // 1. Tandai antrian yang sedang dipanggil di loket ini menjadi 'Selesai'
    $antrianModel->where('kode_loket', $kodeLoket)
                 ->where('status', 'Dipanggil')
                 ->set([
                     'status' => 'Selesai',
                     'updated_at' => date('Y-m-d H:i:s')
                 ])
                 ->update();

    // 2. Cari antrian berikutnya berdasarkan jenis layanan yang statusnya masih 'Menunggu'
    $antrianBerikut = $antrianModel
        ->where('kode_jenis', $kodeJenis)
        ->where('status', 'Menunggu')
        ->orderBy('id_antrian', 'ASC')
        ->first();

    if ($antrianBerikut) {
        // 3. Update antrian tersebut menjadi status 'Dipanggil' dan arahkan ke loket operator
        $antrianModel->update($antrianBerikut['id_antrian'], [
            'status'     => 'Dipanggil',
            'kode_loket' => $kodeLoket,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // 4. Catat ke dalam Log Antrian
        $logModel->insert([
            'id_antrian' => $antrianBerikut['id_antrian'],
            'aksi'       => 'PANGGIL',
            'user_id'    => $userId,
            'waktu'      => date('Y-m-d H:i:s')
        ]);

        // 5. 🎨 Ambil data warna dari tabel loket secara dinamis
        $dataLoket = $loketModel->where('kode_loket', $kodeLoket)->first();

        // 6. 🔥 Kirim data ke WebSocket (Flask) agar tampilan TV berubah secara Real-time
        $this->emitSocket('panggil_antrean', [
            'nomor'      => $antrianBerikut['kode_jenis'] . str_pad($antrianBerikut['nomor'], 3, '0', STR_PAD_LEFT),
            'kode_loket' => $kodeLoket,
            'color'      => $dataLoket['warna'] ?? '#1E88E5' // Mengirim warna dari DB
        ]);
    }

    return redirect()->to('/operator/dashboard');
}

public function panggilUlang()
    {
        $session = session();
        $kodeJenis = $session->get('kode_jenis');
        $kodeLoket = $session->get('kode_loket');

        $antrian = $this->antrianModel
            ->where('kode_jenis', $kodeJenis)
            ->where('status', 'Dipanggil')
            ->where('kode_loket', $kodeLoket)
            ->first();

        if ($antrian) {
            // 🔥 SOCKET EMIT RECALL
            $this->emitSocket('panggil_ulang', [
                'nomor'      => $antrian['kode_jenis'] . str_pad($antrian['nomor'], 3, '0', STR_PAD_LEFT),
                'kode_loket' => $kodeLoket
            ]);
        }

        return redirect()->to('/operator/dashboard');
    }

public function selesai()
    {
        $session = session();
        $kodeJenis = $session->get('kode_jenis');
        $kodeLoket = $session->get('kode_loket');
        $userId = $session->get('user_id');

        $antrianDipanggil = $this->antrianModel
            ->where('kode_jenis', $kodeJenis)
            ->where('status', 'Dipanggil')
            ->where('kode_loket', $kodeLoket)
            ->first();

        if ($antrianDipanggil) {
            $this->antrianModel->update($antrianDipanggil['id_antrian'], [
                'status' => 'Selesai',
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
        }

        return redirect()->to('/operator/dashboard');
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

    public function logoutOperator()
    {
        $session = session();
        $session->remove(['kode_jenis', 'kode_loket', 'logged_in']);
        $session->destroy();

        return redirect()->to('/operator/operator_select');
    }
}

