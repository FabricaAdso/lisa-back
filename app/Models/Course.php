<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'date_start',
        'date_end',
        'end_date_training_stage',
        'shift',
        'state',
        'stage',
        'program_id',
        'course_leader_id',
        'representative_id',
        'co_representative_id',
    ];

    protected $allowIncluded = [
        'program',
        'shifts',
        'apprentices.user',
        'environment.headquarters',
        'representative.user', // Relación con el aprendiz representante
        'co_representative.user', // Relación con el aprendiz co-representante
    ];

    protected $allowFilter = [
        'program_q'
    ];

    public function apprentices()
    {
        return $this->hasMany(Apprentice::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function environment()
    {
        return $this->belongsTo(Environment::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class);
    }
    public function representative()
    {
        return $this->belongsTo(Apprentice::class, 'representative_id');
    }

    public function co_representative()
    {
        return $this->belongsTo(Apprentice::class, 'co_representative_id');
    }


    ////
    public function scopeIncluded(Builder $query)
    {

        if (empty($this->allowIncluded) || empty(request('included'))) { // validamos que la lista blanca y la variable included enviada a travez de HTTP no este en vacia.
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
            // Filtrar por course code (relación con Course a través de Program)
            if ($filter === 'subjectForCourse' && $allowFilter->contains($filter)) {
                $query->whereHas('program.courses', function ($q) use ($value) {
                    $q->where('code', 'LIKE', '%' . $value . '%');
                });
            }
        }
    }
}
