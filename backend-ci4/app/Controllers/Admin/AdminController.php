<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\JenisLoketModel;
use App\Models\LoketModel;
use App\Models\UserModel;
use App\Models\AntrianModel;

class AdminController extends BaseController
{
    public function __construct()
    {
        helper(['url', 'form']);
    }

    // ================================
    // DASHBOARD ADMIN
    // ================================
    public function dashboard()
    {
        $session = session();
        if ($session->get('role') !== 'admin') {
            return redirect()->to('/login')->with('error', 'Akses ditolak!');
        }

        $jenisModel   = new JenisLoketModel();
        $loketModel   = new LoketModel();
        $userModel    = new UserModel();
        $antrianModel = new AntrianModel();

        // Summary
        $data['countJenis']    = $jenisModel->countAllResults();
        $data['countLoket']    = $loketModel->countAllResults();
        $data['countOperator'] = $userModel->where('role', 'operator')->countAllResults();

        $loketList = $loketModel->findAll();
        $today = date('Y-m-d'); // hari ini

        foreach ($loketList as &$l) {

            // Total antrian hari ini
            $l['total_antrian'] = $antrianModel
                ->where('kode_loket', $l['kode_loket'])
                ->where('tanggal >=', $today . ' 00:00:00')
                ->where('tanggal <=', $today . ' 23:59:59')
                ->countAllResults();

            // Nomor terakhir hari ini
            $last = $antrianModel
                ->where('kode_loket', $l['kode_loket'])
                ->where('tanggal >=', $today . ' 00:00:00')
                ->where('tanggal <=', $today . ' 23:59:59')
                ->orderBy('id_antrian', 'DESC')
                ->first();

            $l['last_nomor'] = $last['nomor'] ?? '-';

            // Status loket
            if (isset($l['status'])) {
                $l['status'] = strtolower($l['status']);
            } elseif (isset($l['aktif'])) {
                $l['status'] = ($l['aktif'] == 1) ? 'buka' : 'tutup';
            } else {
                $l['status'] = 'tutup';
            }
        }
        unset($l);

        $data['loketList'] = $loketList;

        // ============================================
        // GRAFIK 1: Total Antrian 7 Hari Terakhir
        // ============================================
        $chartDates = [];
        $chartTotals = [];

        for ($i = 6; $i >= 0; $i--) {
            $day = date('Y-m-d', strtotime("-{$i} days"));

            // Label tanggal e.g. "12 Jan"
            $chartDates[] = date('d M', strtotime($day));

            // Hitung antrian hari tersebut
            $total = $antrianModel
                ->where('tanggal >=', $day . ' 00:00:00')
                ->where('tanggal <=', $day . ' 23:59:59')
                ->countAllResults();

            $chartTotals[] = $total;
        }

        $data['chartDates']  = json_encode($chartDates);
        $data['chartTotals'] = json_encode($chartTotals);

        // ============================================
        // GRAFIK 2: Total Antrian per Loket Hari Ini
        // ============================================
        $loketNames = [];
        $loketTotals = [];

        foreach ($loketList as $lk) {
            $loketNames[] = $lk['nama_loket'];

            // Total per loket hari ini
            $loketTotals[] = $antrianModel
                ->where('kode_loket', $lk['kode_loket'])
                ->where('tanggal >=', $today . ' 00:00:00')
                ->where('tanggal <=', $today . ' 23:59:59')
                ->countAllResults();
        }

        $data['loketNames']  = json_encode($loketNames);
        $data['loketTotals'] = json_encode($loketTotals);

        return view('admin/Dashboard', $data);
    }


    // ================================
    // LOGOUT
    // ================================
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login');
    }
}

