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

class HappyHourRestaurantExport implements FromView, ShouldAutoSize, WithStyles, WithColumnWidths, WithHeadings, WithEvents
{
    use Exportable;

    protected $data;
    protected $happyHour;
    protected $search;

    public function __construct($data, $happyHour, $search = null)
    {
        $this->data = $data;
        $this->happyHour = $happyHour;
        $this->search = $search;
    }

    public function view(): View
    {
        return view('file-exports.happy-hour-restaurant', [
            'data' => $this->data,
            'happyHour' => $this->happyHour,
            'search' => $this->search,
        ]);
    }

    public function columnWidths(): array
    {
        return [
            'B' => 35,
            'C' => 25,
            'D' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A2:D3')->getFont()->setBold(true);
        $sheet->getStyle('A3:D3')->getFill()->applyFromArray([
            'fillType' => 'solid',
            'rotation' => 0,
            'color' => ['rgb' => 'dae0e0'],
        ]);

        $sheet->setShowGridlines(false);

        return [
            // Parenthesised: 'A1:D'.$count + 4 would add before concatenating.
            'A1:D'.($this->data->count() + 4) => [
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
                $event->sheet->getStyle('A1:D1')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $event->sheet->getStyle('A2:B2')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $event->sheet->mergeCells('A1:D1');
                $event->sheet->mergeCells('A2:B2');
                $event->sheet->mergeCells('C2:D2');

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
