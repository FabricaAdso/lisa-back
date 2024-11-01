<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    //
    protected $fillable = ['date','start_time', 'end_time','user_id'];

    public function assistances()
    {
        return $this->hasMany(Assistance::class);
    }
}
