<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'iframe_user_token',
        'firma_id',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'iframe_token_created_at' => 'datetime',
            'iframe_token_last_used' => 'datetime',
        ];
    }

    /**
     * Get the firma that owns the user.
     */
    public function firma(): BelongsTo
    {
        return $this->belongsTo(Firma::class);
    }

    /**
     * Get all projects the user has access to.
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class)->withTimestamps();
    }

    /**
     * Get all projects created by the user.
     */
    public function createdProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    /**
     * Get all tickets created by the user.
     */
    public function createdTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'created_by');
    }

    /**
     * Get all tickets assigned to the user.
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    /**
     * Check if user is a developer.
     */
    public function isDeveloper(): bool
    {
        return $this->role->isDeveloper();
    }

    /**
     * Check if user is a customer.
     */
    public function isCustomer(): bool
    {
        return $this->role->isCustomer();
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Generate secure iframe user token
     */
    public function generateIframeUserToken(): string
    {
        do {
            $token = Str::random(64);
        } while (self::where('iframe_user_token', $token)->exists());

        $this->update([
            'iframe_user_token' => $token,
            'iframe_token_created_at' => now()
        ]);
        
        return $token;
    }

    /**
     * Find user by iframe credentials with security checks
     */
    public static function findByIframeCredentials(string $token, string $email): ?User
    {
        return self::where('iframe_user_token', $token)
                  ->where('email', $email)
                  ->whereNotNull('iframe_user_token') // Token muss existieren
                  ->with(['firma'])
                  ->first();
    }

    /**
     * Track token usage for security monitoring
     */
    public function markIframeTokenUsed(): void
    {
        $this->update(['iframe_token_last_used' => now()]);
    }

    /**
     * Revoke iframe token (security feature)
     */
    public function revokeIframeToken(): void
    {
        $this->update([
            'iframe_user_token' => null,
            'iframe_token_created_at' => null,
            'iframe_token_last_used' => null
        ]);
    }

    /**
     * Developer brauchen keine Firma-Zuordnung
     */
    public function requiresFirma(): bool
    {
        return $this->role === UserRole::CUSTOMER;
    }

    /**
     * Validierung ob User korrekt konfiguriert ist
     */
    public function isProperlyConfigured(): bool
    {
        // Developer: Firma optional
        if ($this->role === UserRole::DEVELOPER) {
            return true;
        }
        
        // Customer: Firma required
        return $this->firma_id !== null;
    }
}
