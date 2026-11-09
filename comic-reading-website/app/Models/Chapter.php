<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chapter extends Model
{
    use HasFactory;

    protected $fillable = [
        'comic_id',
        'number',
        'title',
        'views',
    ];

    /** Khi thêm/sửa chương thì cập nhật updated_at của truyện (để truyện nổi lên mục "mới cập nhật") */
    protected $touches = ['comic'];

    protected function casts(): array
    {
        return [
            // Để URL là chap-10 hoặc chap-10.5 thay vì chap-10.0
            'number' => 'float',
            'views'  => 'integer',
        ];
    }

    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }

    /** Các trang ảnh, đã sắp xếp theo thứ tự trang */
    public function images(): HasMany
    {
        return $this->hasMany(ChapterImage::class)->orderBy('order');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
