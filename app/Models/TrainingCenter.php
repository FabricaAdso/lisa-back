<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainingCenter extends Model
{
    //
    public function headquartes(){
        return $this->belongsTo(Headquarters::class);
    }
}
