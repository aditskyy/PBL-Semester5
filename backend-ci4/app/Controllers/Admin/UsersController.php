<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class UsersController extends BaseController
{
    protected $userModel;
    protected $validation;

    public function __construct()
    {
        $this->userModel   = new UserModel();
        $this->validation  = \Config\Services::validation();
    }

    public function index()
    {
        $data = [
            'title' => 'Manajemen User',
            'users' => $this->userModel->findAll()
        ];

        return view('admin/user/index', $data);
    }

    public function create()
    {
        return view('admin/user/create', ['title' => 'Tambah User']);
    }

    public function store()
    {
        // rules
        $rules = [
            'username' => 'required|min_length[3]|is_unique[users.username]',
            'password' => 'required|min_length[4]',
            'role'     => 'required'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // hash password
        $hashed = password_hash($this->request->getPost('password'), PASSWORD_BCRYPT);

        $this->userModel->save([
            'username' => $this->request->getPost('username'),
            'password' => $hashed,
            'role'     => $this->request->getPost('role')
        ]);

        return redirect()->to(base_url('admin/users'))->with('success', 'User berhasil ditambahkan');
    }

    public function edit($id)
    {
        $user = $this->userModel->find($id);
        if (! $user) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('User tidak ditemukan');
        }

        return view('admin/user/edit', [
            'title' => 'Edit User',
            'user'  => $user
        ]);
    }

    public function update($id)
    {
        // basic rules (username must be required and unique except for current user)
        $rules = [
            'username' => "required|min_length[3]|is_unique[users.username,id,{$id}]",
            'role'     => 'required'
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'role'     => $this->request->getPost('role')
        ];

        // jika password diisi, hash dan update; kalau kosong, biarkan password lama
        $pw = $this->request->getPost('password');
        if (!empty($pw)) {
            if (strlen($pw) < 4) {
                return redirect()->back()->withInput()->with('errors', ['password' => 'Password minimal 4 karakter jika ingin mengganti.']);
            }
            $data['password'] = password_hash($pw, PASSWORD_BCRYPT);
        }

        $this->userModel->update($id, $data);

        return redirect()->to(base_url('admin/users'))->with('success', 'User berhasil diperbarui');
    }

    public function delete($id)
    {
        // opsional: jangan izinkan delete admin pertama (id=1) — sesuaikan kebutuhan
        $this->userModel->delete($id);
        return redirect()->to(base_url('admin/users'))->with('success', 'User berhasil dihapus');
    }
}
