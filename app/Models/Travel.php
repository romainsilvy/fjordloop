<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Travel extends Model
{
    use SoftDeletes, HasFactory;

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
     *
     * @return User
     */
    public function owner(): User
    {
        return $this->belongsToMany(User::class)->withPivot('is_owner')->withTimestamps()->wherePivot('is_owner', true)->first();
    }

    // public function getTotalDays(): int
    // {
    //     if (! $this->start_date || ! $this->end_date) {
    //         return 0;
    //     }

    //     return (int) $this->start_date->diffInDays($this->end_date);
    // }

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

    // /**
    //  * invite
    //  *
    //  * @param  array<string>  $members
    //  */
    // public function inviteMembers(array $members, User $user): void
    // {
    //     foreach ($members as $member) {
    //         $this->invitations()->create([
    //             'email' => $member,
    //             'user_id' => $user->id,
    //         ]);
    //     }
    // }

    // /**
    //  * invitations
    //  *
    //  * @return HasMany<TravelInvitation, $this>
    //  */
    // public function invitations(): HasMany
    // {
    //     return $this->hasMany(TravelInvitation::class);
    // }

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
}
