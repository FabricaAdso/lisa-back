<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assistance extends Model
{
    //
    protected $fillable = ['assitance','session_id','apprentice_id'];

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
