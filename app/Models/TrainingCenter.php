<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TrainingCenter extends Model
{
    //
    protected $fillable = ['name'];
    protected $allowIncluded = ['headquarters'];

    public function headquarters()
    {
        return $this->hasMany(Headquarters::class);
    }

    public function programs ()
    {
        return $this->hasMany(Program::class);
    }

    public function instructors ()
    {
        return $this->hasMany(Instructor::class);
    }

    public function regional ()
    {
        return $this->belongsTo(Regional::class);
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
