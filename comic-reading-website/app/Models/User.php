<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // user | admin
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Các truyện người dùng đang theo dõi */
    public function followedComics(): BelongsToMany
    {
        return $this->belongsToMany(Comic::class, 'comic_follows')->withTimestamps();
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ReadingHistory::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
