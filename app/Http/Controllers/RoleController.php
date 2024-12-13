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

    public function assignRole(Request $request)
    {
        
      $request->validate([
            'user_id' => 'required|exists:users,id', 
            'role_id' => 'required|exists:roles,id', 
        ]);
    
        $user = User::findOrFail($request->user_id);
        $training_center_id = $this->token_service->getTrainingCenterIdFromToken();
    
        $role = Role::findOrFail($request->role_id);
    
        try {
            // Inserta el rol en la tabla pivot, incluyendo el centro de formación
            DB::table('role_training_center_user')->insert([
                'user_id' => $user->id,
                'role_id' => $role->id,
                'training_center_id' => $training_center_id,  
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            return response()->json([
                'message' => 'Rol asignado exitosamente.',
                'user' => $user,   
                'role' => $role    
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

}
