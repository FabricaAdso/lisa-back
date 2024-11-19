<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instructor extends Model
{

      
      public function trainingCenter()
      {
          return $this->belongsTo(TrainingCenter::class);
      }

    public function scopeByTrainingCenter($query, $centerId)
    {
        return $query->where('training_center_id', $centerId);
    }
}
