<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Headquarters extends Model
{
    //

    protected $fillable = ['name', 'adress', 'opening_time', 'closing_time', 'municipality_id', 'training_center_id'];
    protected $allowIncluded = ['trainingCenter', 'municipality'];
    protected $allowFilter = ['training_Center', 'municipality_'];

    public function trainingCenter()
    {
        return $this->belongsTo(TrainingCenter::class);
    }

    public function environments()
    {
        return $this->hasMany(Environment::class);
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
            // Filtrar por Centro De Formacion
            if ($filter === 'training_Center') {
                $query->whereHas('trainingCenter', function ($q) use ($value) {
                    $q->where('name', 'LIKE', '%' . $value . '%');
                });
            }
            if ($filter === 'municipality_') {
                $query->whereHas('municipality', function ($q) use ($value) {
                    $q->where('name', 'LIKE', '%' . $value . '%');
                });
            }

            //otros campos de Program si están en allowFilter
            if ($allowFilter->contains($filter) && $filter !== 'training_Center' &&  $filter !== 'municipality_') {
                $query->where($filter, 'LIKE', '%' . $value . '%');
            }
        }
    }
}
