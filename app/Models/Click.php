<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Click extends Model
{
    protected $fillable = ['link_id', 'ip', 'referer', 'user_agent'];

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }
}
