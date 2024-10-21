<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvironmentArea extends Model
{
    //
    public function environments(){
        return $this->belongsTo(Environment::class);
    }
}
