<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\LoketModel;

class LoketController extends BaseController
{
    public function getLoketByJenis($kodeJenis)
    {
        $loketModel = new LoketModel();

        $loket = $loketModel
            ->select('kode_loket, nama_loket')
            ->where('kode_jenis', $kodeJenis)
            ->orderBy('kode_loket', 'ASC')
            ->findAll();

        return $this->response->setJSON($loket ?: []);
    }
}
