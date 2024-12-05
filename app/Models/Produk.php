<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produks';
    protected $primaryKey = 'id';
    protected $fillable = ['nama_produk', 'harga', 'stok'];

    public function detail_penjualans()
    {
        return $this->belongsTo(DetailPenjualan::class);
    }

    public function kategoris()
    {
        return $this->belongsTo(Kategori::class);
    }
}
