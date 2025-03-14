<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\{Border, Alignment};
use Maatwebsite\Excel\Concerns\{WithHeadings, FromCollection, WithStyles, ShouldAutoSize, WithEvents};

class PembelianExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('pembelians')
            ->join('detail_pembelians', 'pembelians.id', '=', 'detail_pembelians.id_pembelian')
            ->join('produks', 'detail_pembelians.id_produk', '=', 'produks.id')
            ->select(
                'pembelians.id as ID_Pembelian',
                'pembelians.date as Tanggal_Pembelian',
                'produks.nama_produk as Nama_Produk',
                'detail_pembelians.jumlah_produk as Jumlah_Produk',
                'detail_pembelians.sub_total as Sub_Total',
                'pembelians.quantity as Qty',
                'pembelians.tax as Pajak',
                'pembelians.discount as Diskon',
                'pembelians.total_pembayaran as Total_Harga',
                'pembelians.status as Status',
                'pembelians.payment_method as Metode_Pembayaran',
                'pembelians.note as Catatan'
            )
            ->get();
    }

    public function headings(): array
    {
        return [
            "ID Pembelian",
            "Tanggal Pembelian",
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
