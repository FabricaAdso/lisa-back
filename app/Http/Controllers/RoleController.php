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

    public function assignRoles(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
            'course_id' => 'nullable|exists:courses,id',
            'state' => 'nullable|in:Formacion,Desertado,Etapa_productiva,Retiro_voluntario',
            'knowledge_network_id' => 'nullable|exists:knowledge_networks,id',
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

            // Verificar si el usuario tiene el rol de "aprendiz" o "instructor"
            $isApprentice = $roles->contains('name', 'Aprendiz');
            $isInstructor = $roles->contains('name', 'Instructor');

            // Si tiene el rol de "aprendiz", agregarlo a la tabla `apprentices`
            if ($isApprentice) {
                if (!$request->has('course_id')){
                    throw new \Exception('El campo course_id es requerido para el rol de aprendiz.');
                }
                if (!$request->has('state')) {
                    throw new \Exception('El campo state es requerido para el rol de aprendiz.');
                }

                DB::table('apprentices')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'course_id' => $request->course_id,
                        'state' => $request->state,
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
                if (!$request->has('knowledge_network_id')) {
                    throw new \Exception('El campo knowledge_network_id es requerido para el rol de instructor.');
                }

                DB::table('instructors')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'training_center_id' => $trainingCenterId,
                        'knowledge_network_id' => $request->knowledge_network_id,
                        'state' => 'Activo',
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
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

}
