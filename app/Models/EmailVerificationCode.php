<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailVerificationCode extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'code',
        'expires_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the verification code.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter only valid (non-expired) codes.
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Generate a new verification code for a given user.
     * Deletes any previous codes for that user.
     */
    public static function generateFor(User $user): self
    {
        // Remove any existing codes for this user
        static::where('user_id', $user->id)->delete();

        return static::create([
            'user_id' => $user->id,
            'code' => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'expires_at' => now()->addMinutes(15),
            'created_at' => now(),
        ]);
    }
}
