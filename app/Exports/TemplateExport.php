<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class TemplateExport implements FromArray, WithTitle, WithStyles
{
    private array $templateData;
    private string $title;

    public function __construct(array $templateData, string $title = 'Template')
    {
        $this->templateData = $templateData;
        $this->title = $title;
    }

    /**
     * Get the data array
     */
    public function array(): array
    {
        return $this->templateData;
    }

    /**
     * Set the sheet title
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * Apply styles to the worksheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold header
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFDCE6F1']
                ]
            ],
        ];
    }
}
