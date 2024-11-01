<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'code',
        'date_start',
        'date_end',
        'program_id'
    ];

    protected $allowIncluded = [
        'program',
        'shifts'
    ];

    protected $allowFilter = [
        'program_q'
    ];
    ////
    public function participants()
    {
        return $this->hasMany(Participant::class);
    }
    
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class);
    }
    ////
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
    ////
    public function scopeFilter(Builder $query)
    {
        if (empty($this->allowFilter) || empty(request('filter'))) {
            return;
        }
    
        $filters = request('filter');
        $allowFilter = collect($this->allowFilter);
    
        foreach ($filters as $filter => $value) {
            // Filtrar por el nombre del programa relacionado
            if ($filter === 'program_q') {
                $query->whereHas('program', function($q) use ($value) {
                    $q->where('name', 'LIKE', '%' . $value . '%');
                });
            }
    
            //filtros para la tabla de cursos
            if ($allowFilter->contains($filter) && $filter !== 'program_q') {
                $query->where($filter, 'LIKE', '%' . $value . '%');
            }
        }
    }
    
}
