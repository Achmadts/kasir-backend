<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PenjualanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            $pelangganId = DB::table('pelanggans')->insertGetId([
                'nama_pelanggan' => 'Pelanggan-' . $i,
                'kota' => 'Karawang',
                'negara' => 'Indonesia',
            ]);

            $penjualanId = DB::table('penjualans')->insertGetId([
                'id_pelanggan' => $pelangganId,
                'tanggal_penjualan' => "2024-12-" .$i,
                'quantity' => $i,
                'pajak' => random_int(1000, 999999),
                'diskon' => random_int(1000, 999999),
                'total_harga' => random_int(1000, 999999),
                'status' => 'Completed',
                'metode_pembayaran' => 'Cash',
                'catatan' => 'Catatan ' . $i,
            ]);

            DB::table('detail_penjualans')->insert([
                'id_penjualan' => $penjualanId,
                'id_produk' => $i,
                'jumlah_produk' => $i,
                'sub_total' => random_int(1000, 999999),
            ]);
        }
    }
}
