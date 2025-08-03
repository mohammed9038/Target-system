<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $salesman_id
 * @property string $classification
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Salesman $salesman
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesmanClassification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesmanClassification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesmanClassification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesmanClassification whereClassification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesmanClassification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesmanClassification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesmanClassification whereSalesmanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesmanClassification whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SalesmanClassification extends Model
{
    protected $fillable = [
        'salesman_id',
        'classification',
    ];

    public function salesman()
    {
        return $this->belongsTo(Salesman::class);
    }
}