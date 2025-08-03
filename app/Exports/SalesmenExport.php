<?php

namespace App\Exports;

use App\Models\Salesman;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SalesmenExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $query = Salesman::with(['region', 'channel']);

        // Apply filters
        if (!empty($this->filters['name'])) {
            $query->where('name', 'like', '%' . $this->filters['name'] . '%');
        }

        if (isset($this->filters['is_active'])) {
            $query->where('is_active', $this->filters['is_active']);
        }

        if (!empty($this->filters['region_id'])) {
            $query->where('region_id', $this->filters['region_id']);
        }

        if (!empty($this->filters['channel_id'])) {
            $query->where('channel_id', $this->filters['channel_id']);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Active',
            'Region',
            'Channel',
            'Targets Count',
            'Created At',
            'Updated At',
        ];
    }

    /**
     * @param $salesman
     * @return array
     */
    public function map($salesman): array
    {
        return [
            $salesman->id,
            $salesman->name,
            $salesman->is_active ? 'Yes' : 'No',
            $salesman->region ? $salesman->region->name : '',
            $salesman->channel ? $salesman->channel->name : '',
            $salesman->salesTargets()->count(),
            $salesman->created_at ? $salesman->created_at->format('Y-m-d H:i:s') : '',
            $salesman->updated_at ? $salesman->updated_at->format('Y-m-d H:i:s') : '',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Header styling
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 
                  'fill' => ['fillType' => 'solid', 'color' => ['rgb' => '4472C4']]],
            
            // Auto-size columns
            'A:H' => ['alignment' => ['horizontal' => 'left']],
            
            // Active column styling
            'C' => ['alignment' => ['horizontal' => 'center']],
            
            // Numbers alignment
            'F' => ['alignment' => ['horizontal' => 'center']],
        ];
    }
}
