<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Regional extends Model
{
    //
    protected $fillable = ['code','name'];

    protected $allowIncluded = ['trainingCenters'];

    protected $allowFilter = ['training'];

    public function trainingCenters ()
    {
        return $this->hasMany(TrainingCenter::class);
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
            // Filtrar por centro de formacion
            if ($filter === 'training') {
                $query->whereHas('trainingCenters', function ($q) use ($value) {
                    $q->where('name', 'LIKE', '%' . $value . '%');
                });
            }

            if ($allowFilter->contains($filter) && $filter !== 'training') {
                $query->where($filter, 'LIKE', '%' . $value . '%');
            }
        }
    }
}
