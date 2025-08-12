<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Travel extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'place_name',
        'place_latitude',
        'place_longitude',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public static function fromInvitation(string $token): ?Travel
    {
        return self::withoutGlobalScope('userIsMember')->whereHas('invitations', function (Builder $query) use ($token) {
            $query->where('token', $token);
        })->first();
    }

    protected static function booted()
    {
        static::addGlobalScope('userIsMember', function (Builder $builder) {
            if (auth()->check()) {
                $builder->whereHas('members', fn ($query) => $query->where('user_id', auth()->id()));
            }
        });

        // static::creating(function ($travel) {
        //     $type = TravelType::where('id', $travel->travel_type_id)->firstOrFail();
        //     $image = $type->travelImages->random();
        //     $travel->travel_image_id = $image->id;
        // });

        // static::updating(function ($travel) {
        //     $type = TravelType::where('id', $travel->travel_type_id)->firstOrFail();
        //     $image = $type->travelImages->random();
        //     $travel->travel_image_id = $image->id;
        // });
    }

    /**
     * members
     *
     * @return BelongsToMany<User, $this>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('is_owner')->withTimestamps();
    }

    /**
     * owner
     */
    public function owner(): User
    {
        return $this->belongsToMany(User::class)->withPivot('is_owner')->withTimestamps()->wherePivot('is_owner', true)->first();
    }

    public function isOwner(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->wherePivot('is_owner', true)->exists();
    }

    /**
     * Scope a query to only include ended travels.
     *
     * @param  Builder<Travel>  $query
     * @return Builder<Travel>
     */
    public function scopePast(Builder $query): Builder
    {
        return $query->where('end_date', '<', now());
    }

    /**
     * Scope a query to only include active travels.
     *
     * @param  Builder<Travel>  $query
     * @return Builder<Travel>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where(fn ($query) => $query->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
        );
    }

    /**
     * Scope a query to only include upcoming travels.
     *
     * @param  Builder<Travel>  $query
     * @return Builder<Travel>
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Scope a query to only include travels that have no complete dates.
     *
     * @param  Builder<Travel>  $query
     * @return Builder<Travel>
     */
    public function scopeNoDate(Builder $query): Builder
    {
        return $query->whereNull('start_date')->orWhereNull('end_date');
    }

    public function attachOwner(User $user): void
    {
        $this->members()->attach($user->id, ['is_owner' => true]);
    }

    /**
     * invite
     *
     * @param  array<string>  $members
     */
    public function inviteMembers(array $members, User $user): void
    {
        foreach ($members as $member) {
            $this->invitations()->create([
                'email' => $member,
                'user_id' => $user->id,
            ]);
        }
    }

    public function acceptInvitation(string $token): void
    {
        $this->invitations()->where('token', $token)->firstOrFail()->delete();
        $this->members()->attach(auth()->id());
    }

    public function refuseInvitation(string $token): void
    {
        $this->invitations()->where('token', $token)->firstOrFail()->delete();
    }

    public function isMember(): bool
    {
        return $this->members()->where('user_id', auth()->id())->exists();
    }

    /**
     * invitations
     *
     * @return HasMany<TravelInvitation, $this>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(TravelInvitation::class);
    }

    // /**
    //  * travelType
    //  *
    //  * @return BelongsTo<TravelType, $this>
    //  */
    // public function travelType(): BelongsTo
    // {
    //     return $this->belongsTo(TravelType::class);
    // }

    // /**
    //  * travelType
    //  *
    //  * @return BelongsTo<TravelImage, $this>
    //  */
    // public function travelImage(): BelongsTo
    // {
    //     return $this->belongsTo(TravelImage::class);
    // }

    // public function getTravelImageUrlAttribute(): ?string
    // {
    //     $travelImage = $this->travelImage;

    //     if (! $travelImage) {
    //         return null;
    //     }

    //     return $travelImage->getUrlAttribute();
    // }

    // /**
    //  * housings
    //  *
    //  * @return HasMany<TravelHousing, $this>
    //  */
    // public function housings(): HasMany
    // {
    //     return $this->hasMany(TravelHousing::class);
    // }

    /**
     * activities
     *
     * @return HasMany<Activity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * housings
     *
     * @return HasMany<Housing, $this>
     */
    public function housings(): HasMany
    {
        return $this->hasMany(Housing::class);
    }

    public function getDayEvents($day)
    {
        $activities = $this->activities()->get();
        $housings = $this->housings()->get();
        $travelEvents = [];

        foreach ($activities as $activity) {
            $travelEvents[] = [
                'name' => $activity->name,
                'start_date' => $activity->start_date,
                'end_date' => $activity->end_date,
                'start_time' => $activity->start_time,
                'end_time' => $activity->end_time,
                'latitude' => $activity->place_latitude,
                'longitude' => $activity->place_longitude,
                'place_name' => $activity->place_name,
                'type' => 'activity',
            ];
        }

        foreach ($housings as $housing) {
            $travelEvents[] = [
                'name' => $housing->name,
                'start_date' => $housing->start_date,
                'end_date' => $housing->end_date,
                'start_time' => $housing->start_time,
                'end_time' => $housing->end_time,
                'latitude' => $housing->place_latitude,
                'longitude' => $housing->place_longitude,
                'place_name' => $housing->place_name,
                'type' => 'housing',
            ];
        }
        $events = [];
        foreach ($travelEvents as $event) {
            if (empty($event['start_date']) || empty($event['end_date'])) {
                continue;
            }

            $startDate = Carbon::parse($event['start_date'])->startOfDay();
            $endDate = Carbon::parse($event['end_date'])->endOfDay();

            if ($day->between($startDate, $endDate)) {
                $events[] = [
                    'name' => $event['name'],
                    'start_time' => $event['start_time'],
                    'end_time' => $event['end_time'],
                    'latitude' => $event['latitude'],
                    'longitude' => $event['longitude'],
                    'place_name' => $event['place_name'],
                    'type' => $event['type'],
                ];
            }
        }

        usort($events, function ($a, $b) {
            $aTime = $a['start_time'];
            $bTime = $b['start_time'];

            return strcmp($aTime, $bTime);
        });

        return $events;
    }
}
