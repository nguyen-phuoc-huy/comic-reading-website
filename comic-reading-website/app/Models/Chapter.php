<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    public function comic()
    {
        return $this->belongsTo(Comic::class);
    }
    public function images()
    {
        return $this->hasMany(ChapterImage::class)->orderBy('order');
    }
}
