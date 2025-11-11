<?php

namespace App\Controllers;

use App\Models\LogAntrianModel;
use CodeIgniter\RESTful\ResourceController;

class LogAntrianController extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $model = new LogAntrianModel();
        $data = $model->orderBy('id_log', 'DESC')->findAll();
        return $this->respond($data, 200);
    }
}
