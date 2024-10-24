<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    //
    protected $fillable = ['name'];
    protected $allowIncluded = ['departament'];

    public function departament()
    {
        return $this->belongsTo(Departament::class);
    }

    public function trainingCenters()
    {
        return $this->hasMany(TrainingCenter::class);
    }

    public function headquarters()
    {
        return $this->hasMany(Headquarters::class);
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
