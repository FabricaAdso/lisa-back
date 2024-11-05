<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assistance extends Model
{
    //
    protected $fillable = ['assistance', 'participant_id', 'session_id'];
    
    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function session ()
    {
        return $this->belongsTo(Session::class);
    }
}
