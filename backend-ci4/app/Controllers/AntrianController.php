<?php

namespace App\Controllers;
use App\Models\JenisLoketModel;
use App\Models\LoketModel;
use App\Models\LogAntrianModel;
use App\Models\AntrianModel;
use CodeIgniter\RESTful\ResourceController;

class AntrianController extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $model = new AntrianModel();
        $data = $model->findAll();

        return $this->respond($data, 200);
    }

    public function menunggu()
    {
    $model = new AntrianModel();
    $data = $model->where('status', 'Menunggu')->findAll();
    return $this->respond($data, 200);
    }

    public function next()
    {
    $model = new \App\Models\AntrianModel();
    $logModel = new \App\Models\LogAntrianModel();

    $kodeJenis = $this->request->getPost('kode_jenis');
    $kodeLoket = $this->request->getPost('kode_loket');

    // Selesaikan antrian aktif di loket ini
    $antrianAktif = $model->where('status', 'Dipanggil')
                          ->where('kode_loket', $kodeLoket)
                          ->first();

    if ($antrianAktif) {
        $model->update($antrianAktif['id_antrian'], ['status' => 'Selesai']);
    }

    // Ambil antrian menunggu berikutnya untuk jenis ini
    $antrianSelanjutnya = $model->where('status', 'Menunggu')
                                ->where('kode_jenis', $kodeJenis)
                                ->orderBy('tanggal', 'ASC')
                                ->orderBy('nomor', 'ASC')
                                ->first();

    if (!$antrianSelanjutnya) {
        return $this->respond([
            'status' => 'error',
            'message' => 'Tidak ada antrian menunggu untuk jenis ini.'
        ], 404);
    }

    $model->update($antrianSelanjutnya['id_antrian'], [
        'status' => 'Dipanggil',
        'kode_loket' => $kodeLoket
    ]);

    $logModel->insert([
        'id_antrian' => $antrianSelanjutnya['id_antrian'],
        'aksi'       => 'next',
        'waktu'      => date('Y-m-d H:i:s'),
        'user_id'    => null
    ]);

    return $this->respond([
        'status' => 'success',
        'message' => 'Antrian berikutnya berhasil dipanggil.',
        'data' => $antrianSelanjutnya
    ], 200);
    }

    public function create()
{
    $antrianModel = new \App\Models\AntrianModel();
    $loketModel = new \App\Models\LoketModel();
    $jenisLoketModel = new \App\Models\JenisLoketModel();
    $logModel = new \App\Models\LogAntrianModel();

    $request = $this->request->getJSON(true);

    $kode_loket = $request['kode_loket'] ?? null;
    $kode_jenis = $request['kode_jenis'] ?? null;

    // --- PILIH MODE ---
    if ($kode_loket) {
        // ✅ Mode: langsung pakai kode loket
        $loket = $loketModel->where('kode_loket', $kode_loket)->first();
        if (!$loket) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Loket tidak ditemukan.'
            ], 404);
        }
    } elseif ($kode_jenis) {
        // ✅ Mode: pakai kode jenis
        $jenis = $jenisLoketModel->where('kode_jenis', $kode_jenis)->first();
        if (!$jenis) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Jenis loket tidak ditemukan.'
            ], 404);
        }

        // Ambil loket pertama dari jenis tersebut
        $loket = $loketModel->where('kode_jenis', $kode_jenis)->first();
        if (!$loket) {
            return $this->respond([
                'status' => 'error',
                'message' => 'Loket tidak ditemukan untuk jenis ini.'
            ], 404);
        }

        $kode_loket = $loket['kode_loket'];
    } else {
        return $this->respond([
            'status' => 'error',
            'message' => 'Kode loket atau kode jenis wajib diisi.'
        ], 400);
    }

    // --- Dapatkan nomor terakhir di loket tersebut ---
    $last = $antrianModel->where('kode_loket', $kode_loket)
                         ->orderBy('id_antrian', 'DESC')
                         ->first();
    $nextNomor = $last ? $last['nomor'] + 1 : 1;

    // --- Simpan data antrian ---
    $data = [
        'kode_loket' => $kode_loket,
        'nomor' => $nextNomor,
        'tanggal' => date('Y-m-d H:i:s'),
        'status' => 'Menunggu'
    ];
    $antrianModel->insert($data);

    $id_antrian = $antrianModel->getInsertID();

    // --- Simpan ke log_antrian ---
    $logModel->insert([
        'id_antrian' => $id_antrian,
        'aksi' => 'create',
        'waktu' => date('Y-m-d H:i:s'),
        'user_id' => null // nanti bisa diisi dari session/login admin/operator
    ]);

    return $this->respond([
        'status' => 'success',
        'message' => 'Antrian berhasil dibuat.',
        'data' => [
            'kode_loket' => $kode_loket,
            'nomor' => $nextNomor,
            'tanggal' => date('Y-m-d H:i:s'),
            'status' => 'Menunggu'
        ]
    ], 200);
}

    public function reset()
    {
    $model = new \App\Models\AntrianModel();
    $kodeJenis = $this->request->getPost('kode_jenis');

    $model->where('kode_jenis', $kodeJenis)
          ->set(['status' => 'Menunggu'])
          ->update();

    return $this->respond([
        'status' => 'success',
        'message' => 'Antrian berhasil direset untuk jenis ' . $kodeJenis
    ], 200);
    }

    public function panggil()
{
    $model = new \App\Models\AntrianModel();
    $loketModel = new \App\Models\LoketModel();
    $logModel = new \App\Models\LogAntrianModel();

    $kodeJenis = $this->request->getVar('kode_jenis');
    $kodeLoket = $this->request->getVar('kode_loket');

    // Validasi input
    if (!$kodeJenis || !$kodeLoket) {
        return $this->respond([
            'status' => 'error',
            'message' => 'kode_jenis dan kode_loket harus dikirim.'
        ], 400);
    }

    // Pastikan loket sesuai jenis ada di database
    $loket = $loketModel->where('kode_loket', $kodeLoket)
                        ->where('kode_jenis', $kodeJenis)
                        ->first();

    if (!$loket) {
        return $this->respond([
            'status' => 'error',
            'message' => 'Loket tidak ditemukan untuk jenis ini.'
        ], 404);
    }

    // Ambil antrian menunggu paling awal (tanpa filter kode_jenis karena tidak ada di tabel antrian)
    $antrian = $model->where('status', 'Menunggu')
                     ->orderBy('tanggal', 'ASC')
                     ->orderBy('nomor', 'ASC')
                     ->first();

    if (!$antrian) {
        return $this->respond([
            'status' => 'error',
            'message' => 'Tidak ada antrian yang menunggu.'
        ], 404);
    }

    // Update status dan isi kode_loket
    $model->update($antrian['id_antrian'], [
        'status' => 'Dipanggil',
        'kode_loket' => $kodeLoket
    ]);

    // Simpan ke log
    $logModel->insert([
        'id_antrian' => $antrian['id_antrian'],
        'aksi'       => 'panggil',
        'waktu'      => date('Y-m-d H:i:s'),
        'user_id'    => null
    ]);

    // Kirim hasil sukses
    return $this->respond([
        'status' => 'success',
        'message' => 'Antrian berhasil dipanggil.',
        'data' => [
            'id_antrian' => $antrian['id_antrian'],
            'nomor' => $antrian['nomor'],
            'kode_loket' => $kodeLoket,
            'status' => 'Dipanggil'
        ]
    ], 200);
}
    public function panggilUlang()
{
    $model = new \App\Models\AntrianModel();
    $logModel = new \App\Models\LogAntrianModel();

    $idAntrian = $this->request->getVar('id_antrian');

    if (!$idAntrian) {
        return $this->respond([
            'status' => 'error',
            'message' => 'ID antrian wajib dikirim.'
        ], 400);
    }

    $antrian = $model->find($idAntrian);

    if (!$antrian) {
        return $this->respond([
            'status' => 'error',
            'message' => 'Antrian tidak ditemukan.'
        ], 404);
    }

    if ($antrian['status'] !== 'Dipanggil') {
        return $this->respond([
            'status' => 'error',
            'message' => 'Hanya antrian dengan status Dipanggil yang bisa dipanggil ulang.'
        ], 400);
    }

    // Catat ke log
    $logModel->insert([
        'id_antrian' => $idAntrian,
        'aksi'       => 'panggil_ulang',
        'waktu'      => date('Y-m-d H:i:s'),
        'user_id'    => $this->request->getVar('user_id') ?? null
    ]);

    return $this->respond([
        'status' => 'success',
        'message' => 'Antrian berhasil dipanggil ulang.',
        'data' => [
            'id_antrian' => $antrian['id_antrian'],
            'nomor' => $antrian['nomor'],
            'kode_loket' => $antrian['kode_loket']
        ]
    ], 200);
}
    
    public function selesai()
{
    $model = new \App\Models\AntrianModel();
    $logModel = new \App\Models\LogAntrianModel();

    $idAntrian = $this->request->getVar('id_antrian');

    if (!$idAntrian) {
        return $this->respond([
            'status' => 'error',
            'message' => 'ID antrian wajib dikirim.'
        ], 400);
    }

    $antrian = $model->find($idAntrian);

    if (!$antrian) {
        return $this->respond([
            'status' => 'error',
            'message' => 'Antrian tidak ditemukan.'
        ], 404);
    }

    // Ubah status jadi selesai
    $model->update($idAntrian, ['status' => 'Selesai']);

    // Simpan ke log
    $logModel->insert([
        'id_antrian' => $idAntrian,
        'aksi'       => 'selesai',
        'waktu'      => date('Y-m-d H:i:s'),
        'user_id'    => $this->request->getVar('user_id') ?? null
    ]);

    return $this->respond([
        'status' => 'success',
        'message' => 'Antrian telah selesai.',
        'data' => [
            'id_antrian' => $idAntrian,
            'status' => 'Selesai'
        ]
    ], 200);
}
}

