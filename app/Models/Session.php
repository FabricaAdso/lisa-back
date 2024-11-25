<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    protected $fillable = [
        'date',
        'start_time',
        'end_time',
        'instructor_id',
        'course_id'
    ];
    //
    protected $fillable = ['date','start_time','end_time','instructor_id','course_id'];

    public function assistances ()
    {
        return $this->hasMany(Assistance::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
