<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EnvironmentArea extends Model
{
    //
    protected $fillable = ['name'];
   

    public function environments(){
        return $this->hasMany(Environment::class);
    }
}
