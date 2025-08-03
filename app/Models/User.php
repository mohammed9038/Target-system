<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\User
 * 
 * @property int $id
 * @property string $username
 * @property string $password
 * @property string $role
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Region[] $regions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Channel[] $channels
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserClassification[] $classifications
 * @property-read array $classification_list
 * @method bool isAdmin()
 * @method bool isManager()
 * @method array|null scope()
 * @method array getClassificationListAttribute()
 * @method array getRegionIds()
 * @method array getChannelIds()
 * @method bool hasRegion(int $regionId)
 * @method bool hasChannel(int $channelId)
 * @method bool hasClassification(string $classification)
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function regions()
    {
        return $this->belongsToMany(Region::class, 'user_regions');
    }

    public function channels()
    {
        return $this->belongsToMany(Channel::class, 'user_channels');
    }

    // Legacy properties for backward compatibility
    public function getRegionIdAttribute()
    {
        return $this->regions()->first()?->id;
    }

    public function getChannelIdAttribute()
    {
        return $this->channels()->first()?->id;
    }

    public function getRegionAttribute()
    {
        return $this->regions()->first();
    }

    public function getChannelAttribute()
    {
        return $this->channels()->first();
    }

    // Helper methods
    public function getRegionIds()
    {
        return $this->regions()->pluck('regions.id')->toArray();
    }

    public function getChannelIds()
    {
        return $this->channels()->pluck('channels.id')->toArray();
    }

    public function hasRegion($regionId)
    {
        return $this->regions()->where('regions.id', $regionId)->exists();
    }

    public function hasChannel($channelId)
    {
        return $this->channels()->where('channels.id', $channelId)->exists();
    }

    public function classifications()
    {
        return $this->hasMany(UserClassification::class);
    }

    public function hasClassification($classification)
    {
        return $this->classifications()->where('classification', $classification)->exists();
    }

    public function getClassificationListAttribute()
    {
        return $this->classifications()->pluck('classification')->toArray();
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isManager()
    {
        return $this->role === 'manager';
    }

    public function scope()
    {
        if ($this->isAdmin()) {
            return null; // Admin sees everything
        }
        
        $scope = [
            'region_ids' => $this->getRegionIds(),
            'channel_ids' => $this->getChannelIds(),
        ];

        // Add classification scope using many-to-many relationship
        $userClassifications = $this->getClassificationListAttribute();
        if (!empty($userClassifications)) {
            $scope['classifications'] = $userClassifications;
        }

        return $scope;
    }
} 