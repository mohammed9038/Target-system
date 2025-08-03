<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $classification
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserClassification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserClassification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserClassification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserClassification whereClassification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserClassification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserClassification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserClassification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserClassification whereUserId($value)
 * @mixin \Eloquent
 */
class UserClassification extends Model
{
    protected $fillable = [
        'user_id',
        'classification',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}