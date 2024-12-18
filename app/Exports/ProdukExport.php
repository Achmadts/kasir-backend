<?php

namespace App\Exports;

use App\Models\Produk;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ProdukExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Produk::select('kode_produk', 'nama_produk', 'harga_beli', 'harga_jual', 'stok', 'deskripsi', 'foto', 'kategori_id')->get();
    }
    public function headings(): array
    {
        return ["Kode Produk", "Nama Produk", "Harga Beli", "Harga Jual", "Stok", "Deskripsi", "Foto", "Kategori ID"];
    }
}
