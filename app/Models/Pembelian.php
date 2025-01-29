<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $table = 'pembelians';
    protected $fillable = ['id_produk', 'quantity', 'date', 'nama_supplier', 'tax', 'discount', 'jumlah_produk', 'status', 'payment_method', 'total_pembayaran', 'note', 'no_rekening_penerima', 'nama_rekening_penerima', 'bukti_transfer'];
    protected $primaryKey = 'id';

    public function produks()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id');
    }
    public function detailPembelians()
    {
        return $this->hasMany(DetailPembelian::class, 'id_pembelian');
    }
}
