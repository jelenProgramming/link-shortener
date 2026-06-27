<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Link extends Model
{
    protected $fillable = ['slug', 'original_url'];

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }
}
