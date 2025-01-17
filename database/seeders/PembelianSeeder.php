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
        $startDate = new DateTime('2024-12-01');

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
            $tax = $totalPembayaran * 0.10;
            $discount = $totalPembayaran * 0.05;
            $jumlahPembelian = $jumlahBarang;

            if ($i === 7) {
                $jumlahPembelian = $jumlahBarang * 20;
            }

            if ($i > 7) {
                $jumlahPembelian = $jumlahBarang * 25;
            }

            $pembelianId = DB::table('pembelians')->insertGetId([
                'date' => $tanggalPembelian,
                'nama_supplier' => 'Supplier-' . $i,
                'tax' => $tax,
                'quantity' => $jumlahPembelian,
                'discount' => $discount,
                'status' => 'Completed',
                'payment_method' => 'Cash',
                'total_pembayaran' => $totalPembayaran,
                'note' => 'Catatan ' . $i,
            ]);

            DB::table('produks')->where('id', $produkId)->increment('stok', $jumlahBarang);
            $subTotal = ($totalPembayaran - $tax - $discount) / $jumlahPembelian;
            DB::table('detail_pembelians')->insert([
                'id_pembelian' => $pembelianId,
                'id_produk' => $produkId,
                'jumlah_produk' => $jumlahPembelian,
                'sub_total' => $subTotal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
