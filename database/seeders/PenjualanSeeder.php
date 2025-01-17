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
        $startDate = new DateTime('2024-01-17');

        for ($i = 1; $i <= 1825; $i++) {
            $produk = DB::table('produks')->where('id', $i)->first();

            $pelangganId = DB::table('pelanggans')->insertGetId([
                'nama_pelanggan' => 'Pelanggan-' . $i,
                'kota' => 'Karawang',
                'negara' => 'Indonesia',
            ]);

            $tanggalPenjualan = $startDate->format('Y-m-d');
            $startDate->modify('+1 day');
            $jumlahBarang = random_int(1, 50);
            $hargaJual = random_int(7000, 10000);
            $totalHarga = $jumlahBarang * $hargaJual;
            $pajak = $totalHarga * 0.1;
            $diskon = $totalHarga * 0.05;
            $penjualanId = DB::table('penjualans')->insertGetId([
                'id_pelanggan' => $pelangganId,
                'tanggal_penjualan' => $tanggalPenjualan,
                'quantity' => $jumlahBarang,
                'pajak' => $pajak,
                'diskon' => $diskon,
                'total_harga' => $totalHarga,
                'status' => 'Completed',
                'metode_pembayaran' => 'Cash',
                'catatan' => 'Catatan ' . $i,
            ]);

            DB::table('detail_penjualans')->insert([
                'id_penjualan' => $penjualanId,
                'id_produk' => $produk->id,
                'jumlah_produk' => $jumlahBarang,
                'sub_total' => ($totalHarga - $pajak - $diskon) / $jumlahBarang,
            ]);
        }
    }
}
