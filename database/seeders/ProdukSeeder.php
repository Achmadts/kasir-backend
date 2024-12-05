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
        for ($i = 1; $i < 5; $i++) {
            DB::table('produks')->insert([
                'nama_produk' => 'Produk  ' . $i,
                'harga' => str_pad(random_int(1, 99999999), 8, '0', STR_PAD_LEFT),
                'stok' => str_pad(random_int(1, 999), 3, '0', STR_PAD_LEFT),
                'kategori_id' => $i
            ]);
        }
    }
}
