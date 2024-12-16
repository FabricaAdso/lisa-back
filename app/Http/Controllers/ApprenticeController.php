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
}
