<?php

namespace App\Exports;

use App\Models\Kategori;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class KategoriExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Kategori::select('kode_kategori', 'nama_kategori')->get();
    }
    public function headings(): array
    {
        return ["Kode Kategori", "Nama Kategori"];
    }
}
