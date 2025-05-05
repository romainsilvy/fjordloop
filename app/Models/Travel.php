<?php

namespace App\Models;

use App\Models\Housing;
use App\Models\Activity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Travel extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

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

    public static function fromInvitation(string $token): ?Travel
    {
        return self::withoutGlobalScope('userIsMember')->whereHas('invitations', function (Builder $query) use ($token) {
            $query->where('token', $token);
        })->first();
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

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function housings(): HasMany
    {
        return $this->hasMany(Housing::class);
    }
}
