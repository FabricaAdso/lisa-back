<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Day extends Model
{
    //renombrar el pivot
    public function getShiftData(){
        return [
            'shift_id' => $this->pivot->shift_id,
            'day_id' => $this->pivot->day_id,
        ];
    }

    public function shifts()
    {
        return $this->belongsToMany(Shift::class);
    }
}
