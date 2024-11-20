<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function toggleRole(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        $roles = $request->input('roles');

        $currentRoles = $user->getRoleNames()->toArray();

        if (!is_array($roles)) {
            return response()->json(['error' => 'El campo "roles" debe ser un arreglo.'], 400);
        }

        foreach ($currentRoles as $roleName) {
            if (!in_array($roleName, $roles)) {
                $role = Role::findByName($roleName);
                $user->removeRole($role);
            }
        }

        foreach ($roles as $roleName) {
            $role = Role::findByName($roleName);
            if (!$user->hasRole($role)) {
                $user->assignRole($role);
            }
        }
        $user->load('roles');
        return response()->json(['message' => 'Roles actualizados', 'roles' => $user->getRoleNames(),
        'user' => $user], 200);
    }
}
