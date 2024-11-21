<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Environment extends Model
{
    //

    protected $fillable = ['name', 'capacity'];
    protected $allowIncluded = ['headquarters'];
    protected $allowFilter = ['headquarters_'];


    public function headquarters()
    {
        return $this->belongsTo(Headquarters::class);
    }
    
    public function courses ()
    {
        return $this->hasMany(Course::class);
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
        if (empty($this->allowFilter) || empty(request('filter'))) {
            return;
        }

        $filters = request('filter');
        $allowFilter = collect($this->allowFilter);

        foreach ($filters as $filter => $value) {
            // Filtrar por Area de Ambiente y Sede
            if ($filter === 'environment_area') {
                $query->whereHas('environmentArea', function ($q) use ($value) {
                    $q->where('name', 'LIKE', '%' . $value . '%');
                });
            }
            if ($filter === 'headquarters_') {
                $query->whereHas('headquarters', function ($q) use ($value) {
                    $q->where('name', 'LIKE', '%' . $value . '%');
                });
            }

            //otros campos de Program si están en allowFilter
            if ($allowFilter->contains($filter) && $filter !== 'environment_area' &&  $filter !== 'headquarters_') {
                $query->where($filter, 'LIKE', '%' . $value . '%');
            }
        }
    }
}
