<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{
    protected $fillable = [
        'user_id',
        'training_center_id',
        'state'    
    ];

    //
    public function aprobations ()
    {
        return $this->hasMany(Aprobation::class);
    }

    public function sessions ()
    {
        return $this->hasMany(Session::class);
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

    
}
