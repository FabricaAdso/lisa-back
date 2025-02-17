<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use App\Models\Instructor;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class InstructorController extends Controller
{

    protected  $token_service;

    function __construct(TokenService $token_service)
    {
        $this->token_service = $token_service;
    }

    public function index()
    {
        $instructor = Instructor::byTrainingCenter()->included()->filter()->get();
        return response()->json($instructor);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'state' => 'required|in:Activo,Inactivo',
            'knowledge_networks_id' => 'nullable|exists:knowledge_networks,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $training_center_id = $this->token_service->getTrainingCenterIdFromToken();

        DB::beginTransaction();
        try {
             // Verificar si el usuario ya tiene el rol de "Instructor"
            $role = Role::where('name', 'Instructor')->firstOrFail();

            $hasRole = DB::table('role_training_center_user')
                ->where('user_id', $user->id)
                ->where('role_id', $role->id)
                ->where('training_center_id', $training_center_id)
                ->exists();

            if (!$hasRole) {
                DB::table('role_training_center_user')->insert([
                    'user_id' => $user->id,
                    'role_id' => $role->id,
                    'training_center_id' => $training_center_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $instructor = Instructor::create([
                'user_id' => $user->id,
                'training_center_id' => $training_center_id,
                'knowledge_network_id' => $request->knowledge_networks_id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'El rol de Instructor fue asignado exitosamente.',
                'user' => $user,
                'role' => $role,
                'instructor' => $instructor,
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function show($id)
    {
        $instructor = Instructor::find($id);
        return response()->json($instructor);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'training_center_id' => 'required|exists:training_centers,id',
            'state' => 'required|in:Activo,Inactivo',
            'knowledge_network_id' => 'required|exists:knowledge_networks,id'
        ]);

        $instructor = Instructor::find($id);
        $instructor->update($request->all());
        return response()->json($instructor);
    }

    public function destroy($id)
    {
        $instructor =  Instructor::find($id);
        $instructor->delete();
        return response()->json(['message' => 'Instructor deleted successfully']);
    }
}
