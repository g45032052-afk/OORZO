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

class EtaConfigurationExport implements FromView, ShouldAutoSize, WithStyles, WithHeadings, WithEvents
{
    use Exportable;

    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('file-exports.eta-configuration', [
            'data' => $this->data,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A2:I2')->getFont()->setBold(true);
        $sheet->getStyle('A3:I3')->getFont()->setBold(true);
        $sheet->getStyle('A3:I3')->getFill()->applyFromArray([
            'fillType' => 'solid',
            'rotation' => 0,
            'color' => ['rgb' => 'dae0e0'],
        ]);

        $sheet->setShowGridlines(false);

        return [
            'A1:I' . ($this->data['data']->count() + 3) => [
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
                $event->sheet->getStyle('A1:I1')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                $event->sheet->getStyle('A3:I' . ($this->data['data']->count() + 3))
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $event->sheet->mergeCells('A1:I1');
                $event->sheet->mergeCells('A2:D2');
                $event->sheet->mergeCells('E2:I2');

                $event->sheet->getDefaultRowDimension()->setRowHeight(30);
                $event->sheet->getRowDimension(1)->setRowHeight(50);
                $event->sheet->getRowDimension(2)->setRowHeight(50);
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
