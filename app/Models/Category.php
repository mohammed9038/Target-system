<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_code',
        'name',
        'supplier_id',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($category) {
            if (empty($category->category_code)) {
                $category->category_code = static::generateCategoryCode();
            }
        });
    }

    public static function generateCategoryCode()
    {
        $lastCategory = static::orderBy('id', 'desc')->first();
        $nextNumber = $lastCategory ? ((int) substr($lastCategory->category_code, 3)) + 1 : 1;
        return 'CAT' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function salesTargets()
    {
        return $this->hasMany(SalesTarget::class);
    }
} 