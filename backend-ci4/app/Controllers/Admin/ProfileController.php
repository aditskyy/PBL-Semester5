<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProfileModel;

class ProfileController extends BaseController
{
    protected $profileModel;

    public function __construct()
    {
        $this->profileModel = new ProfileModel();
    }

    // INDEX
    public function index()
    {
        $profile = $this->profileModel->first();

        // Kalau belum ada profile → redirect ke create
        if (!$profile) {
            return redirect()->to('admin/profile/create');
        }

        return view('admin/profile/index', [
            'profile' => $profile
        ]);
    }

    // CREATE FORM
    public function create()
    {
        // Kalau sudah ada profile → gak boleh create lagi
        if ($this->profileModel->countAll() > 0) {
            return redirect()->to('admin/profile');
        }

        return view('admin/profile/create');
    }

    // STORE
    public function store()
    {
        $data = [
            'nama_instansi' => $this->request->getPost('nama_instansi'),
            'alamat'        => $this->request->getPost('alamat'),
            'telp'          => $this->request->getPost('telp'),
            'color_palette' => $this->request->getPost('color_palette'),
        ];

        $file = $this->request->getFile('gambar_logo');
        if ($file && $file->isValid()) {
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/logo', $newName);
            $data['gambar_logo'] = $newName;
        }

        $this->profileModel->insert($data);

        return redirect()->to('admin/profile')->with('success', 'Profile berhasil dibuat');
    }

    // EDIT FORM
    public function edit($id)
    {
        $profile = $this->profileModel->find($id);

        if (!$profile) {
            return redirect()->to('admin/profile');
        }

        return view('admin/profile/edit', [
            'profile' => $profile
        ]);
    }

    // UPDATE
    public function update($id)
    {
        $data = [
            'nama_instansi' => $this->request->getPost('nama_instansi'),
            'alamat'        => $this->request->getPost('alamat'),
            'telp'          => $this->request->getPost('telp'),
            'color_palette' => $this->request->getPost('color_palette'),
        ];

        $file = $this->request->getFile('gambar_logo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/logo', $newName);
            $data['gambar_logo'] = $newName;
        }

        $this->profileModel->update($id, $data);

        return redirect()->to('admin/profile')->with('success', 'Profile berhasil diperbarui');
    }
}
