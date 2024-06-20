<?php

namespace App\Models;

use CodeIgniter\Model;

class M_npd extends Model
{
    function __construct(Type $var = null)
    {
        parent::__construct();
    }

    public function getWilayah()
    {
        $sql = 'SELECT w1.kode_wilayah,w1.nama,w1.id_level_wilayah,w1.mst_kode_wilayah, w2.nama as nama_parent
                FROM wilayah w1
                JOIN wilayah w2 ON w1.mst_kode_wilayah=w2.kode_wilayah
                where w1.id_level_wilayah<=2
                order by w1.kode_wilayah';

        $query = $this->db->query($sql);

        return $query->getResult();
    }
}
