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
        $rules = [
        'kode_loket' => 'required|is_unique[loket.kode_loket]',
        'nama_loket' => 'required',
        'kode_jenis' => 'required'
    ];

    if (!$this->validate($rules)) {
        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }
        // Tambahkan validasi sederhana agar kode_loket tidak duplikat
        $this->loketModel->insert([
            'kode_loket' => $this->request->getPost('kode_loket'),
            'nama_loket' => $this->request->getPost('nama_loket'),
            'kode_jenis' => $this->request->getPost('kode_jenis'),
            'warna'      => $this->request->getPost('warna'), // Tambahkan ini
            'icon'       => $this->request->getPost('icon'),  // Tambahkan ini
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
        // Pastikan kolom warna dan icon ikut diperbarui
        $this->loketModel->update($kode, [
            'nama_loket' => $this->request->getPost('nama_loket'),
            'kode_jenis' => $this->request->getPost('kode_jenis'),
            'warna'      => $this->request->getPost('warna'), // Tambahkan ini
            'icon'       => $this->request->getPost('icon'),  // Tambahkan ini
        ]);

        return redirect()->to('/admin/loket')->with('success', 'Data berhasil diperbarui');
    }
    public function delete($kode)
    {
        $this->loketModel->delete($kode);
        return redirect()->to('/admin/loket')->with('success', 'Data berhasil dihapus');
    }
}
