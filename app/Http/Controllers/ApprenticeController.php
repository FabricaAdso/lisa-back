<?php

namespace App\Http\Controllers;

use Spatie\Permission\Models\Role;
use App\Models\Apprentice;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class ApprenticeController extends Controller
{
    protected  $token_service;

    function __construct(TokenService $token_service)
    {
        $this->token_service = $token_service;
    }

    public function index()
    {
        //  $apprentices = Apprentice::all();
        $apprentices = Apprentice::byTrainingCenter()->included()->filter()->get();
        return response()->json($apprentices);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'state' => 'required|in:formacion,Desertado,Etapa_productiva,Retiro_voluntario',
        ]);

        $user = User::findOrFail($request->user_id);
        $training_center_id = $this->token_service->getTrainingCenterIdFromToken();

        DB::beginTransaction();
        try {
            // Verificar si el usuario ya tiene el rol de "Aprendiz"
            $role = Role::where('name', 'Aprendiz')->firstOrFail();

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

            $apprentice = Apprentice::create([
                'course_id' => $request->course_id,
                'state' => $request->state,
                'user_id' => $request->user_id,
                'training_center_id' => $training_center_id,
            ]);

            DB::commit();

            return response()->json([
                'user' => $user,
                'role' => $role,
                'apprentice' => $apprentice,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function show($id)
    {
        $apprentice = Apprentice::find($id);
        return response()->json($apprentice);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'state' => 'required|in:formacion,Desertado,Etapa_productiva,Retiro_voluntario',
        ]);


        $apprentice = Apprentice::find($id);
        $apprentice->update($request->all());
        return response()->json($apprentice);
    }

    public function destroy($id)
    {
        $apprentice =  Apprentice::find($id);
        $apprentice->delete();
        return response()->json(['message' => 'Apprentice deleted successfully']);
    }

    //balvin
    public function getByUserId($userId)
{
    // Verificar que el usuario existe y cargar sus datos completos
    $user = User::with(['document_type', 'trainingCenters'])
        ->where('id', $userId)
        ->first();

    if (!$user) {
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }

    $trainingCenterId = $this->token_service->getTrainingCenterIdFromToken();

    // Obtener el aprendiz con relaciones
    $apprentice = Apprentice::with(['course.program.trainingCenter'])
        ->where('user_id', $userId)
        ->whereHas('course.program', function($query) use ($trainingCenterId) {
            $query->where('training_center_id', $trainingCenterId);
        })
        ->first();

    if (!$apprentice) {
        return response()->json(['message' => 'Aprendiz no encontrado para este centro de formación'], 404);
    }

    // Verificar roles
    $roles = DB::table('role_training_center_user')
        ->where('user_id', $userId)
        ->where('training_center_id', $trainingCenterId)
        ->pluck('role_id')
        ->map(function($roleId) {
            return Role::findById($roleId)->name;
        });

    return response()->json([
        'user' => [
            'id' => $user->id,
            'identity_document' => $user->identity_document,
            'name' => $user->name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'document_type' => $user->document_type,
            'roles' => $roles,
            'training_centers' => $user->trainingCenters
        ],
        'apprentice_data' => [
            'id' => $apprentice->id,
            'course_id' => $apprentice->course_id,
            'state' => $apprentice->state,
            'course' => $apprentice->course,
            'program' => $apprentice->course->program ?? null,
            'training_center' => $apprentice->course->program->trainingCenter ?? null,
            'created_at' => $apprentice->created_at,
            'updated_at' => $apprentice->updated_at
        ]
    ], 200);
}
}
