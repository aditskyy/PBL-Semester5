<?php

namespace App\Models;

use CodeIgniter\Model;

class JenisLoketModel extends Model
{
    protected $table = 'jenis_loket';
    protected $primaryKey = 'kode_jenis';
    protected $allowedFields = ['kode_jenis', 'nama_jenis', 'prefix_nomor', 'keterangan'];
}
