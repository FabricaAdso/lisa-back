<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;


class Assistance extends Model
{
    //

    protected $fillable = ['assistance', 'session_id', 'apprentice_id'];
    protected $allowIncluded = [
        'apprentice',
        'apprentice.user',
        'session.instructor.user',
        'session.apprentice.user',
        'jutifications.aprobation'
    ];
    protected $allowFilter = ['assistance', 'session_id', 'apprentice_id'];

    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    public function apprentice()
    {
        return $this->belongsTo(Apprentice::class);
    }

    public function justifications()
    {
        return $this->hasMany(Justification::class);
    }

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
        $query->with($relations);
    }

}
