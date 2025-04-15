<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements CanResetPassword
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'firstname',
        'middlename',
        'lastname',
        'name',
        'email',
        'email1',
        'password',
        'role',
        'canId',
    ];

    protected static function booted()
    {
        static::saving(function ($user) {
            $user->name = trim(implode(' ', array_filter([
                $user->firstname,
                $user->middlename,
                $user->lastname,
            ])));
        });
    }

    // public function schoolYear(): BelongsTo
    // {
    //     return $this->belongsTo(Semester::class);
    // }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
        'password' => 'hashed',
    ];

    public function invoiceRecords(): HasMany
    {
        return $this->hasMany(InvoiceRecord::class);
    }

    public function getIsAdminAttribute(): bool
    {
        return $this->role === 'admin'; // Adjust according to your database structure
    }
}
