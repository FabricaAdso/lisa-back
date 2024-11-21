<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Justification extends Model
{
    //
    public function aprobations ()
    {
        return $this->hasMany(Aprobation::class);
    }

    public function assistance ()
    {
        return $this->belongsTo(Assistance::class);
    }
}
