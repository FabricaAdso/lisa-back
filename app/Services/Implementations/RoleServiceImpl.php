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

    public function toggleRoles(int $userId, int $trainingCenterId, array $roles)
    {
        // Obtener el usuario y el centro de formación
        $user = User::findOrFail($userId);
        $trainingCenter = TrainingCenter::findOrFail($trainingCenterId);

        // Obtener los roles actuales del usuario en el centro de formación
        $currentRoles = $user->trainingCenters()
                            ->wherePivot('training_center_id', $trainingCenterId)
                            ->withPivot('role_id')
                            ->get()
                            ->pluck('pivot.role_id')
                            ->toArray();

        // Eliminar roles que no están en el nuevo arreglo (detaching)
        foreach ($currentRoles as $roleId) {
            if (!in_array($roleId, $roles)) {
                $user->trainingCenters()->wherePivot('role_id', $roleId)
                                        ->wherePivot('training_center_id', $trainingCenterId)
                                        ->detach();
            }
        }

        // Agregar roles nuevos o mantener los existentes si ya están
        foreach ($roles as $roleId) {
            if (!in_array($roleId, $currentRoles)) {
                $user->trainingCenters()->attach($trainingCenterId, ['role_id' => $roleId]);
            }
        }

        // Recargar los centros de formación con los roles asociados
        $user->load(['trainingCenters' => function ($query) use ($trainingCenterId) {
            $query->wherePivot('training_center_id', $trainingCenterId);
        }]);

        // Obtener los roles con sus nombres usando Spatie Role
        $rolesWithNames = $user->trainingCenters->map(function ($trainingCenter) {
            $role = Role::where('id', $trainingCenter->pivot->role_id)->where('guard_name', 'api')->first();
            return [
                'role_id' => $trainingCenter->pivot->role_id,
                'role_name' => $role ? $role->name : null,
            ];
        });

        // Devolver tanto los roles como el usuario
        return [
            'user' => $user,
            'roles' => $rolesWithNames,
        ];
    }

}
