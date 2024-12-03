<?php

namespace App\Models;

use App\Services\Implementations\TokenServiceImpl;
use App\Services\TokenService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Apprentice extends Model
{
    //
    protected $fillable = ['state','user_id','course_id'];
    protected $allowIncluded = ['course.program.trainingCenter','user'];
    protected $allowFilter = ['course_'];
    
    public function user ()
    {
        return $this->belongsTo(User::class);
    }

    public function course ()
    {
        return $this->belongsTo(Course::class);
    }

    public function assistances ()
    {
        return $this->hasMany(Assistance::class);
    }


    public function scopeIncluded(Builder $query)
    {

        if (empty($this->allowIncluded) || empty(request('included'))) {
            return;
        }

        $relations = explode(',', request('included'));
        $allowIncluded = collect($this->allowIncluded);

        foreach ($relations as $key => $relationship) {

            if (!$allowIncluded->contains($relationship)) {
                unset($relations[$key]);
            }
        }
        $query->with($relations);
    }


   public function scopeByTrainingCenter(Builder $query)
    {
        $token_service = new TokenServiceImpl();
        $training_center_id = $token_service->getTrainingCenterIdFromToken();   
        
        return $query->whereHas('course.program', function($query) use ($training_center_id) {
            return $query->whereHas('trainingCenter', function ($query) use ($training_center_id) {
                $query->where('training_center_id', $training_center_id);
            });
        });
    }


    public function scopeFilter(Builder $query)
    {
     
        if (empty($this->allowFilter) || empty(request('filter'))) {
            return $query;
        }
    
        $filters = request('filter');
        $allowFilter = collect($this->allowFilter);
    
        foreach ($filters as $filter => $value) {
            
            if (empty($value)) {
                continue;
            }
         
            if ($filter === 'course_' && $allowFilter->contains($filter)) {
                $query->whereHas('course', function ($q) use ($value) {
                    $q->where('code', 'LIKE', '%' . $value . '%'); // Ajusta 'name' según el campo del modelo Course
                });
            }

            elseif ($allowFilter->contains($filter)) {
                $query->where($filter, 'LIKE', '%' . $value . '%');
            }
        }
    
        return $query;
    }
    
}
