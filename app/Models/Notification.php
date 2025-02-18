<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'title',
        'user_id',
        'user_recieved',
        'message',
        'type',
        'read_at',
        'date'
    ];

    protected $casts = [
        'reat_at' => 'datetime',
        'data' => 'array'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function markAsRead(){
        $this->read_at = now();
    }

    public function markAsUnread(){
        $this->read_at = null;
    }
}
