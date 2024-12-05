<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Instructor extends Model
{
    protected $fillable = [
        'user_id',
        'training_center_id',
        'state'    
    ];

    //
  
    protected $allowIncluded = [''];

    public function aprobations ()
    {
        return $this->hasMany(Aprobation::class);
    }

    public function sessions ()
    {
        return $this->hasMany(Session::class, 'instructor_id');
    }

    public function user ()
    {
        return $this->belongsTo(User::class);
    }

    public function trainingCenter ()
    {
        return $this->belongsTo(TrainingCenter::class);
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
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
