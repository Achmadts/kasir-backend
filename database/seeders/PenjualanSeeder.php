<?php

namespace Database\Seeders;

use DateTime;
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
        $startDate = new DateTime('2024-12-01');

        for ($i = 1; $i <= 365; $i++) {
            $pelangganId = DB::table('pelanggans')->insertGetId([
                'nama_pelanggan' => 'Pelanggan-' . $i,
                'kota' => 'Karawang',
                'negara' => 'Indonesia',
            ]);

            $tanggalPenjualan = $startDate->format('Y-m-d');
            $startDate->modify('+1 day');

            $totalHarga = random_int(1000, 999999);
            $pajak = $totalHarga * 0.1;
            $diskon = $totalHarga * 0.05;

            $penjualanId = DB::table('penjualans')->insertGetId([
                'id_pelanggan' => $pelangganId,
                'tanggal_penjualan' => $tanggalPenjualan,
                'quantity' => $i ** 2,
                'pajak' => $pajak,
                'diskon' => $diskon,
                'total_harga' => $totalHarga,
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
