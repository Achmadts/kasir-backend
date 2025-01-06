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
        for ($i = 1; $i <= 30; $i++) {
            $quantity = $i;

            if ($i === 29) {
                $quantity = 0;
            }

            if ($i === 30) {
                $quantity = -10;
            }

            $pelangganId = DB::table('pelanggans')->insertGetId([
                'nama_pelanggan' => 'Pelanggan-' . $i,
                'kota' => 'Karawang',
                'negara' => 'Indonesia',
            ]);

            $penjualanId = DB::table('penjualans')->insertGetId([
                'id_pelanggan' => $pelangganId,
                'tanggal_penjualan' => "2025-01-" .$i,
                'quantity' => $quantity,
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
