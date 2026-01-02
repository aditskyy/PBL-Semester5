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

    public function index()
    {
        $profile = $this->profileModel->first();

        if (!$profile) {
            return redirect()->to('admin/profile/create');
        }

        return view('admin/profile/index', [
            'profile' => $profile
        ]);
    }

    public function create()
    {
        if ($this->profileModel->countAll() > 0) {
            return redirect()->to('admin/profile');
        }
        return view('admin/profile/create');
    }

    public function store()
    {
        $data = [
            'nama_instansi' => $this->request->getPost('nama_instansi'),
            'alamat'        => $this->request->getPost('alamat'),
            'telp'          => $this->request->getPost('telp'),
            'color_palette' => $this->request->getPost('color_palette'),
        ];

        $this->profileModel->insert($data);
        return redirect()->to('admin/profile')->with('success', 'Profile berhasil dibuat');
    }

    public function edit($id)
    {
        $profile = $this->profileModel->find($id);
        if (!$profile) {
            return redirect()->to('admin/profile');
        }
        return view('admin/profile/edit', ['profile' => $profile]);
    }

    public function update($id)
    {
        $data = [
            'nama_instansi' => $this->request->getPost('nama_instansi'),
            'alamat'        => $this->request->getPost('alamat'),
            'telp'          => $this->request->getPost('telp'),
            'color_palette' => $this->request->getPost('color_palette'),
        ];

        $this->profileModel->update($id, $data);
        return redirect()->to('admin/profile')->with('success', 'Profile berhasil diperbarui');
    }
}