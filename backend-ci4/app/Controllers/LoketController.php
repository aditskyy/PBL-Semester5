<?php

namespace App\Controllers;

use App\Models\LoketModel;
use CodeIgniter\RESTful\ResourceController;

class LoketController extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $model = new LoketModel();
        $data = $model->findAll();

        return $this->respond($data, 200);
    }
}
