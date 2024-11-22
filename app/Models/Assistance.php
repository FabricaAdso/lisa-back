<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assistance extends Model
{
    //
    public function session ()
    {
        return $this->belongsTo(Session::class);
    }

    public function apprentice ()
    {
        return $this->belongsTo(Apprentice::class);
    }

    public function justifications ()
    {
        return $this->hasMany(Justification::class);
    }
}
