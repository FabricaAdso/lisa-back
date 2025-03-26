<?php

namespace App\Services\Implementations;

use App\Services\RoleService;
use App\Models\User;
use App\Models\TrainingCenter;
use Spatie\Permission\Models\Role;

class RoleServiceImpl implements RoleService {

     public function getRoles()
    {
        $roles = Role::all();
        return response()->json($roles, 200);
    }


}
