<?php

namespace App\Models;

use App\Services\Implementations\TokenServiceImpl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Environment extends Model
{
    //
    use HasFactory;

    protected $fillable = ['name', 'capacity', 'headquarters_id', 'knowledge_network_id'];
    protected $allowIncluded = ['headquarters', 'knowledgeNetwork'];
    protected $allowFilter = ['headquarters_'];


    public function headquarters()
    {
        return $this->belongsTo(Headquarters::class);
    }

    public function knowledgeNetwork()
    {
        return $this->belongsTo(KnowledgeNetwork::class);
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

    public function scopeFilter(Builder $query)
    {
        if (empty($this->allowFilter) || empty(request('filter'))) {
            return;
        }

        $filters = request('filter');
        $allowFilter = collect($this->allowFilter);

        foreach ($filters as $filter => $value) {
            // Filtrar por Area de Ambiente y Sede
            if ($filter === 'headquarters_') {
                $query->whereHas('headquarters', function ($q) use ($value) {
                    $q->where('name', 'LIKE', '%' . $value . '%');
                });
            }

            //otros campos de Program si están en allowFilter
            if ($allowFilter->contains($filter) &&  $filter !== 'headquarters_') {
                $query->where($filter, 'LIKE', '%' . $value . '%');
            }
        }
    }

    public function scopeByTrainingCenter($query)
    {
        $token_service = new TokenServiceImpl();
        $training_center_id = $token_service->getTrainingCenterIdFromToken();

        return $query->whereHas('headquarters', function ($query) use ($training_center_id) {
            $query->whereHas('trainingCenter', function ($query) use ($training_center_id) {
                $query->where('training_center_id', $training_center_id);
            });
        });
    }
}
