<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\JenisLoketModel;

class JenisLoketController extends BaseController
{
    protected $jenisLoketModel;

    public function __construct()
    {
        $this->jenisLoketModel = new JenisLoketModel();
    }

    public function Index()
    {
        $data = [
            'title' => 'Manajemen Jenis Loket',
            'jenisLoket' => $this->jenisLoketModel->findAll()
        ];

        return view('admin/JenisLoket/Index', $data);
    }

    public function create()
    {
        return view('admin/JenisLoket/Create');
    }

    public function store()
{
    $data = [
        'kode_jenis' => $this->request->getPost('kode_jenis'),
        'nama_jenis' => $this->request->getPost('nama_jenis'),
        'keterangan' => $this->request->getPost('keterangan'),
    ];

    $this->jenisLoketModel->insert($data);

    return redirect()
        ->to('/admin/jenisLoket')
        ->with('success', 'Data berhasil ditambahkan');
}

   public function edit($kode_jenis)
{
    $jenis = $this->jenisLoketModel->find($kode_jenis);

    if (!$jenis) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Data tidak ditemukan");
    }

    $data = [
        'title' => 'Edit Jenis Loket',
        'jenis' => $jenis
    ];

    return view('admin/jenisLoket/Edit', $data);
}

public function update($kode_jenis)
{
    $this->jenisLoketModel->update($kode_jenis, [
        'nama_jenis' => $this->request->getPost('nama_jenis'),
        'keterangan' => $this->request->getPost('keterangan')
    ]);

    return redirect()->to('/admin/jenisLoket')->with('success', 'Data berhasil diperbarui');
}

    public function delete($kode)
    {
        $this->jenisLoketModel->delete($kode);
        return redirect()->to('/admin/jenisLoket')->with('success', 'Data berhasil dihapus');
    }
}
