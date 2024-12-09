<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

use Illuminate\Database\Eloquent\Builder;

class Assistance extends Model
{
    //

    protected $fillable = ['assistance', 'session_id', 'apprentice_id'];
    protected $allowIncluded = ['apprentice', 'apprentice.user', 'session.instructor','session'];
    protected $allowFilter = ['assistance', 'session_id', 'apprentice_id'];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function apprentice()
    {
        return $this->belongsTo(Apprentice::class);
    }

    public function justifications()
    {
        return $this->hasMany(Justification::class);
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
