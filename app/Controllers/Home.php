<?php

namespace App\Controllers;

use App\Models\M_npd;

class Home extends BaseController
{

    function __construct()
    {
        $this->m_npd = new M_npd();
    }

    public function index(): string
    {
        $data_wilayah = $this->m_npd->getWilayah();
        $data = array();
        $data['data_wilayah'] = $data_wilayah;
        return view('utama', $data);
    }
}
