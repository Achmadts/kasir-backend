<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenjualan extends Model
{
    protected $table = 'detail_penjualans';
    protected $primaryKey = 'id';
    protected $fillable = ["penjualan_id", "produk_id", "jumlah_produk", "sub_total"];

    public function pelanggans()
    {
        return $this->belongsTo(Pelanggan::class);
    }
    public function produks()
    {
        return $this->hasMany(Produk::class);
    }
}
