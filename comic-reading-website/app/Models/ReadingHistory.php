<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReadingHistory extends Model
{
    public function comic()
    {
        return $this->belongsTo(Comic::class);
    }
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
