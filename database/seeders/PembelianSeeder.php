<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PembelianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 30; $i++) {
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

            DB::table('pembelians')->insert([
                'id_produk' => $produkId,
                'date' => "2025-01-" . $i,
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
