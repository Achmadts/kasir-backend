<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    protected $table = 'kategoris';
    protected $primaryKey = 'id';
    protected $fillable = ['kode_kategori', 'nama_kategori'];

    public function produks()
    {
        return $this->hasMany(Produk::class, 'id_kategori', 'id');
    }
}
