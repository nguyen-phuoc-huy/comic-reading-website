<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comic extends Model
{
    protected $fillable = ['author_id', 'title', 'slug', 'other_names', 'description', 'cover', 'status', 'views'];
    public function author()
    {
        return $this->belongsTo(Author::class);
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }
    public function chapters()
    {
        return $this->hasMany(Chapter::class)->orderByDesc('number');
    }
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    public function followers()
    {
        return $this->belongsToMany(User::class, 'comic_follows')->withTimestamps();
    }
}
