<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    //
    public function assistances()
    {
        return $this->hasMany(Assistance::class);
    }
}
