<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggans';
    protected $primaryKey = 'id';
    protected $fillable = ['nama_pelanggan', 'alamat', 'nomor_telepon'];

    public function detail_penjualans()
    {
        return $this->hasMany(DetailPenjualan::class, 'id_pelanggan', 'id');
    }
    public function penjualans()
    {
        return $this->hasMany(Penjualan::class, 'id_pelanggan', 'id');
    }
}
