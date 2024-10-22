<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Headquarters extends Model
{
    //

    protected $fillable = ['name','adress','opening_time','closing_time','municipality_id','training_center_id'];
    protected $allowIncluded = ['trainingCenter','municipality'];

    public function trainingCenter(){
        return $this->belongsTo(TrainingCenter::class);
    }

    public function municipality(){
        return $this->belongsTo(Municipality::class);
    }


    public function environments(){
        return $this->hasMany(Environment::class);
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
