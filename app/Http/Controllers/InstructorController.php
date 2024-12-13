<?php

namespace App\Http\Controllers;

use App\Models\Instructor;
use App\Services\TokenService;
use Illuminate\Http\Request;

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
        ]);
        $training_center_id = $this->token_service->getTrainingCenterIdFromToken();
        $instructor = Instructor::create([
            'user_id' => $request->user_id,
            'training_center_id'=>$training_center_id,
            'knowledge_network_id'=>$request->knowledge_networks_id
        ]);
        if ($instructor->user) {
            // Verificar si el usuario no tiene el rol de "instructor"
            if (!$instructor->user->hasRole('Instructor')) {
                $instructor->user->assignRole('Instructor');
            }
        }
    
        return response()->json($instructor);
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
            'training_center_id'=>'required|exists:training_centers,id',
            'state' => 'required|in:Activo,Inactivo',
            'knowledge_network_id'=> 'required|exists:knowledge_networks,id'
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

    public function assignRoleToInstructor(Request $request, $instructorId)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $instructor = Instructor::findOrFail($instructorId);

        if (!$instructor->user) {
            return response()->json(['message' => 'El participante no tiene un usuario asociado.'], 404);
        }

    
        $instructor->user->assignRole($request->role);

        return response()->json([
            'message' => "Rol '{$request->role}' asignado al usuario asociado al participante.",
            'participant' => $instructor
        ]);
    }
}
