<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    //
    protected $fillable = ['name','departament_id'];
   // protected $allowIncluded = ['departaments'];

    public function departaments(){
        return $this->hasMany(Departament::class);
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
