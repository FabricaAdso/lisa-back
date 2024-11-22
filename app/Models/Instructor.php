<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    
    //relaciones
    public function courses()
    {
        return $this->hasOne(Course::class);
    }
}
