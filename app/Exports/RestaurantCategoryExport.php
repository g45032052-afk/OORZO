<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RestaurantCategoryExport implements FromView, ShouldAutoSize, WithStyles, WithHeadings, WithEvents
{
    use Exportable;

    protected mixed $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('file-exports.restaurant-category-export', [
            'data' => $this->data,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $endColumn = !empty($this->data['show_restaurant']) ? 'E' : 'D';
        $lastRow = $this->data['data']->count() + 3;

        $sheet->getStyle("A2:{$endColumn}2")->getFont()->setBold(true);
        $sheet->getStyle("A3:{$endColumn}3")->getFont()->setBold(true);
        $sheet->getStyle("A3:{$endColumn}3")->getFill()->applyFromArray([
            'fillType' => 'solid',
            'rotation' => 0,
            'color' => ['rgb' => 'dae0e0'],
        ]);

        $sheet->setShowGridlines(false);
        $sheet->getStyle("A1:{$endColumn}1")->applyFromArray([
            'borders' => [
                'bottom' => ['borderStyle' => 'hair', 'color' => ['argb' => 'FFFF0000']],
                'top' => ['borderStyle' => 'hair', 'color' => ['argb' => 'FFFF0000']],
                'right' => ['borderStyle' => 'hair', 'color' => ['argb' => 'FF00FF00']],
                'left' => ['borderStyle' => 'hair', 'color' => ['argb' => 'FF00FF00']],
            ],
            'fillType' => 'solid',
            'rotation' => 0,
        ]);

        return [
            "A1:{$endColumn}{$lastRow}" => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => '000000'],
                    ],
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $endColumn = !empty($this->data['show_restaurant']) ? 'E' : 'D';
                $lastRow = $this->data['data']->count() + 3;

                $event->sheet->getStyle("A1:{$endColumn}1")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $event->sheet->getStyle("A3:{$endColumn}{$lastRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $event->sheet->getStyle("A2:{$endColumn}2")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $event->sheet->mergeCells("A1:{$endColumn}1");
                $event->sheet->mergeCells("A2:{$endColumn}2");

                $event->sheet->getDefaultRowDimension()->setRowHeight(30);
                $event->sheet->getRowDimension(1)->setRowHeight(50);
                $event->sheet->getRowDimension(2)->setRowHeight(40);
            },
        ];
    }

    public function headings(): array
    {
        return ['1'];
    }
}
