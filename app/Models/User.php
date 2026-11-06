<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const CONTACT_CHANNEL_LABELS = [
        'email' => 'Имейл',
        'phone' => 'Телефон',
    ];

    public const ACCOUNT_STATUS_LABELS = [
        'active' => 'Активен',
        'suspended' => 'Временно спрян',
        'deactivated' => 'Деактивиран',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'preferred_contact_channel',
        'password',
        'role',
        'account_status',
        'suspended_until',
        'deactivated_at',
        'admin_metadata',
        'email_verified_at',
        'email_verification_code',
        'email_verification_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_code',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_expires_at' => 'datetime',
            'password' => 'hashed',
            'suspended_until' => 'datetime',
            'deactivated_at' => 'datetime',
            'admin_metadata' => 'array',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_last_used_step' => 'integer',
        ];
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function authoredTicketUpdates()
    {
        return $this->hasMany(TicketUpdate::class, 'author_id');
    }

    public function workflows()
    {
        return $this->hasMany(Workflow::class, 'client_user_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(ClientSubscription::class);
    }

    public function currentSubscription()
    {
        return $this->hasOne(ClientSubscription::class)
            ->whereIn('status', ['trial', 'active', 'past_due', 'paused'])
            ->latestOfMany();
    }

    public function usageRecords()
    {
        return $this->hasMany(UsageRecord::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAccessBlocked(): bool
    {
        if ($this->account_status === 'deactivated' || filled($this->deactivated_at)) {
            return true;
        }

        return $this->account_status === 'suspended'
            && (blank($this->suspended_until) || $this->suspended_until->isFuture());
    }

    public function accountStatusLabel(): string
    {
        if ($this->account_status === 'suspended' && filled($this->suspended_until) && $this->suspended_until->isPast()) {
            return 'Активен след изтичане на паузата';
        }

        return self::ACCOUNT_STATUS_LABELS[$this->account_status] ?? $this->account_status;
    }

    public function hasVerifiedEmail(): bool
    {
        return filled($this->email_verified_at);
    }

    public function hasTwoFactorEnabled(): bool
    {
        return filled($this->two_factor_secret) && filled($this->two_factor_confirmed_at);
    }

    public function preferredContactLabel(): string
    {
        return self::CONTACT_CHANNEL_LABELS[$this->preferred_contact_channel] ?? $this->preferred_contact_channel;
    }

    public function issueEmailVerificationCode(): string
    {
        $plainCode = (string) random_int(100000, 999999);

        $this->forceFill([
            'email_verification_code' => Hash::make($plainCode),
            'email_verification_expires_at' => now()->addMinutes(15),
            'email_verified_at' => null,
        ])->save();

        return $plainCode;
    }

    public function verificationCodeIsValid(string $code): bool
    {
        return filled($this->email_verification_code)
            && filled($this->email_verification_expires_at)
            && $this->email_verification_expires_at->isFuture()
            && Hash::check($code, $this->email_verification_code);
    }

    public function markEmailAsVerified(): void
    {
        $this->forceFill([
            'email_verified_at' => now(),
            'email_verification_code' => null,
            'email_verification_expires_at' => null,
        ])->save();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
