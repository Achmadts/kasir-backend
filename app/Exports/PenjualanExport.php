<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\{Border, Alignment};
use Maatwebsite\Excel\Concerns\{WithHeadings, FromCollection, WithStyles, ShouldAutoSize, WithEvents};

class PenjualanExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    public function collection()
    {
        return DB::table('penjualans')
            ->join('pelanggans', 'penjualans.id_pelanggan', '=', 'pelanggans.id')
            ->join('detail_penjualans', 'penjualans.id', '=', 'detail_penjualans.id_penjualan')
            ->join('produks', 'detail_penjualans.id_produk', '=', 'produks.id')
            ->select(
                'penjualans.id as ID_Penjualan',
                'pelanggans.nama_pelanggan as Nama_Pelanggan',
                'penjualans.tanggal_penjualan as Tanggal_Penjualan',
                'produks.nama_produk as Nama_Produk',
                'detail_penjualans.jumlah_produk as Jumlah_Produk',
                'detail_penjualans.sub_total as Sub_Total',
                'penjualans.quantity as Qty',
                'penjualans.pajak as Pajak',
                'penjualans.diskon as Diskon',
                'penjualans.total_harga as Total_Harga',
                'penjualans.status as Status',
                'penjualans.metode_pembayaran as Metode_Pembayaran',
                'penjualans.catatan as Catatan'
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            "ID Penjualan",
            "Nama Pelanggan",
            "Tanggal Penjualan",
            "Nama Produk",
            "Jumlah Produk",
            "Sub Total",
            "Qty",
            "Pajak",
            "Diskon",
            "Total Harga",
            "Status",
            "Metode Pembayaran",
            "Catatan"
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $range = "A1:{$highestColumn}{$highestRow}";
                $sheet->getStyle($range)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle($range)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                ]);

                $sheet->setAutoFilter("A1:{$highestColumn}{$highestRow}");
            },
        ];
    }
}