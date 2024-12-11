<?php

namespace App\Models;
use App\Services\Implementations\TokenServiceImpl;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Instructor extends Model
{
    protected $fillable = [
        'user_id',
        'training_center_id',
        'state',
        'knowledge_network_id' 
    ];

    protected $allowIncluded = ['user','trainingCenter','knowledgeNetwork','aprobations','sessions','courses'];

    protected $allowFilter = ['knowledge_network_id'];

    public function aprobations ()
    {
        return $this->hasMany(Aprobation::class);
    }

    public function sessions ()
    {
        return $this->hasMany(Session::class, 'instructor_id');
    }

    public function knowledgeNetwork ()
    {
        return $this->belongsTo(KnowledgeNetwork::class,'knowledge_network_id');
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

    public function scopeByTrainingCenter(Builder $query)
    {
        $token_service = new TokenServiceImpl();
        $training_center_id = $token_service->getTrainingCenterIdFromToken();   
        
        return $query->where('training_center_id', $training_center_id);
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
            if($filter === 'knowledge_network_id' && $allowFilter->contains('knowledge_network_id')){
                $query->where('knowledge_network_id', $value  );
            } 
        }
    
        return $query;
    }
    
}
