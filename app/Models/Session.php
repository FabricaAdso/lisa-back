<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Session extends Model
{
    protected $allowIncluded = ['course.program','instructor','course.environment','assistances.apprentice','course'];
    protected $fillable = ['date','start_time','end_time','instructor_id','instructor2_id','course_id'];

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

    public function scopeFilter(Builder $query)
    {
        // If no allowed filters are set or no filter is requested, exit the method
        if (empty($this->allowFilter) || empty(request('filter'))) {
            return;
        }
    
        // Get the filter parameters from the request
        $filters = request('filter');
        
        // Convert the allowed filters to a collection for easy checking
        $allowFilter = collect($this->allowFilter);
    
        // Iterate through each filter in the request
        foreach ($filters as $filter => $value) {
            // Check if the current filter is in the list of allowed filters
            if ($allowFilter->contains($filter)) {
                // Apply a LIKE query where the specified column contains the filter value
                $query->where($filter, 'LIKE', '%' . $value . '%');
            }
        }
    }
}
