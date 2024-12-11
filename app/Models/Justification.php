<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

use Illuminate\Database\Eloquent\Builder;

class Justification extends Model
{
    protected $fillable = [
        'assistance_id',
        'file_url',
        'description'
    ];
    protected $allowFilter = ['assistance_id', 'file_url', 'description'];
    protected $allowIncluded = ['assistance.session.instructor.user','assistance.session','aprobation','assistance.session.course','assistance.apprentice.user'];

    //
    public function aprobation ()
    {
        return $this->hasOne(Aprobation::class);
    }
    public function assistance ()
    {
        return $this->belongsTo(Assistance::class);
    }

    public function scopeFilter(Builder $query)
    {
        // If no allowed filters are set or no filter is requested, exit the method
        if (empty($this->allowFilter) || empty(request('filter'))) {
            return;
        }
    
        // Get the filter parameters from the request
        $filters = request('filter');
        
        // Convert the allowed filters to a collection for easy checking
        $allowFilter = collect($this->allowFilter);
    
        // Iterate through each filter in the request
        foreach ($filters as $filter => $value) {
            // Check if the current filter is in the list of allowed filters
            if ($allowFilter->contains($filter)) {
                // Apply a LIKE query where the specified column contains the filter value
                $query->where($filter, 'LIKE', '%' . $value . '%');
            }
        }
    }

    public function scopeIncluded(Builder $query)
    {
       
        if(empty($this->allowIncluded)||empty(request('included'))){// validamos que la lista blanca y la variable included enviada a travez de HTTP no este en vacia.
            return;
        }

        
        $relations = explode(',', request('included')); //['posts','relation2']//recuperamos el valor de la variable included y separa sus valores por una coma

      

        $allowIncluded = collect($this->allowIncluded); //colocamos en una colecion lo que tiene $allowIncluded en este caso = ['posts','posts.user']

        foreach ($relations as $key => $relationship) { //recorremos el array de relaciones

            if (!$allowIncluded->contains($relationship)) {
                unset($relations[$key]);
            }
        }
         $query->with($relations); //se ejecuta el query con lo que tiene $relations en ultimas es el valor en la url de included
    }

}
