<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Regional extends Model
{
    //
    public function trainingCenters ()
    {
        return $this->hasMany(TrainingCenter::class);
    }
}
