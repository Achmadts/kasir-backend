<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produks';
    protected $primaryKey = 'id';
    protected $fillable = ['kode_produk', 'nama_produk', 'harga_beli', 'harga_jual', 'stok', 'deskripsi', 'kategori_id', 'foto'];

    public function detail_penjualans()
    {
        return $this->belongsTo(DetailPenjualan::class);
    }

    public function kategoris()
    {
        return $this->belongsTo(Kategori::class);
    }
}
