<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AntrianModel;

class AntrianController extends BaseController
{
    protected $antrianModel;

    public function __construct()
    {
        $this->antrianModel = new AntrianModel();
    }

    // ============================
    // 📋 TAMPILKAN SEMUA ANTRIAN
    // ============================
    public function index()
    {
        $data['antrian'] = $this->antrianModel
            ->orderBy('id_antrian', 'DESC')
            ->findAll();

        return view('admin/antrian/index', $data);
    }

    // ============================
    // ❌ HAPUS ANTRIAN
    // ============================
    public function delete($id)
    {
        // cek dulu apakah datanya ada
        $cek = $this->antrianModel->find($id);

        if (!$cek) {
            return redirect()->to(base_url('admin/antrian'))
                ->with('error', 'Data antrian tidak ditemukan.');
        }

        // soft delete otomatis karena model pakai useSoftDeletes = true
        $this->antrianModel->delete($id);

        return redirect()->to(base_url('admin/antrian'))
            ->with('success', 'Data antrian berhasil dihapus.');
    }
}
