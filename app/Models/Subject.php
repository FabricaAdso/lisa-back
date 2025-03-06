<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    //
    use HasFactory;

    protected $fillable = ['name', 'total_number_hours', 'program_id','user_id','updated_porcentage','percentage'];
    protected $allowIncluded = ['program', 'raps','user','program.courses'];
    protected $allowFilter = [
        'subjectForCourse'
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function raps ()
    {
        return $this->hasMany(Rap::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
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
        if (empty($this->allowFilter) || empty(request('filter'))) {
            return;
        }

        $filters = request('filter');
        $allowFilter = collect($this->allowFilter);

        foreach ($filters as $filter => $value) {
            // Filtrar por course (relación con Course)
            if ($filter === 'subjectForCourse' && $allowFilter->contains($filter)) {
                $query->whereHas('program.courses', function ($q) use ($value) {
                    $q->where('code', 'LIKE', '%' . $value . '%');
                });
            }
        }
    }
}
