<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aprobation extends Model
{
    protected $fillable = [
        'state',
        'motive',
        'justification_id',
        'instructor_id'
    ];
    //
    public function justification ()
    {
        return $this->belongsTo(Justification::class);
    }

    public function instructor ()
    {
        return $this->belongsTo(Instructor::class);
    }
}
