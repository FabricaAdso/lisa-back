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
        'course.program.subjects.id',
        'instructor_',
        'date_from',
        'date_to'
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

    // filtro para curso por código utilizando la clave 'course_'
    if (isset($filters['course_']) && $allowFilter->contains('course_')) {
        $query->whereHas('course', function ($q) use ($filters) {
            $q->where('code', 'LIKE', '%' . $filters['course_'] . '%');
        });
    }

    // filtro para instructor utilizando la clave 'instructor_'
    if (isset($filters['instructor_']) && $allowFilter->contains('instructor_')) {
        $query->whereHas('instructor.user', function ($q) use ($filters) {
            // nombre del instructor es el campo a filtrar.
            $q->where('name', 'LIKE', '%' . $filters['instructor_'] . '%');
        });
    }

    // filtro para rap utilizando la clave 'rap_'
    if (isset($filters['rap_']) && $allowFilter->contains('rap_')) {
        $query->whereHas('rap', function ($q) use ($filters) {
            // descripción del rap es el campo a filtrar.
            $q->where('description', 'LIKE', '%' . $filters['rap_'] . '%');
        });
    }

    // filtro para competencia (subject) utilizando la clave 'subject_'
    if (isset($filters['subject_']) && $allowFilter->contains('subject_')) {
        $query->whereHas('course.program.subjects', function ($q) use ($filters) {
            // filtra por el nombre de la competencia
            $q->where('name', 'LIKE', '%' . $filters['subject_'] . '%');
        });
    }

    if (isset($filters['date_from']) && $allowFilter->contains('date_from')) {
        $query->where('date', '>=', $filters['date_from']);
    }

    // Nuevo: Filtro por fecha "hasta"
    if (isset($filters['date_to']) && $allowFilter->contains('date_to')) {
        $query->where('date', '<=', $filters['date_to']);
    }


    // procesar otros filtros que tengan puntos en la clave
    foreach ($filters as $filter => $value) {
        // ignorar los filtros ya procesados
        if (in_array($filter, ['course_', 'pending', 'past', 'instructor_', 'rap_', 'subject_'])) {
            continue;
        }
        if (!$allowFilter->contains($filter)) {
            continue;
        }
        if (strpos($filter, '.') !== false) {
            $parts = explode('.', $filter);
            $property = array_pop($parts);
            $relations = implode('.', $parts);
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
            $query->where($filter, $value);
        }
    }

    // filtros especiales para el estado de la sesión (basados en la fecha)
    if (isset($filters['pending']) && $filters['pending'] === 'true') {
        $query->where('date', '>=', now());
    }
    if (isset($filters['past']) && $filters['past'] === 'true') {
        $query->where('date', '<', now());
    }

    return $query;
}

public function scopeLeaderFilter(Builder $query, array $filters, $courseIds)
{
    // Filtrar por código de curso usando 'course_'
    if (isset($filters['course_'])) {
        $query->whereHas('course', function ($q) use ($filters) {
            $q->where('code', 'LIKE', '%' . $filters['course_'] . '%');
        });
    }

    // Filtrar por rap usando 'rap_'
    if (isset($filters['rap_'])) {
        $query->whereHas('rap', function ($q) use ($filters) {
            $q->where('description', 'LIKE', '%' . $filters['rap_'] . '%');
        });
    }

    // Filtrar por subject usando 'subject_'
    if (isset($filters['subject_'])) {
        $query->whereHas('course.program.subjects', function ($q) use ($filters) {
            $q->where('name', 'LIKE', '%' . $filters['subject_'] . '%');
        });
    }

    // Filtrar por fechas
    if (isset($filters['date_from'])) {
        $query->where('date', '>=', $filters['date_from']);
    }
    if (isset($filters['date_to'])) {
        $query->where('date', '<=', $filters['date_to']);
    }

    // Aquí se aplica el filtro para que solo traiga sesiones de cursos donde el usuario es líder
    $query->whereIn('course_id', $courseIds);

    return $query;
}






}
