<?php

namespace App\Models;

use CodeIgniter\Model;

class AntrianModel extends Model
{
    protected $table = 'antrian';
    protected $primaryKey = 'id_antrian';
    protected $allowedFields = [
    'kode_jenis',
    'kode_loket',
    'nomor',
    'tanggal',
    'status',
    'user_id',
    'created_at',
    'updated_at',
    'deleted_at'
];
    protected $useTimestamps = true;  // AKTIFKAN
    protected $useSoftDeletes = true; // AKTIFKAN
}
