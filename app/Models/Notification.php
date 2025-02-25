<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $allowIncluded = [
        'user'
    ];

    protected $allowFilter = [
        'user_id',
        'user_recieved',
        'message',
        'type',
        'read_at',
        'date'
    ];

    protected $fillable = [
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

    public function scopeIncluded(Builder $query)
    {

        if (empty($this->allowIncluded) || empty(request('included'))) {
            return;
        }

        $relations = explode(',', request('included'));

        $allowIncluded = collect($this->allowIncluded);

        foreach ($relations as $key => $relationship) {

            if (!$allowIncluded->contains($relationship)) {
                unset($relations[$key]);
            }
        }
        $query->with($relations);
    }

    public function scopeFilter(Builder $query)
    {
        // If no allowed filters are set or no filter is requested, exit the method
        if (empty($this->allowFilter) || empty(request('filter'))) {
            return;
        }
    
        // Get the filter parameters from the request
        $filters = request('filter');
        
        // Convert the allowed filters to a collection for easy checking
        $allowFilter = collect($this->allowFilter);
    
        // Iterate through each filter in the request
        foreach ($filters as $filter => $value) {
            // Check if the current filter is in the list of allowed filters
            if ($allowFilter->contains($filter)) {
                // Apply a LIKE query where the specified column contains the filter value
                $query->where($filter, 'LIKE', '%' . $value . '%');
            }
        }
    }

}
