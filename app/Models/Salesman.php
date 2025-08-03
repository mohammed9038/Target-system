<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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