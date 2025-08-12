<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Housing extends Model implements HasMedia
{
    use HasFactory, HasUuids, InteractsWithMedia;

    protected $fillable = [
        'name',
        'description',
        'url',
        'price_by_person',
        'price_by_group',
        'place_name',
        'place_latitude',
        'place_longitude',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'travel_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
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

    public function getStartTimeAttribute($startTime)
    {
        return $startTime ? Carbon::parse($startTime)->format('H:i') : null;
    }

    public function getEndTimeAttribute($endTime)
    {
        return $endTime ? Carbon::parse($endTime)->format('H:i') : null;
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

    /**
     * travel
     *
     * @return BelongsTo<Travel, $this>
     */
    public function travel(): BelongsTo
    {
        return $this->belongsTo(Travel::class);
    }
}
