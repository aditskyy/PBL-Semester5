<?php

namespace App\Models;

use CodeIgniter\Model;

class LoketModel extends Model
{
    protected $table = 'loket';
    protected $primaryKey = 'kode_loket';
    protected $allowedFields = ['kode_loket', 'nama_loket', 'kode_jenis'];
    protected $useAutoIncrement = false; // karena kode_loket manual
}
