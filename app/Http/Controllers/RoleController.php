<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function getRoles()
    {
        $roles = $this->roleService->getRoles();
        return $roles;
    }

    public function toggleRole(Request $request, $userId, $trainingCenterId)
    {
        $roles = $request->input('roles');
        if (!is_array($roles)) {
            return response()->json(['error' => 'El campo "roles" debe ser un arreglo.'], 400);
        }

        $result = $this->roleService->toggleRoles($userId, $trainingCenterId, $roles);

        return response()->json([
            'message' => 'Roles actualizados correctamente.',
            'user' => $result['user'],
            'roles' => $result['roles'],
        ], 200);
    }

}
