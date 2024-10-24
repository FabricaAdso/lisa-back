<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    public function shifts()
    {
        return $this->belongsToMany(Shift::class);
    }
}
