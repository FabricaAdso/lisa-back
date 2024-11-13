<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apprentice extends Model
{
    //
    protected $fillable = ['start_date', 'end_date', 'user_id','estate', 'course_id'];

    public function assistances()
    {
        return $this->hasMany(Assistance::class);
    }

    public function user ()
    {
        return $this->belongsTo(User::class);
    }

    public function course ()
    {
        return $this->belongsTo(Course::class);
    }

}
