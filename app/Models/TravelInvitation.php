<?php

namespace App\Models;

use App\Models\User;
use App\Models\Travel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Mail\TravelInvitation as TravelInvitationMail;

class TravelInvitation extends Model
{
    protected $fillable = [
        'travel_id',
        'user_id',
        'email',
        'token',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($invitation) {
            $invitation->token = bin2hex(random_bytes(32));
        });

        static::created(function ($invitation) {
            $invitation->sendEmail();
        });
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

    /**
     * creator
     *
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sendEmail(): void
    {
        $creator = $this->creator()->firstOrFail();
        $travel = $this->travel()->firstOrFail();

        Mail::to($this->email)->send(new TravelInvitationMail($creator, $travel, $this->token));
    }
}
