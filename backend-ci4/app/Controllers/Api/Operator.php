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
     * 🛰️ Helper untuk mengirim data ke WebSocket Server
     */
    private function emitSocket($event, $data)
{
    $client = \Config\Services::curlrequest();
    try {
        $client->post('http://localhost:5000/api/emit', [
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

    /**
     * 🟢 Panggil antrian pertama kali (dari list antrian)
     */
    public function panggil()
{
    $idAntrian = $this->request->getPost('id_antrian');
    $kodeLoket = $this->request->getPost('kode_loket');
    $userId = session()->get('user_id');

    if (!$idAntrian || !$userId) {
        return $this->fail('ID antrian atau user tidak ditemukan.', 400);
    }

    $antrian = $this->antrianModel->find($idAntrian);

    $this->antrianModel->update($idAntrian, [
        'status'        => 'Dipanggil',
        'kode_loket'    => $kodeLoket,
        'user_id'       => $userId,
        'waktu_panggil' => date('Y-m-d H:i:s')
    ]);

    // 🔥 SOCKET
    $this->emitSocket('panggil_antrean', [
        'nomor'      => $antrian['nomor'],
        'kode_loket' => $kodeLoket,
        'kode_jenis' => $antrian['kode_jenis']
    ]);

    return $this->respond(['status' => 'success', 'message' => 'Antrian dipanggil.']);
}

    /**
     * 🟢 Panggil Antrian Selanjutnya (Auto Next)
     */
    public function panggilSelanjutnya()
{
    $kodeJenis = $this->request->getPost('kode_jenis');
    $kodeLoket = $this->request->getPost('kode_loket');
    $userId = session()->get('user_id');

    // Selesaikan yang lama
    $this->antrianModel
        ->where('kode_loket', $kodeLoket)
        ->where('status', 'Dipanggil')
        ->set(['status' => 'Selesai'])
        ->update();

    // Ambil berikutnya
    $antrian = $this->antrianModel
        ->where('kode_jenis', $kodeJenis)
        ->where('status', 'Menunggu')
        ->orderBy('id_antrian', 'ASC')
        ->first();

    if (!$antrian) {
        return $this->fail('Tidak ada antrian berikutnya.', 404);
    }

    $this->antrianModel->update($antrian['id_antrian'], [
        'status'     => 'Dipanggil',
        'kode_loket' => $kodeLoket
    ]);

    // 🔥 SOCKET (FIELD VALID)
    $this->emitSocket('panggil_antrean', [
        'nomor'      => $antrian['nomor'],
        'kode_loket' => $kodeLoket,
        'kode_jenis' => $kodeJenis
    ]);

    return $this->respond(['status' => 'success', 'data' => $antrian]);
}

    /**
     * 🟡 Panggil Ulang Antrian (Recall)
     */
    public function panggilUlang()
{
    $idAntrian = $this->request->getPost('id_antrian');
    $antrian = $this->antrianModel->find($idAntrian);

    if (!$antrian) {
        return $this->failNotFound('Antrian tidak ditemukan.');
    }

    // 🔥 SOCKET 
    $this->emitSocket('panggil_ulang', [
        'nomor'      => $antrian['nomor'],
        'kode_loket' => $antrian['kode_loket']
    ]);

    return $this->respond([
        'status'  => 'success',
        'message' => 'Panggilan ulang dikirim.'
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

    // 🔥 SOCKET 
    $this->emitSocket('selesai_antrean', [
    'nomor' => $antrian['nomor']
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
