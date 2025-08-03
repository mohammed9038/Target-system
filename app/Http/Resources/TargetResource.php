<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TargetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'salesman' => [
                'id' => $this->salesman->id,
                'name' => $this->salesman->name,
                'employee_code' => $this->salesman->employee_code,
                'region' => $this->salesman->region->name ?? null,
                'channel' => $this->salesman->channel->name ?? null,
            ],
            'supplier' => [
                'id' => $this->supplier->id,
                'name' => $this->supplier->name,
                'supplier_code' => $this->supplier->supplier_code,
                'classification' => $this->supplier->classification,
            ],
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'category_code' => $this->category->category_code,
            ],
            'period' => [
                'year' => $this->year,
                'month' => $this->month,
                'formatted' => $this->year . '-' . str_pad($this->month, 2, '0', STR_PAD_LEFT),
            ],
            'target_amount' => $this->target_amount,
            'formatted_amount' => '$' . number_format($this->target_amount, 2),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
