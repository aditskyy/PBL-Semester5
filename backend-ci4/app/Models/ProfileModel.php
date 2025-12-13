<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfileModel extends Model
{
    protected $table = 'profile';
    protected $primaryKey = 'id';
    protected $allowedFields = ['gambar_logo', 'color_palette', 'nama_instansi', 'alamat', 'tipe_instansi'];
}
