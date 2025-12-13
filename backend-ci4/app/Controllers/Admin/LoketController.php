<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LoketModel;
use App\Models\JenisLoketModel;

class LoketController extends BaseController
{
    protected $loketModel;
    protected $jenisModel;

    public function __construct()
    {
        $this->loketModel = new LoketModel();
        $this->jenisModel = new JenisLoketModel();
    }

    public function index()
    {
        $data = [
            'title' => 'Manajemen Loket',
            'loket' => $this->loketModel->findAll()
        ];

        return view('admin/loket/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Loket',
            'jenis' => $this->jenisModel->findAll()
        ];
        return view('admin/loket/create', $data);
    }

    public function store()
    {
        $this->loketModel->insert([
            'kode_loket' => $this->request->getPost('kode_loket'),
            'nama_loket' => $this->request->getPost('nama_loket'),
            'kode_jenis' => $this->request->getPost('kode_jenis'),
        ]);

        return redirect()->to('/admin/loket')->with('success', 'Data berhasil ditambah');
    }

    public function edit($kode)
    {
        $data = [
            'title' => 'Edit Loket',
            'loket' => $this->loketModel->find($kode),
            'jenis' => $this->jenisModel->findAll()
        ];

        return view('admin/loket/edit', $data);
    }

    public function update($kode)
    {
        $this->loketModel->update($kode, [
            'nama_loket' => $this->request->getPost('nama_loket'),
            'kode_jenis' => $this->request->getPost('kode_jenis'),
        ]);

        return redirect()->to('/admin/loket')->with('success', 'Data berhasil diperbarui');
    }

    public function delete($kode)
    {
        $this->loketModel->delete($kode);
        return redirect()->to('/admin/loket')->with('success', 'Data berhasil dihapus');
    }
}
