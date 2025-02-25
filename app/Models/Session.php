<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Session extends Model
{
    protected $allowIncluded = ['course.program','instructor','course.environment','assistances.apprentice.user','course','course.program.subjects','rap'];
    protected $fillable = ['date','start_time','end_time','instructor_id','instructor2_id','course_id','rap_id'];

    protected $allowFilter = [
        'course_',
        'subject_',
        'rap_',
    ];

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
    
    public function rap ()
    {
        return $this->belongsTo(Rap::class);
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

    public function scopeFilter(Builder $query)
    {
        if (empty($this->allowFilter) || empty(request('filter'))) {
            return;
        }
    
        $filters = request('filter');
        $allowFilter = collect($this->allowFilter);
    
        foreach ($filters as $filter => $value) {
            // Filtrar por course (relación con Course)
            if ($filter === 'course_' && $allowFilter->contains($filter)) {
                $query->whereHas('course', function ($q) use ($value) {
                    $q->where('code', 'LIKE', '%' . $value . '%');
                });
            }
    
            // Filtrar por subject (relación encadenada Course -> Program -> Subject)
            if ($filter === 'subject_' && $allowFilter->contains($filter)) {
                $query->whereHas('course.program.subjects', function ($q) use ($value) {
                    $q->where('name', 'LIKE', '%' . $value . '%');
                });
            }
    
            // Filtrar por rap (relación con Rap)
            if ($filter === 'rap_' && $allowFilter->contains($filter)) {
                $query->whereHas('rap', function ($q) use ($value) {
                    $q->where('description', 'LIKE', '%' . $value . '%');
                });
            }
        }
    }

}
