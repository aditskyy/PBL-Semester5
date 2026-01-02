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
     * 🟢 Panggil Antrian Selanjutnya (Auto Next)
     * PERUBAHAN: Ditambah Log SELESAI untuk antrian lama & Log PANGGIL untuk antrian baru
     */
    public function panggilSelanjutnya()
    {
        $kodeJenis = $this->request->getPost('kode_jenis');
        $kodeLoket = $this->request->getPost('kode_loket');
        $userId = session()->get('user_id');

        if (!$userId) return $this->failUnauthorized('Sesi habis.');

        // 1. Cari dan Selesaikan antrian yang sedang 'Dipanggil' di loket ini saja
        $antrianLama = $this->antrianModel
            ->where('kode_loket', $kodeLoket)
            ->where('status', 'Dipanggil')
            ->first();

        if ($antrianLama) {
            $this->antrianModel->update($antrianLama['id_antrian'], ['status' => 'Selesai']);
            
            // 📝 LOG: Antrian lama selesai
            $this->logModel->insert([
                'id_antrian' => $antrianLama['id_antrian'],
                'user_id'    => $userId,
                'aksi'       => 'SELESAI',
                'waktu'      => date('Y-m-d H:i:s')
            ]);
        }

        // 2. Ambil antrian berikutnya
        $antrian = $this->antrianModel
            ->where('kode_jenis', $kodeJenis)
            ->where('status', 'Menunggu')
            ->orderBy('id_antrian', 'ASC')
            ->first();

        if (!$antrian) {
            return $this->fail('Tidak ada antrian berikutnya.', 404);
        }

        // 3. Update status antrian baru
        $this->antrianModel->update($antrian['id_antrian'], [
            'status'     => 'Dipanggil',
            'kode_loket' => $kodeLoket
        ]);

        // 📝 LOG: Antrian baru dipanggil
        $this->logModel->insert([
            'id_antrian' => $antrian['id_antrian'],
            'user_id'    => $userId,
            'aksi'       => 'PANGGIL',
            'waktu'      => date('Y-m-d H:i:s')
        ]);

        $loket = $this->loketModel->where('kode_loket', $kodeLoket)->first();

        $this->emitSocket('panggil_antrean', [
            'nomor'      => $antrian['kode_jenis'] . str_pad($antrian['nomor'], 3, '0', STR_PAD_LEFT),
            'kode_loket' => $kodeLoket,
            'warna'      => $loket['warna'] ?? '#1E88E5'
        ]);

        return $this->respond(['status' => 'success', 'data' => $antrian]);
    }

    /**
     * 🟡 Panggil Ulang Antrian (Recall)
     * PERUBAHAN: Ditambah Log RECALL
     */
    public function panggilUlang()
    {
        $idAntrian = $this->request->getPost('id_antrian');
        $kodeLoket = $this->request->getPost('kode_loket'); // Ambil dari POST tab
        $userId = session()->get('user_id');

        $antrian = $this->antrianModel->find($idAntrian);
        if (!$antrian) return $this->failNotFound('Antrian tidak ditemukan.');

        // 📝 LOG: Catat aksi RECALL
        $this->logModel->insert([
            'id_antrian' => $idAntrian,
            'user_id'    => $userId,
            'aksi'       => 'RECALL',
            'waktu'      => date('Y-m-d H:i:s')
        ]);

        $loket = $this->loketModel->where('kode_loket', $kodeLoket)->first();

        $this->emitSocket('panggil_ulang', [
            'nomor'      => $antrian['kode_jenis'] . str_pad($antrian['nomor'], 3, '0', STR_PAD_LEFT),
            'kode_loket' => $kodeLoket,
            'warna'      => $loket['warna'] ?? '#1E88E5'
        ]);

        return $this->respond(['status' => 'success', 'message' => 'Panggilan ulang dikirim.']);
    }

    /**
     * 🔴 Selesaikan Antrian
     * PERUBAHAN: Ditambah Log SELESAI
     */
    public function selesai()
    {
        $idAntrian = $this->request->getPost('id_antrian');
        $kodeLoket = $this->request->getPost('kode_loket');
        $userId = session()->get('user_id');

        if (!$idAntrian) return $this->fail('ID antrian tidak ditemukan', 400);

        $this->antrianModel->update($idAntrian, [
            'status' => 'Selesai',
            'waktu_selesai' => date('Y-m-d H:i:s')
        ]);

        // 📝 LOG: Catat aksi SELESAI
        $this->logModel->insert([
            'id_antrian' => $idAntrian,
            'user_id'    => $userId,
            'aksi'       => 'SELESAI',
            'waktu'      => date('Y-m-d H:i:s')
        ]);

        $antrian = $this->antrianModel->find($idAntrian);
        
        $this->emitSocket('selesai_antrean', [
            'nomor'      => $antrian['kode_jenis'] . str_pad($antrian['nomor'], 3, '0', STR_PAD_LEFT),
            'kode_loket' => $kodeLoket
        ]);

        return $this->respond(['status' => 'success', 'message' => 'Antrian selesai.']);
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
            'message' => 'Tidak ada data antriaAn yang perlu direset.'
        ]);
    }
}
