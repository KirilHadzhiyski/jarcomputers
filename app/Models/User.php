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
        'viber' => 'Viber',
        'whatsapp' => 'WhatsApp',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'preferred_contact_channel',
        'password',
        'role',
        'email_verified_at',
        'email_verification_code',
        'email_verification_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_verification_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_expires_at' => 'datetime',
            'password' => 'hashed',
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

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasVerifiedEmail(): bool
    {
        return filled($this->email_verified_at);
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
