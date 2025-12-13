<?php

namespace App\Models;

use CodeIgniter\Model;

class LogAntrianModel extends Model
{
    protected $table = 'log_antrian';
    protected $primaryKey = 'id_log';

    // WAJIB: lindungi field
    protected $protectFields = true;

    protected $allowedFields = [
        'id_antrian',
        'user_id',
        'aksi',
        'waktu'
    ];

    protected $useTimestamps = false;
}
