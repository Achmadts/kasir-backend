<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table = 'penjualans';
    protected $primaryKey = 'id';
    protected $fillable = ['tanggal_penjualan', 'total_harga', 'id_pelanggan'];

    public function pelanggans()
    {
        return $this->belongsTo(Pelanggan::class);
    }
}
