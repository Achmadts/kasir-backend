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
        $startDate = new DateTime('2024-01-17');

        for ($i = 1; $i <= 1825; $i++) {
            $jumlahBarang = random_int(1, 100);
            $hargaBeli = random_int(500, 5000);
            $totalPembayaran = $jumlahBarang * $hargaBeli;

            $produkId = DB::table('produks')->insertGetId([
                'kode_produk' => 'Prod-' . $i,
                'nama_produk' => 'Produk ' . $i,
                'harga_beli' => $hargaBeli,
                'harga_jual' => $hargaBeli * 1.5,
                'foto' => 'storage/images/produk' . $i,
                'kategori_id' => $i,
                'stok' => $jumlahBarang,
            ]);

            $tanggalPembelian = $startDate->format('Y-m-d');
            $startDate->modify('+1 day');

            $tax = $totalPembayaran * 0.10;
            $discount = $totalPembayaran * 0.05;

            $pembelianId = DB::table('pembelians')->insertGetId([
                'date' => $tanggalPembelian,
                'nama_supplier' => 'Supplier-' . $i,
                'tax' => $tax,
                'quantity' => $jumlahBarang,
                'discount' => $discount,
                'status' => 'Completed',
                'payment_method' => 'Cash',
                'total_pembayaran' => $totalPembayaran,
                'note' => 'Catatan ' . $i,
            ]);

            $subTotal = ($totalPembayaran - $tax - $discount) / $jumlahBarang;
            DB::table('detail_pembelians')->insert([
                'id_pembelian' => $pembelianId,
                'id_produk' => $produkId,
                'jumlah_produk' => $jumlahBarang,
                'sub_total' => $subTotal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
