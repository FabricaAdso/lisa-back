<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Environment extends Model
{
    //
  
    protected $fillable = ['name','capacity'];
    protected $allowIncluded = ['headquartes','environmentsArea'];


    public function headquartes(){
        return $this->hasMany(Headquarters::class);
        
    }

    public function environmentsArea(){
        return $this->hasMany(EnvironmentArea::class);
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
