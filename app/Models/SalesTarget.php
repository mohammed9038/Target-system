<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'year',
        'month',
        'region_id',
        'channel_id',
        'salesman_id',
        'supplier_id',
        'category_id',
        'target_amount',
        'notes',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
    ];

    /**
     * Scope for filtering based on user permissions and request filters
     */
    public function scopeFilter($query, array $filters)
    {
        // Year filter
        if (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        // Month filter
        if (!empty($filters['month'])) {
            $query->where('month', $filters['month']);
        }

        // Region scope
        if (!empty($filters['region_id'])) {
            $query->where('region_id', $filters['region_id']);
        }
        if (!empty($filters['region_ids'])) {
            $query->whereIn('region_id', $filters['region_ids']);
        }

        // Channel scope
        if (!empty($filters['channel_id'])) {
            $query->where('channel_id', $filters['channel_id']);
        }
        if (!empty($filters['channel_ids'])) {
            $query->whereIn('channel_id', $filters['channel_ids']);
        }

        // Supplier filter
        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        // Category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Salesman filter
        if (!empty($filters['salesman_id'])) {
            $query->where('salesman_id', $filters['salesman_id']);
        }

        // Classification scope (via salesman)
        if (!empty($filters['classifications'])) {
            $query->whereHas('salesman', function($q) use ($filters) {
                $q->whereHas('classifications', function($subQ) use ($filters) {
                    $subQ->whereIn('classification', $filters['classifications']);
                });
            });
        }

        return $query;
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function salesman()
    {
        return $this->belongsTo(Salesman::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeByPeriod($query, $year, $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopeByRegion($query, $regionId)
    {
        return $query->where('region_id', $regionId);
    }

    public function scopeByChannel($query, $channelId)
    {
        return $query->where('channel_id', $channelId);
    }

    public function scopeBySalesman($query, $salesmanId)
    {
        return $query->where('salesman_id', $salesmanId);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeByEmployeeCode($query, $employeeCode)
    {
        return $query->whereHas('salesman', function ($q) use ($employeeCode) {
            $q->where('employee_code', $employeeCode);
        });
    }
} 