<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Headquarters extends Model
{
    //

    protected $fillable = ['name','adress','opening_time','closing_time'];
    protected $allowIncluded = ['trainingCenters'];

    public function trainigCenters(){
        return $this->hasMany(TrainingCenter::class);
    }

    public function environments(){
        return $this->belongsTo(Environment::class);
    }

    public function scopeIncluded(Builder $query)
    {

        if(empty($this->allowIncluded)||empty(request('included'))){
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
