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

    protected $allowIncluded = ['user','trainingCenter','knowledgeNetwork'];

    public function aprobations ()
    {
        return $this->hasMany(Aprobation::class);
    }

    public function sessions ()
    {
        return $this->hasMany(Session::class);
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

    
}
