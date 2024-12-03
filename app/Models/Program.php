<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        'code',
        'version',
        'name',
        'education_level_id',
        'training_center_id'
    ];
    protected $allowIncluded = [
        'educationLevel',
        'trainingCenter'
    ];
    protected $allowFilter = [
        'name',
        'training_Center'
    ];

    // Relaciones
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


    // Scope

    public function scopeIncluded(Builder $query)
    {
       
        if(empty($this->allowIncluded)||empty(request('included'))){
            return;
        }

        
        $relations = explode(',', request('included')); 

        //return $relations;

        $allowIncluded = collect($this->allowIncluded); 

        foreach ($relations as $key => $relationship) { 
            if (!$allowIncluded->contains($relationship)) {
                unset($relations[$key]);
            }
        }
        $query->with($relations); 
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
            if ($filter === 'training_Center') {
                $query->whereHas('trainingCenter', function ($q) use ($value) {
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