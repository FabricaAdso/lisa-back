<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    //

    protected $fillable = ['name', 'number_hours', 'program_id'];
    
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function sessions ()
    {
        return $this->hasMany(Session::class);
    }
}
