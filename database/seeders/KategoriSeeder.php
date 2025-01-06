<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            DB::table('kategoris')->insert([
                'nama_kategori' => 'Kategori ' . $i,
                'kode_kategori' => 'CA-' . $i,
            ]);
        }
    }
}
