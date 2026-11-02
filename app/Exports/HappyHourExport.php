<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HappyHourExport implements FromView, ShouldAutoSize, WithStyles, WithColumnWidths, WithHeadings, WithEvents
{
    use Exportable;

    protected $data;
    protected $search;

    public function __construct($data, $search = null)
    {
        $this->data = $data;
        $this->search = $search;
    }

    public function view(): View
    {
        return view('file-exports.happy-hour', [
            'data' => $this->data,
            'search' => $this->search,
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'B' => 45,
            'G' => 45,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A2:H3')->getFont()->setBold(true);
        $sheet->getStyle('A3:H3')->getFill()->applyFromArray([
            'fillType' => 'solid',
            'rotation' => 0,
            'color' => ['rgb' => 'dae0e0'],
        ]);

        $sheet->setShowGridlines(false);

        return [
            'A1:H'.($this->data->count() + 4) => [
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
                $event->sheet->getStyle('A1:H1')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $event->sheet->getStyle('A2:C2')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $event->sheet->mergeCells('A1:H1');
                $event->sheet->mergeCells('A2:C2');
                $event->sheet->mergeCells('D2:H2');

                $event->sheet->getDefaultRowDimension()->setRowHeight(30);
                $event->sheet->getRowDimension(1)->setRowHeight(50);
                $event->sheet->getRowDimension(2)->setRowHeight(40);
            },
        ];
    }

    public function headings(): array
    {
        return [
            '1',
        ];
    }
}
