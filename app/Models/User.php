<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\TrainingCenter;

class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory,HasRoles ,HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'identity_document',
        'first_name',
        'middle_name',
        'last_name',
        'second_last_name',
        'email',
        'password',
        'document_type_id',
        'training_center_id',
    ];

    public function document_type()
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function trainingCenters()
    {
        return $this->belongsToMany(TrainingCenter::class, 'role_training_center_user')
                    ->withPivot('role_id')
                    ->withTimestamps();
    }

    public function apprentices ()
    {
        return $this->hasMany(Apprentice::class);
    }

    public function instructors ()
    {
        return $this->hasMany(Instructor::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getTrainingCentersWithRolesAttribute()
    {
        $centers = $this->trainingCenters->groupBy('pivot.training_center_id')->map(function($center) {
            return [

                'roles' => $center->pluck('pivot.role_id')->unique()->map(function($roleId) {
                     return \Spatie\Permission\Models\Role::findById($roleId)->name;
                // })->implode(', '), Como cadena de texto
                })->all(), //Como arreglo
            ];
        })->pluck('roles');

        return $centers->values()->first();
    }

}
