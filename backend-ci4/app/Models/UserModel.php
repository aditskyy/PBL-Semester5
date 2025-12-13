<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users'; // sesuai tabel kamu
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'password', 'role'];
}
