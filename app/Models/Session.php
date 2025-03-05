<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Session extends Model
{
    protected $allowIncluded = ['course.program','instructor','course.environment','assistances.apprentice.user', 'instructor.user', 'course','course.program.subjects','rap', 'course.subject', 'rap.subject'];
    protected $fillable = ['date','start_time','end_time','instructor_id','instructor2_id','course_id','rap_id'];

    protected $allowFilter = [
        'course_',
        'subject_',
        'rap_',
        'pending',
        'past',
        'course.program.subjects.id'
    ];

    public function assistances()
    {
        return $this->hasMany(Assistance::class);
    }

    public function instructor()
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }
    public function instructor2()
    {
        return $this->belongsTo(Instructor::class, 'instructor2_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function rap ()
    {
        return $this->belongsTo(Rap::class);
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

    public function scopeFilter(Builder $query)
    {
        if (empty($this->allowFilter) || empty(request('filter'))) {
            return $query;
        }

        $filters = request('filter');
    $allowFilter = collect($this->allowFilter);

    foreach ($filters as $filter => $value) {
        // verificar que el filtro este permitido
        if (!$allowFilter->contains($filter)) {
            continue;
        }

        // si la clave contiene un punto, se trata de relaciones anidadas
        if (strpos($filter, '.') !== false) {
            $parts = explode('.', $filter);
            $property = array_pop($parts);
            $relations = implode('.', $parts);

            // si es id y el valor es numerico, hacemos comparación exacta
            if ($property === 'id' && is_numeric($value)) {
                $query->whereHas($relations, function ($q) use ($property, $value) {
                    $q->where($property, $value);
                });
            } else {
                $query->whereHas($relations, function ($q) use ($property, $value) {
                    $q->where($property, 'LIKE', '%' . $value . '%');
                });
            }
        } else {
            // Filtro directo en la columna
            $query->where($filter, $value);
        }
    }

    // Filtros especiales para 'pending' y 'past'
    if (isset($filters['pending']) && $filters['pending'] === 'true') {
        $query->where('date', '>=', now());
    }
    if (isset($filters['past']) && $filters['past'] === 'true') {
        $query->where('date', '<', now());
    }

    return $query;
    }
}
