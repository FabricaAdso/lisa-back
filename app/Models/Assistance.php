<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Assistance extends Model
{
    //
    protected $fillable = ['assitance','session_id','apprentice_id'];
    protected $allowIncluded = ['apprentice','apprentice.user'];

    public function session ()
    {
        return $this->belongsTo(Session::class);
    }

    public function apprentice ()
    {
        return $this->belongsTo(Apprentice::class);
    }

    public function justifications ()
    {
        return $this->hasMany(Justification::class);
    }

    public function scopeIncluded(Builder $query)
    {

        if (empty($this->allowIncluded) || empty(request('included'))) {
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
