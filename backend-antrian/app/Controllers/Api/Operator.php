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

    // 🔹 Panggil Antrian Baru
    public function panggil()
    {
        $kodeJenis = $this->request->getJSON()->kode_jenis ?? $this->request->getPost('kode_jenis');
        $kodeLoket = $this->request->getJSON()->kode_loket ?? $this->request->getPost('kode_loket');

        $antrian = $this->antrianModel
            ->where('kode_jenis', $kodeJenis)
            ->where('status', 'Menunggu')
            ->orderBy('tanggal', 'ASC')
            ->orderBy('nomor', 'ASC')
            ->first();

        if (!$antrian) {
            return $this->respond(['status' => 'error', 'message' => 'Tidak ada antrian menunggu.'], 404);
        }

        $this->antrianModel->update($antrian['id_antrian'], [
            'status' => 'Dipanggil',
            'kode_loket' => $kodeLoket
        ]);

        $this->logModel->insert([
            'id_antrian' => $antrian['id_antrian'],
            'aksi' => 'panggil',
            'waktu' => date('Y-m-d H:i:s')
        ]);

        return $this->respond([
            'status' => 'success',
            'message' => 'Antrian berhasil dipanggil.',
            'data' => [
                'id_antrian' => $antrian['id_antrian'],
                'nomor' => $antrian['nomor'],
                'kode_loket' => $kodeLoket,
                'status' => 'Dipanggil'
            ]
        ]);
    }

    // 🔹 Panggil ulang antrian aktif
    public function panggilUlang()
    {
        $idAntrian = $this->request->getJSON()->id_antrian ?? null;

        $antrian = $this->antrianModel->find($idAntrian);
        if (!$antrian) {
            return $this->respond(['status' => 'error', 'message' => 'Data antrian tidak ditemukan.'], 404);
        }

        $this->logModel->insert([
            'id_antrian' => $idAntrian,
            'aksi' => 'panggil ulang',
            'waktu' => date('Y-m-d H:i:s')
        ]);

        return $this->respond(['status' => 'success', 'message' => 'Antrian dipanggil ulang.']);
    }

    // 🔹 Selesaikan antrian
    public function selesaikan()
    {
        $idAntrian = $this->request->getJSON()->id_antrian ?? null;

        $this->antrianModel->update($idAntrian, ['status' => 'Selesai']);
        $this->logModel->insert([
            'id_antrian' => $idAntrian,
            'aksi' => 'selesai',
            'waktu' => date('Y-m-d H:i:s')
        ]);

        return $this->respond(['status' => 'success', 'message' => 'Antrian telah diselesaikan.']);
    }

    // 🔹 Lewati antrian
    public function lewati()
    {
        $idAntrian = $this->request->getJSON()->id_antrian ?? null;

        $this->antrianModel->update($idAntrian, ['status' => 'Lewati']);
        $this->logModel->insert([
            'id_antrian' => $idAntrian,
            'aksi' => 'lewati',
            'waktu' => date('Y-m-d H:i:s')
        ]);

        return $this->respond(['status' => 'success', 'message' => 'Antrian telah dilewati.']);
    }

    // 🔹 Tampilkan status loket aktif
    public function loketAktif()
    {
        $loket = $this->loketModel
            ->select('loket.kode_loket, loket.nama_loket, antrian.id_antrian, antrian.nomor, antrian.status')
            ->join('antrian', 'antrian.kode_loket = loket.kode_loket AND antrian.status = "Dipanggil"', 'left')
            ->orderBy('loket.kode_loket', 'ASC')
            ->findAll();

        return $this->respond($loket);
    }
}
