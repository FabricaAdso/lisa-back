<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $fillable = [
        'name',
        'abbreviation'
    ];

    use HasFactory;

    public function users()
    {
        return $this->hasMany(User::class);
    }


}
