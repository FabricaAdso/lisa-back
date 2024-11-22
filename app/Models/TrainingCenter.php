<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class TrainingCenter extends Model
{
    //
    protected $fillable = ['name'];
    protected $allowIncluded = ['headquarters'];
    protected $allowFilter = ['name'];


    public function headquarters()
    {
        return $this->hasMany(Headquarters::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'role_training_center_user')
                    ->withPivot('role_id');
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

    public function scopeFilter(Builder $query)
    {
    
        if (empty($this->allowFilter) || !is_array($this->allowFilter) || !is_array(request('filter'))) {
            return $query;
        }
    
        $filters = request('filter');
        $allowFilter = collect($this->allowFilter);
    
        foreach ($filters as $filter => $value) {
            if (empty($value)) {
                continue; 
            }
    
            if ($filter === 'name' && $allowFilter->contains('name')) {
                $query->where('name', 'LIKE', '%' . $value . '%');
                continue;
            }
    
            if ($allowFilter->contains($filter)) {
                $query->where($filter, 'LIKE', '%' . $value . '%');
            }
        }
    
        return $query;
    }
    
}
