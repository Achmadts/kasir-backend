<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\{Border, Alignment};
use Maatwebsite\Excel\Concerns\{WithHeadings, FromCollection, WithStyles, ShouldAutoSize, WithEvents};

class CashierExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    /**
     * 
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return DB::table('users')
            ->select(
                'id',
                'name',
                'email',
                DB::raw("IF(is_admin = 1, 'Admin', 'User') as role"),
                'images'
            )
            ->get();
    }

    /**
     * 
     * @return array
     */
    public function headings(): array
    {
        return [
            "User ID",
            "Nama",
            "Email",
            "Role",
            "Image",
        ];
    }

    /**
     * 
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    /**
     * 
     * @return array
     */
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