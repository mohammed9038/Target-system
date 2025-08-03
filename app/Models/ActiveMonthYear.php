<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $year
 * @property int $month
 * @property bool $is_open
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $period
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveMonthYear closed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveMonthYear newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveMonthYear newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveMonthYear open()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveMonthYear query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveMonthYear whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveMonthYear whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveMonthYear whereIsOpen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveMonthYear whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveMonthYear whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActiveMonthYear whereYear($value)
 * @mixin \Eloquent
 */
class ActiveMonthYear extends Model
{
    use HasFactory;

    protected $table = 'active_months_years';

    protected $fillable = [
        'year',
        'month',
        'is_open',
    ];

    protected $casts = [
        'is_open' => 'boolean',
    ];

    public function scopeOpen($query)
    {
        return $query->where('is_open', true);
    }

    public function scopeClosed($query)
    {
        return $query->where('is_open', false);
    }

    public function getPeriodAttribute()
    {
        return sprintf('%04d-%02d', $this->year, $this->month);
    }
} 