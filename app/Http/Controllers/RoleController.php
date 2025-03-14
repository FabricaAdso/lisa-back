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

    // public function assignRoles(Request $request)
    // {
    //     $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'role_ids' => 'required|array',
    //         'role_ids.*' => 'exists:roles,id',
    //     ]);

    //     $user = User::findOrFail($request->user_id);
    //     $trainingCenterId = $this->token_service->getTrainingCenterIdFromToken();

    //     try {
    //         DB::beginTransaction();
    //         DB::table('role_training_center_user')
    //             ->where('user_id', $user->id)
    //             ->where('training_center_id', $trainingCenterId)
    //             ->delete();

    //         $roles = Role::whereIn('id', $request->role_ids)->get();

    //         $roleData = $roles->map(function ($role) use ($user, $trainingCenterId) {
    //             return [
    //                 'user_id' => $user->id,
    //                 'role_id' => $role->id,
    //                 'training_center_id' => $trainingCenterId,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ];
    //         })->toArray();

    //         DB::table('role_training_center_user')->insert($roleData);
    //         DB::commit();

    //         return response()->json([
    //             'message' => 'Roles asignados exitosamente.',
    //             'user' => $user,
    //             'roles' => $roles
    //         ], 200);
    //     } catch (Exception $e) {
    //         DB::rollBack();
    //         return response()->json(['error' => $e->getMessage()], 400);
    //     }
    // }

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

            // Eliminar roles existentes para el usuario en el centro de entrenamiento
            DB::table('role_training_center_user')
                ->where('user_id', $user->id)
                ->where('training_center_id', $trainingCenterId)
                ->delete();

            // Obtener los roles que se van a asignar
            $roles = Role::whereIn('id', $request->role_ids)->get();

            // Preparar los datos para insertar en la tabla pivote
            $roleData = $roles->map(function ($role) use ($user, $trainingCenterId) {
                return [
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                    'training_center_id' => $trainingCenterId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            // Insertar los roles en la tabla pivote
            DB::table('role_training_center_user')->insert($roleData);

            // Verificar si el usuario tiene el rol de "aprendiz" o "instructor"
            $isApprentice = $roles->contains('name', 'aprendiz');
            $isInstructor = $roles->contains('name', 'instructor');

            // Si tiene el rol de "aprendiz", agregarlo a la tabla `apprentices`
            if ($isApprentice) {
                DB::table('apprentices')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'training_center_id' => $trainingCenterId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            } else {
                // Si no tiene el rol de "aprendiz", eliminarlo de la tabla `apprentices`
                DB::table('apprentices')->where('user_id', $user->id)->delete();
            }

            // Si tiene el rol de "instructor", agregarlo a la tabla `instructors`
            if ($isInstructor) {
                DB::table('instructors')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'training_center_id' => $trainingCenterId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            } else {
                // Si no tiene el rol de "instructor", eliminarlo de la tabla `instructors`
                DB::table('instructors')->where('user_id', $user->id)->delete();
            }

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
