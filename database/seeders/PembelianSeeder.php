<?php

namespace Database\Seeders;

use DateTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PembelianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $startDate = new DateTime('2024-01-12');

        for ($i = 1; $i <= 365; $i++) {
            $jumlahBarang = $i;
            $totalPembayaran = random_int(1000, 999999);

            $produkId = DB::table('produks')->insertGetId([
                'kode_produk' => 'Prod-' . $i,
                'nama_produk' => 'Produk ' . $i,
                'harga_beli' => $totalPembayaran / $jumlahBarang,
                'harga_jual' => ($totalPembayaran / $jumlahBarang) * 1.2,
                'foto' => 'storage/images/produk' . $i,
                'kategori_id' => $i,
                'stok' => $i,
            ]);

            $tanggalPembelian = $startDate->format('Y-m-d');
            $startDate->modify('+1 day');

            DB::table('pembelians')->insert([
                'id_produk' => $produkId,
                'date' => $tanggalPembelian,
                'nama_supplier' => 'Supplier-' . $i,
                'tax' => random_int(1000, 999999),
                'discount' => random_int(1000, 999999),
                'jumlah_barang' => $jumlahBarang,
                'status' => 'Completed',
                'payment_method' => 'Cash',
                'total_pembayaran' => $totalPembayaran,
                'note' => 'Catatan ' . $i,
            ]);

            DB::table('produks')->where('id', $produkId)->increment('stok', $jumlahBarang);
        }
    }
}
