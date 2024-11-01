<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
class Participant extends Model
{
    //
    protected $fillable = ['start_date', 'end_date','user_id','course_id','role_id'];
    protected $allowIncluded = ['course','user'];

    public function assistances()
    {
        return $this->hasMany(Assistance::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
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


}
