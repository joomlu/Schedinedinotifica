<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
        'structure_id',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relationships

    /**
     * Get the structure associated with this user.
     */
    public function structure()
    {
        return $this->belongsTo(Estructura::class, 'structure_id');
    }

    // Role helper methods

    /**
     * Check if user is superadmin (can see everything).
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if user is admin (satellite - manages hotels).
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is hotel owner (manages hotel staff).
     */
    public function isHotelOwner(): bool
    {
        return $this->role === 'hotel_owner';
    }

    /**
     * Check if user is hotel staff (secretary, receptionist).
     */
    public function isHotelStaff(): bool
    {
        return $this->role === 'hotel_staff';
    }

    /**
     * Check if user can manage structures.
     */
    public function canManageStructures(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    /**
     * Check if user can manage users.
     */
    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->isHotelOwner();
    }

    /**
     * Get structures accessible by this user.
     * - superadmin: all
     * - admin: their assigned structure (hotel/satellite)
     * - hotel_owner/staff: their assigned structure
     */
    public function getAccessibleStructures()
    {
        if ($this->isSuperAdmin()) {
            return Estructura::all();
        }

        if ($this->structure_id) {
            return Estructura::where('id', $this->structure_id)->get();
        }

        return collect();
    }

    /**
     * Get customers accessible by this user.
     */
    public function getAccessibleCustomers()
    {
        if ($this->isSuperAdmin()) {
            return Customers::all();
        }

        if ($this->structure_id) {
            return Customers::where('structure_id', $this->structure_id)->get();
        }

        return collect();
    }

    // Query scopes

    /**
     * Scope to filter structures based on user role.
     */
    public function scopeForUser($query, User $user)
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->structure_id) {
            return $query->where('structure_id', $user->structure_id);
        }

        return $query->whereRaw('1 = 0'); // Return empty
    }
}
