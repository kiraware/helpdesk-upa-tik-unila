<?php

namespace App\Models;

use App\Enums\UserEntity;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username_sso',
        'name',
        'email',
        'identity_number',
        'phone',
        'avatar_path',
        'role',
        'entity',
        'division_id',
        'department_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
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
            'role' => UserRole::class,
            'entity' => UserEntity::class,
        ];
    }

    /**
     * Relasi: User (Staff) milik satu Penanggung Jawab
     */
    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Relasi: User milik satu Departemen
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relasi: Tiket yang DIBUAT oleh user ini
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    /**
     * Relasi: Tiket yang DITUGASKAN ke user ini (khusus Staff/Admin)
     */
    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function routeNotificationForWhatsapp($notification)
    {
        return $this->phone;
    }

    /**
     * Accessor untuk mendapatkan URL foto profil yang valid
     */
    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar_path
            ? asset('storage/'.$this->avatar_path)
            : 'https://ui-avatars.com/api/?name='.urlencode($this->name);
    }
}
