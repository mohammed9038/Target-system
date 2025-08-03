<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $year
 * @property int $month
 * @property int $region_id
 * @property int $channel_id
 * @property int $salesman_id
 * @property int $supplier_id
 * @property int $category_id
 * @property numeric $target_amount
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $classification
 * @property-read \App\Models\Category $category
 * @property-read \App\Models\Channel $channel
 * @property-read \App\Models\Region $region
 * @property-read \App\Models\Salesman $salesman
 * @property-read \App\Models\Supplier $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget byCategory($categoryId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget byChannel($channelId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget byEmployeeCode($employeeCode)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget byPeriod($year, $month)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget byRegion($regionId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget bySalesman($salesmanId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget bySupplier($supplierId)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget filter(array $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget whereClassification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget whereRegionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget whereSalesmanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget whereTargetAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesTarget whereYear($value)
 * @mixin \Eloquent
 */
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