<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_code',
        'name',
        'classification',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($supplier) {
            if (empty($supplier->supplier_code)) {
                $supplier->supplier_code = static::generateSupplierCode();
            }
        });
    }

    public static function generateSupplierCode()
    {
        $lastSupplier = static::orderBy('id', 'desc')->first();
        $nextNumber = $lastSupplier ? ((int) substr($lastSupplier->supplier_code, 1)) + 1 : 1;
        return 'S' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function salesTargets()
    {
        return $this->hasMany(SalesTarget::class);
    }

    public function scopeByClassification($query, $classification)
    {
        return $query->where('classification', $classification);
    }
} 