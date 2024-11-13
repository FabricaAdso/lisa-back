<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    //
    protected $fillable = ['start_date', 'end_date', 'user_id', 'course_id','training_center_id'];
    protected $allowIncluded = ['courses','user','trainingCenter'];

    public function sessions ()
    {
        return $this->hasMany(Session::class);
    }

    public function user ()
    {
        return $this->belongsTo(User::class);
    }

    public function courses ()
    {
        return $this->belongsToMany(Course::class,'course_instructor', 'instructor_id', 'course_id');
    }

    public function trainingCenter ()
    {
        return $this->belongsTo(TrainingCenter::class);
    }

    public function scopeIncluded(Builder $query)
    {

        if (empty($this->allowIncluded) || empty(request('included'))) {
            return;
        }


        $relations = explode(',', request('included'));

        // return $relations;

        $allowIncluded = collect($this->allowIncluded);

        foreach ($relations as $key => $relationship) {

            if (!$allowIncluded->contains($relationship)) {
                unset($relations[$key]);
            }
        }
        $query->with($relations);
    }

}
