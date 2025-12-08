<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    protected $fillable = [
        'mobile_number',
        'otp_hash',
        'expires_at',
        'used',
        'ip_address',
        'verified_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'used' => 'boolean',
    ];

    public static function createForMobile(string $mobileNumber, string $otp, ?string $ipAddress = null): self
    {
        return self::create([
            'mobile_number' => $mobileNumber,
            'otp_hash' => hash('sha256', $otp),
            'expires_at' => now()->addMinutes(10), // 10 minute expiry
            'ip_address' => $ipAddress,
        ]);
    }

    public function verify(string $otp): bool
    {
        if ($this->used) {
            return false;
        }

        if ($this->expires_at->isPast()) {
            return false;
        }

        // Constant-time comparison
        $providedHash = hash('sha256', $otp);
        $isValid = hash_equals($this->otp_hash, $providedHash);

        if ($isValid) {
            $this->update([
                'used' => true,
                'verified_at' => now(),
            ]);
        }

        return $isValid;
    }

    public function scopeValid($query)
    {
        return $query->where('used', false)
                    ->where('expires_at', '>', now());
    }
}
