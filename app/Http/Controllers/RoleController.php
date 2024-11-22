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

    // public function toggleRole(Request $request, $userId, $trainingCenterId)
    // {
    //     $user = User::findOrFail($userId);
    //     $trainingCenter = TrainingCenter::findOrFail($trainingCenterId);

    //     $roles = $request->input('roles');
    //     if (!is_array($roles)) {
    //         return response()->json(['error' => 'El campo "roles" debe ser un arreglo.'], 400);
    //     }

    //     // Obtén los roles actuales del usuario en el centro de formación
    //     $currentRoles = $user->trainingCenters()
    //                         ->wherePivot('training_center_id', $trainingCenterId)
    //                         ->withPivot('role_id')
    //                         ->get()
    //                         ->pluck('pivot.role_id')
    //                         ->toArray();

    //     // Eliminar roles que no estén en el nuevo arreglo (detaching)
    //     foreach ($currentRoles as $roleId) {
    //         if (!in_array($roleId, $roles)) {
    //             $user->trainingCenters()->wherePivot('role_id', $roleId)
    //                                 ->wherePivot('training_center_id', $trainingCenterId)
    //                                 ->detach();
    //         }
    //     }

    //     // Agregar roles nuevos o mantener los existentes si ya están
    //     foreach ($roles as $roleId) {
    //         if (!in_array($roleId, $currentRoles)) {
    //             $user->trainingCenters()->attach($trainingCenterId, ['role_id' => $roleId]);
    //         }
    //     }

    //     // Recargar los centros de formación con los roles asociados
    //     $user->load(['trainingCenters' => function ($query) use ($trainingCenterId) {
    //         $query->wherePivot('training_center_id', $trainingCenterId);
    //     }]);

    //     // Obtener los roles con sus nombres usando Spatie Role
    //     $rolesWithNames = $user->trainingCenters->map(function ($trainingCenter) {
    //         $role = Role::where('id', $trainingCenter->pivot->role_id)->where('guard_name', 'api')->first();
    //         return [
    //             'role_id' => $trainingCenter->pivot->role_id,
    //             'role_name' => $role ? $role->name : null,
    //         ];
    //     });

    //     return response()->json([
    //         'message' => 'Roles actualizados correctamente.',
    //         'roles' => $rolesWithNames,
    //         'user' => $user
    //     ], 200);
    // }


}
