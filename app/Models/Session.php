<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    //
    protected $fillable = ['date', 'start_time', 'end_time', 'participant_id'];
    protected $allowIncluded = ['participant'];

    public function assistances()
    {
        return $this->hasMany(Assistance::class);
    }

    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
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
