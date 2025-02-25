<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Rap extends Model
{
    //
    protected $fillable = ['description', 'subject_id', 'number_hours'];
    protected $allowIncluded = ['subject', 'sessions'];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function sessions()
    {
        return $this->hasMany(Session::class);
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
