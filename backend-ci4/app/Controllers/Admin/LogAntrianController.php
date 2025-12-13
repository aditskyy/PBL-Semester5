<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LogAntrianModel;
use App\Models\UserModel;

class LogAntrianController extends BaseController
{
    protected $logModel;
    protected $userModel;

    public function __construct()
    {
        $this->logModel = new LogAntrianModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        return $this->showData();
    }

    public function filter()
    {
        return $this->showData(true);
    }

    private function showData($filtered = false)
    {
        $tanggal = $this->request->getPost('tanggal');
        $aksi = $this->request->getPost('aksi');
        $user_id = $this->request->getPost('user_id');

        $builder = $this->logModel
            ->select('log_antrian.*, users.username')
            ->join('users', 'users.id = log_antrian.user_id', 'left');

        // apply filter jika tombol filter dipakai
        if ($filtered) {
            if ($tanggal) $builder->where('DATE(waktu)', $tanggal);
            if ($aksi) $builder->where('aksi', $aksi);
            if ($user_id) $builder->where('log_antrian.user_id', $user_id);
        }

        // PAGINATE + menyimpan query filter
        $logs = $builder->orderBy('waktu', 'DESC')->paginate(50);
        $pager = $builder->pager;

        // kalau paginate klik halaman, filter tetap jalan
        $pager->setPath('admin/log-antrian?' . http_build_query([
            'tanggal' => $tanggal,
            'aksi' => $aksi,
            'user_id' => $user_id
        ]));

        $data = [
            'logs'     => $logs,
            'pager'    => $pager,
            'users'    => $this->userModel->findAll(),
            'tanggal'  => $tanggal,
            'aksi'     => $aksi,
            'user_id'  => $user_id
        ];

        return view('admin/LogAntrian/Index', $data);
    }

    public function reset()
{
    $this->logModel->truncate();
    session()->setFlashdata('success', 'Semua data log berhasil dihapus.');
    return redirect()->to(base_url('admin/log-antrian'));
 }
}
