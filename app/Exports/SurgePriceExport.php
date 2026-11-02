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

class SurgePriceExport implements FromView, ShouldAutoSize, WithStyles, WithHeadings, WithEvents
{
    use Exportable;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('file-exports.surge-price', [
            'data' => $this->data,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A2:G2')->getFont()->setBold(true);
        $sheet->getStyle('A3:G3')->getFont()->setBold(true)->getColor()->setARGB('FFFFFF');
        $sheet->getStyle('A3:G3')->getFill()->applyFromArray([
            'fillType' => 'solid',
            'rotation' => 0,
            'color' => ['rgb' => '22663e'],
        ]);

        $sheet->setShowGridlines(false);

        return [
            'A1:G' . ($this->data['data']->count() + 3) => [
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
                $rows = $this->data['data']->count() + 3;

                $event->sheet->getStyle('A1:G1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle('A2:C2')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle('A3:G' . $rows)->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle('D2:G2')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $event->sheet->mergeCells('A1:G1');
                $event->sheet->mergeCells('A2:C2');
                $event->sheet->mergeCells('D2:G2');

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
