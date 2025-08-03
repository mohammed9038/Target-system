<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $region_code
 * @property string $name
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Channel> $channels
 * @property-read int|null $channels_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Salesman> $salesmen
 * @property-read int|null $salesmen_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereRegionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Region whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'region_code',
        'name',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($region) {
            if (empty($region->region_code)) {
                $region->region_code = static::generateRegionCode();
            }
        });
    }

    public static function generateRegionCode()
    {
        $lastRegion = static::orderBy('id', 'desc')->first();
        $nextNumber = $lastRegion ? ((int) substr($lastRegion->region_code, 1)) + 1 : 1;
        return 'R' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function channels()
    {
        return $this->hasMany(Channel::class);
    }

    public function salesmen()
    {
        return $this->hasMany(Salesman::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_regions');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
} 