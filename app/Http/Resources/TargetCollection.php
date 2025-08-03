<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TargetCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total' => $this->collection->count(),
                'per_page' => $request->get('per_page', 15),
                'current_page' => $request->get('page', 1),
            ],
            'summary' => [
                'total_amount' => $this->collection->sum('target_amount'),
                'average_amount' => $this->collection->avg('target_amount'),
                'formatted_total' => '$' . number_format($this->collection->sum('target_amount'), 2),
            ],
        ];
    }
}
