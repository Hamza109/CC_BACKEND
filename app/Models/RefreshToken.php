<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RefreshToken extends Model
{
    protected $fillable = [
        'mobile_number',
        'token_hash',
        'expires_at',
        'revoked',
        'ip_address',
        'user_agent',
        'last_used_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'revoked' => 'boolean',
    ];

    public static function createForMobile(string $mobileNumber, ?string $ipAddress = null, ?string $userAgent = null): array
    {
        $token = Str::random(64);
        $tokenHash = hash('sha256', $token);

        self::create([
            'mobile_number' => $mobileNumber,
            'token_hash' => $tokenHash,
            'expires_at' => now()->addDays(30), // 30 day expiry
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);

        return ['token' => $token, 'hash' => $tokenHash];
    }

    public function verify(string $token): bool
    {
        if ($this->revoked) {
            return false;
        }

        if ($this->expires_at->isPast()) {
            return false;
        }

        $providedHash = hash('sha256', $token);
        return hash_equals($this->token_hash, $providedHash);
    }

    public function revoke(): void
    {
        $this->update([
            'revoked' => true,
        ]);
    }

    public function scopeValid($query)
    {
        return $query->where('revoked', false)
                    ->where('expires_at', '>', now());
    }
}
