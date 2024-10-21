<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Departament extends Model
{
    //
    protected $fillable = ['name'];

    public function municipalities(){
        return $this->belongsTo(Municipality::class);
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
