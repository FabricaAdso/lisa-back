<?php

namespace App\Http\Controllers;
use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Services\RoleService;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;
class RoleController extends Controller
{
    protected $roleService;
    protected $token_service;

    public function __construct(TokenService $token_service, RoleService $roleService)
    {
        $this->token_service = $token_service;
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

    public function assignRoles(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $trainingCenterId = $this->token_service->getTrainingCenterIdFromToken();

        try {
            DB::beginTransaction();
            DB::table('role_training_center_user')
                ->where('user_id', $user->id)
                ->where('training_center_id', $trainingCenterId)
                ->delete();

            $roles = Role::whereIn('id', $request->role_ids)->get();

            $roleData = $roles->map(function ($role) use ($user, $trainingCenterId) {
                return [
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                    'training_center_id' => $trainingCenterId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            DB::table('role_training_center_user')->insert($roleData);
            DB::commit();

            return response()->json([
                'message' => 'Roles asignados exitosamente.',
                'user' => $user,
                'roles' => $roles
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

}
