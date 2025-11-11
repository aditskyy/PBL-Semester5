<?php

namespace App\Models;

use CodeIgniter\Model;

class LogAntrianModel extends Model
{
    protected $table = 'log_antrian';
    protected $primaryKey = 'id_log';
    protected $allowedFields = [
        'id_antrian',
        'aksi',      // 'next', 'reset', 'create', 'panggil ulang'
        'waktu',     // datetime
        'user_id'
    ];
}
