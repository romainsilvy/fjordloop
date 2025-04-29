<?php

namespace App\Models;

use App\Models\Travel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'name',
        'description',
        'url',
        'price_by_person',
        'price_by_group',
        'place_name',
        'place_latitude',
        'place_longitude',
        'place_geojson',
        'travel_id',
    ];

    protected static function booted()
    {
        static::addGlobalScope('userIsMember', function (Builder $builder) {
            $builder->whereHas('travel.members', function ($query) {
                if (auth()->check()) {
                    $query->where('user_id', auth()->id());
                } else {
                    $query->where('user_id', null);
                }
            });
        });
    }

    public function scopeHasPlace(Builder $query)
    {
        return $query->whereNotNull(['place_name', 'place_latitude', 'place_longitude']);
    }

    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }
}
