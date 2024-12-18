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
        return Produk::select('nama_produk', 'harga', 'stok', 'kategori_id')->get();
    }
    public function headings(): array
    {
        return ["Nama Produk", "Harga", "Stok", "Kategori ID"];
    }
}
