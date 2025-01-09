<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $table = 'pembelians';
    protected $fillable = ['id_produk', 'date', 'nama_supplier', 'tax', 'discount', 'jumlah_barang', 'status', 'payment_method', 'total_pembayaran', 'note'];
    protected $primaryKey = 'id';

    public function produks (){
        return $this->belongsTo(Produk::class, 'id_produk', 'id');
    }
}
