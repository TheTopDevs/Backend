<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    /**
     * @return BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(UserRoles::class);
    }

    public static function getSomeAdmin(): Model
    {
        return User::with('role')->whereHas('role', function ($q) {
            $q->where('name', UserRoles::ADMIN);
        })->first();
    }

    public static function getSomeManager(): Model
    {
        return User::with('role')->whereHas('role', function ($q) {
            $q->where('name', UserRoles::MANAGER);
        })->first();
    }

    public static function getSomeJvPartner(): Model
    {
        return User::with('role')->whereHas('role', function ($q) {
            $q->where('name', UserRoles::JV_PARTNER);
        })->first();
    }

    public static function getSomeJvPartnerOwner(): Model
    {
        return User::with('role')->whereHas('role', function ($q) {
            $q->where('name', UserRoles::JV_PARTNER_OWNER);
        })->first();
    }


    /**
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role()->where('name', "=", $role)->exists();
    }

    public function isManagerRole(): bool
    {
        return $this->role()->where('name', '=', UserRoles::MANAGER)->exists();
    }

    public function isAdminRole(): bool
    {
        return $this->role()->where('name', '=', UserRoles::ADMIN)->exists();
    }

    public function isJvPartner(): bool
    {
        return $this->role()->where('name', '=', UserRoles::JV_PARTNER)->exists();
    }

    public function isJvPartnerOwner(): bool
    {
        return $this->role()->where('name', '=', UserRoles::JV_PARTNER_OWNER)->exists();
    }
}
