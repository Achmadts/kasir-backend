<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            DB::table('produks')->insert([
                'kode_produk' => 'Prod-'. $i,
                'nama_produk' => 'Produk ' . $i,
                'harga_beli' => str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT),
                'harga_jual' => str_pad(random_int(1, 999999), 6, '0', STR_PAD_LEFT),
                'stok' => str_pad(random_int(1, 999), 3, '0', STR_PAD_LEFT),
                'deskripsi' => 'Deskripsi Produk ' . $i,
                'foto' => 'storage/images/produk' . $i,
                'kategori_id' => $i
            ]);
        }
    }
}
