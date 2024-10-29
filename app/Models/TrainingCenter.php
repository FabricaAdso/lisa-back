<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TrainingCenter extends Model
{
    //
    protected $fillable = ['name'];

    public function headquarters()
    {
        return $this->hasMany(Headquarters::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    // public function municipality()
    // {
    //     return $this->belongsTo(Municipality::class);
    // }


}
