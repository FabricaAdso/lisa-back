<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Session extends Model
{
    protected $allowIncluded = ['course.program', 'course'];
    protected $fillable = ['date', 'start_time', 'end_time', 'instructor_id', 'instructor2_id', 'course_id'];

    public function assistances()
    {
        return $this->hasMany(Assistance::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }
    public function instructor2()
    {
        return $this->belongsTo(Instructor::class, 'instructor2_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function scopeIncluded(Builder $query)
    {

        if (empty($this->allowIncluded) || empty(request('included'))) {
            return;
        }


        $relations = explode(',', request('included'));


        $allowIncluded = collect($this->allowIncluded);

        foreach ($relations as $key => $relationship) {

            if (!$allowIncluded->contains($relationship)) {
                unset($relations[$key]);
            }
        }
        $query->with($relations);
    }
}
