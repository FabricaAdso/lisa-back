<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    //
    public function assistances ()
    {
        return $this->hasMany(Assistance::class);
    }

    public function instructor ()
    {
        return $this->belongsTo(Instructor::class);
    }

    public function course ()
    {
        return $this->belongsTo(Course::class);
    }
}
