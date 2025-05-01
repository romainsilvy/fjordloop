<?php

namespace App\Models;

use App\Models\Travel;
use Carbon\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model implements HasMedia
{
    use HasUuids, HasFactory, InteractsWithMedia;

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
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'travel_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        // 'start_time' => 'date',
        // 'end_time' => 'time',
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

    public function getMediaDisplay()
    {
        return $this->getMedia()->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getTemporaryUrl(Carbon::now()->addMinutes(5)),
                'name' => $media->file_name,
            ];
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
