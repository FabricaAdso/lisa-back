<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Justification extends Model
{
    protected $fillable = [
        'assistance_id',
        'file_url',
        'description'
    ];

    //
    public function aprobation ()
    {
        return $this->hasOne(Aprobation::class);
    }

    public function assistance ()
    {
        return $this->belongsTo(Assistance::class);
    }
}
