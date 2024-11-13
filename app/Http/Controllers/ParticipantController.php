<?php

namespace App\Http\Controllers;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    public function index()
    {
        //$participant = Participant::all();
        $participant = Participant::included()->get();


        return response()->json($participant);
    }

    public function show($id)
    {
        $participant = Participant::find($id);
        return response()->json($participant);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'role_id' => 'required|exists:roles,id'
        ]);

        $participant = Participant::find($id);
        $participant->update($request->all());
        return response()->json($participant);
    }

    public function destroy($id)
    {
        $participant =  Participant::find($id);
        $participant->delete();
        return response()->json(['message' => 'participant deleted successfully']);
    }
    // Asignar participantes a una ficha
    public function assignParticipants(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'role_id' => 'required|exists:roles,id'
        ]);

        $participant = Participant::create($request->all());

        return response()->json($participant, 201);
    }

    // asignar Rol a participante
    public function assignRoleToParticipant(Request $request, $participantId)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $participant = Participant::findOrFail($participantId);

        if (!$participant->user) {
            return response()->json(['message' => 'El participante no tiene un usuario asociado.'], 404);
        }

        // Asignar el rol al usuario del participante
        $participant->user->assignRole($request->role);

        return response()->json([
            'message' => "Rol '{$request->role}' asignado al usuario asociado al participante.",
            'participant' => $participant
        ]);
    }

    // Obtener participantes por rol y ficha
    public function getParticipants(Request $request)
    {
        $query = Participant::query();

        // Filtrar por rol y ficha si se proporciona
        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }
        if ($request->has('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        return response()->json($query->with('user')->get());
    }

    // Asignar instructor a una ficha


    public function assignInstructor(Request $request)
    {
        $request->validate([
            'participant_id' => 'required|exists:participants,id', 
            'course_id' => 'required|exists:courses,id',           
        ]);

        
        $participant = Participant::with('user')->findOrFail($request->participant_id);

        // Comprobar si el usuario asociado al participante tiene el rol de 'Instructor'
        if (!$participant->user->hasRole('Instructor')) {
            return response()->json(['message' => 'El participante no tiene el rol de instructor.'], 403);
        }

      
        $participant->course_id = $request->course_id;
        $participant->save();

        return response()->json(['message' => 'Instructor asignado correctamente a la ficha.'], 200);
    }


    public function assignAprendiz(Request $request)
    {
        $request->validate([
            'participant_id' => 'required|exists:participants,id', 
            'course_id' => 'required|exists:courses,id',           
        ]);

        $participant = Participant::with('user')->findOrFail($request->participant_id);

        // Comprobar si el usuario asociado al participante tiene el rol de 'Instructor'
        if (!$participant->user->hasRole('Aprendiz')) {
            return response()->json(['message' => 'El participante no tiene el rol de Aprendiz.'], 403);
        }

        $participant->course_id = $request->course_id;
        $participant->save();

        return response()->json(['message' => 'Aprendiz asignado correctamente a la ficha.'], 200);
    }
    // Método para obtener participantes por rol
    public function getParticipantsByRole(Request $request)
    {
        
        $request->validate([
            'role' => 'nullable|string', 
            'course_id' => 'nullable|exists:courses,id', 
        ]);

       
        $participants = Participant::query();

        // Si se proporciona un rol, filtrar participantes por ese rol
        if ($request->has('role')) {
            $participants->whereHas('user.roles', function ($query) use ($request) {
                $query->where('name', $request->role); 
            });
        }

        // Si se proporciona un course_id, filtrar por curso
        if ($request->has('course_id')) {
            $participants->where('course_id', $request->course_id);
        }

       
        return response()->json($participants->with('user.roles')->get());
    }
}
