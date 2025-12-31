<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ProfileModel;

class Profile extends ResourceController
{
    public function index()
    {
        $model = new ProfileModel();
        $profile = $model->first();

        if ($profile) {
            // --- PERUBAHAN DI SINI ---
            // Kita tidak perlu base_url('uploads/logo/...') lagi.
            // Biarkan data 'gambar_logo' mengirim nama filenya saja (atau abaikan).
            // Data ini tetap dikirim tapi tidak akan dipakai Flutter untuk render gambar.
        } else {
            $profile = [
                'nama_instansi' => 'PUJASERA DEFAULT',
                'alamat'        => 'Alamat Belum Diatur',
                'telp'          => '-',
                'color_palette' => '#1E88E5',
            ];
        }

        return $this->respond($profile);
    }
}