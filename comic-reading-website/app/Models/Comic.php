<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comic extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'author_id',
        'title',
        'slug',
        'other_names',
        'description',
        'cover',
        'status', // ongoing | completed | dropped
        'views',
    ];

    protected function casts(): array
    {
        return [
            'views' => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    /** Bảng trung gian: category_comic */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /** Mặc định sắp xếp chương mới nhất lên đầu */
    public function chapters(): HasMany
    {
        return $this->hasMany(Chapter::class)->orderByDesc('number');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /** Những người theo dõi truyện (bảng comic_follows) */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'comic_follows')->withTimestamps();
    }

    public function readingHistories(): HasMany
    {
        return $this->hasMany(ReadingHistory::class);
    }
}
