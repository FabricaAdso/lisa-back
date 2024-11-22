<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apprentice extends Model
{
    //
    protected $fillable = ['state','user_id','course_id'];

    public function user ()
    {
        return $this->belongsTo(User::class);
    }

    public function course ()
    {
        return $this->belongsTo(Course::class);
    }

    public function assistances ()
    {
        return $this->hasMany(Assistance::class);
    }
}
