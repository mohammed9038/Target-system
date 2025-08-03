<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $employee_code
 * @property string|null $salesman_code
 * @property string $name
 * @property int $region_id
 * @property int $channel_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_active
 * @property-read \App\Models\Channel $channel
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesmanClassification> $classifications
 * @property-read int|null $classifications_count
 * @property-read mixed $classification_list
 * @property-read \App\Models\Region $region
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalesTarget> $salesTargets
 * @property-read int|null $sales_targets_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salesman active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salesman byClassification($classification)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salesman newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salesman newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salesman query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salesman whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salesman whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salesman whereEmployeeCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salesman whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salesman whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salesman whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salesman whereRegionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salesman whereSalesmanCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Salesman whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Salesman extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_code',
        'salesman_code',
        'name',
        'region_id',
        'channel_id',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($salesman) {
            if (empty($salesman->salesman_code)) {
                $salesman->salesman_code = static::generateSalesmanCode();
            }
        });
    }

    public static function generateSalesmanCode()
    {
        $lastSalesman = static::orderBy('id', 'desc')->first();
        $nextNumber = $lastSalesman ? ((int) substr($lastSalesman->salesman_code, 3)) + 1 : 1;
        return 'SAL' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function salesTargets()
    {
        return $this->hasMany(SalesTarget::class);
    }

    public function classifications()
    {
        return $this->hasMany(SalesmanClassification::class);
    }

    public function hasClassification($classification)
    {
        return $this->classifications()->where('classification', $classification)->exists();
    }

    public function getClassificationListAttribute()
    {
        return $this->classifications()->pluck('classification')->toArray();
    }

    public function scopeActive($query)
    {
        return $query->whereHas('region', function ($q) {
            $q->where('is_active', true);
        })->whereHas('channel', function ($q) {
            $q->where('is_active', true);
        });
    }

    public function scopeByClassification($query, $classification)
    {
        return $query->whereHas('classifications', function($q) use ($classification) {
            $q->where('classification', $classification);
        });
    }
} 