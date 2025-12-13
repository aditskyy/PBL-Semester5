<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\AntrianModel;
use App\Models\LoketModel;
use App\Models\LogAntrianModel;

class Operator extends ResourceController
{
    protected $antrianModel;
    protected $loketModel;
    protected $logModel;

    public function __construct()
    {
        $this->antrianModel = new AntrianModel();
        $this->loketModel = new LoketModel();
        $this->logModel = new LogAntrianModel();
    }

    /**
     * 🟢 Panggil antrian pertama kali
     */
     public function panggil()
{
    $antrianModel = new AntrianModel();
    $idAntrian = $this->request->getPost('id_antrian');
    $userId = session()->get('user_id'); // Ambil dari session

    if (!$idAntrian || !$userId) {
        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'ID antrian atau user tidak ditemukan.'
        ])->setStatusCode(400);
    }

    $antrianModel->update($idAntrian, [
        'status' => 'Dipanggil',
        'aksi' => 'PANGGIL',
        'user_id' => $userId, // ⬅ SIMPAN USER ID
        'waktu_panggil' => date('Y-m-d H:i:s')
    ]);

    return $this->response->setJSON([
        'status' => 'success',
        'message' => 'Antrian berhasil dipanggil.'
    ]);
}

    /**
     * 🟢 Panggil Antrian Selanjutnya
     */
    public function panggilSelanjutnya()
    {
        $kodeJenis = $this->request->getPost('kode_jenis');
        $kodeLoket = $this->request->getPost('kode_loket');
        $userId = session()->get('user_id');

        // Tandai antrian yang sedang dipanggil jadi "Selesai"
        $this->antrianModel
            ->where('kode_loket', $kodeLoket)
            ->where('status', 'Dipanggil')
            ->set(['status' => 'Selesai'])
            ->update();

        // Ambil antrian berikutnya
        $antrian = $this->antrianModel
            ->where('kode_jenis', $kodeJenis)
            ->where('status', 'Menunggu')
            ->orderBy('id_antrian', 'ASC')
            ->first();

        if (!$antrian) {
            return $this->respond(['status' => 'error', 'message' => 'Tidak ada antrian berikutnya.']);
        }

        // Update ke "Dipanggil"
        $this->antrianModel->update($antrian['id_antrian'], [
            'status' => 'Dipanggil',
            'kode_loket' => $kodeLoket
        ]);

        // Simpan log
        $this->logModel->insert([
    'id_antrian' => $antrian['id_antrian'],
    'aksi' => 'PANGGIL',
    'user_id' => $userId,
    'waktu' => date('Y-m-d H:i:s')
]);

        return $this->respond([
            'status' => 'success',
            'message' => 'Antrian berikutnya dipanggil.',
            'data' => $antrian
        ]);
    }

    /**
     * 🟡 Panggil Ulang Antrian
     */
    public function panggilUlang()
    {
        $idAntrian = $this->request->getPost('id_antrian');
        $antrian = $this->antrianModel->find($idAntrian);

        if (!$antrian) {
            return $this->respond(['status' => 'error', 'message' => 'Data antrian tidak ditemukan.']);
        }

        // Log panggil ulang
        $this->logModel->insert([
            'id_antrian' => $idAntrian,
            'aksi' => 'Panggil Ulang',
            'waktu' => date('Y-m-d H:i:s')
        ]);

        return $this->respond([
            'status' => 'success',
            'message' => 'Antrian dipanggil ulang.',
            'data' => $antrian
        ]);
    }

    /**
     * 🔴 Selesaikan Antrian
     */
      public function selesai()
{
    $idAntrian = $this->request->getPost('id_antrian');
    $userId = session()->get('user_id');

    if (!$idAntrian) {
        return $this->respond(['status' => 'error', 'message' => 'ID antrian tidak ditemukan'], 400);
    }

    $antrian = $this->antrianModel->find($idAntrian);
    if (!$antrian) {
        return $this->respond(['status' => 'error', 'message' => 'Data antrian tidak valid'], 404);
    }

    // Update ke selesai
    $this->antrianModel->update($idAntrian, [
        'status' => 'Selesai',
        'waktu_selesai' => date('Y-m-d H:i:s')
    ]);

    // Tambahkan LOG
    $this->logModel->insert([
        'id_antrian' => $idAntrian,
        'user_id'    => $userId,
        'aksi'       => 'SELESAI',
        'waktu'      => date('Y-m-d H:i:s')
    ]);

    return $this->respond([
        'status' => 'success',
        'message' => 'Antrian berhasil diselesaikan.'
    ]);
}


        public function resetAntrian()
    {
        $antrianModel = new AntrianModel();

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
}
