<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        'name',
        'education_level_id'
    ];

    protected $allowIncluded = [
        'educationLevel'
    ];

    protected $allowFilter = [
        'education_level'
    ];
    //{{api}}/programs?included=educationLevel&filter[education_level]=logo
    public function educationLevel()
    {
        return $this->belongsTo(EducationLevel::class);
    }

    public function trainingCenter ()
    {
        return $this->belongsTo(TrainingCenter::class);
    }
    
    public function courses()
    {
        return $this->hasMany(Course::class);
    }


    ///////////////

    public function scopeIncluded(Builder $query)
    {
       
        if(empty($this->allowIncluded)||empty(request('included'))){// validamos que la lista blanca y la variable included enviada a travez de HTTP no este en vacia.
            return;
        }

        
        $relations = explode(',', request('included')); //['posts','relation2']//recuperamos el valor de la variable included y separa sus valores por una coma

        //return $relations;

        $allowIncluded = collect($this->allowIncluded); //colocamos en una colecion lo que tiene $allowIncluded en este caso = ['posts','posts.user']

        foreach ($relations as $key => $relationship) { //recorremos el array de relaciones

            if (!$allowIncluded->contains($relationship)) {
                unset($relations[$key]);
            }
        }
        $query->with($relations); //se ejecuta el query con lo que tiene $relations en ultimas es el valor en la url de included

        //http://api.codersfree1.test/v1/categories?included=posts


    }

    ////////////

    public function scopeFilter(Builder $query)
    {
        if (empty($this->allowFilter) || empty(request('filter'))) {
            return;
        }
    
        $filters = request('filter');
        $allowFilter = collect($this->allowFilter);
    
        foreach ($filters as $filter => $value) {
            // Filtrar por nivel de educación (relación)
            if ($filter === 'education_level') {
                $query->whereHas('educationLevel', function ($q) use ($value) {
                    $q->where('name', 'LIKE', '%' . $value . '%');
                });
            }
    
            //otros campos de Program si están en allowFilter
            if ($allowFilter->contains($filter) && $filter !== 'education_level') {
                $query->where($filter, 'LIKE', '%' . $value . '%');
            }
        }
    }
    

}