<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use function Psy\sh;

class ShiftController extends Controller
{
    public function index()
    {
        //$shifts = Shift::all();
        $shifts = Shift::included()->get();

        return response()->json($shifts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|String|max:20',
            'start_time' => 'required|date_format:h:i A',//formato de 12hr AM/PM
            'end_time' => 'required|date_format:h:i A|after:start_time',//formato de 12
        ]);

        // Convertir el formato de 12 horas a 24 horas
        $start_time_24 = \Carbon\Carbon::createFromFormat('h:i A', $request->start_time)->format('H:i:s');
        $end_time_24 = \Carbon\Carbon::createFromFormat('h:i A', $request->end_time)->format('H:i:s');

        $request->merge([
            'start_time' => $start_time_24,
            'end_time' => $end_time_24,
        ]);

        $shift = Shift::create($request->all());

        return response()->json($shift);

    }

    public function show($id)
    {
        $shift = Shift::find($id);

        return response()->json($shift);
    }

    public  function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|String|max:20',
            'start_time' => 'required|date_format:h:i A',//formato de 12hr AM/PM
            'end_time' => 'required|date_format:h:i A',//formato de 12
        ]);

        // Convertir el formato de 12 horas a 24 horas
        $start_time_24 = \Carbon\Carbon::createFromFormat('h:i A', $request->start_time)->format('H:i:s');
        $end_time_24 = \Carbon\Carbon::createFromFormat('h:i A', $request->end_time)->format('H:i:s');

        $request->merge([
            'start_time' => $start_time_24,
            'end_time' => $end_time_24,
        ]);

        $shift = Shift::find($id);
        $shift->update($request->all());
        return response()->json($shift);
    }

    public function destroy($id)
    {
        $shift = Shift::find($id);
        $shift->delete();
        return response()->json($shift);  
    }

    //requerimiento

    public function assignDaysToShift(Request $request, $shiftId)
    {
        $request->validate([
            'day_ids' => 'required|array',
            'day_ids.*' => 'integer|exists:days,id'
        ]);

        try {
            // Buscar la jornada, lanzará ModelNotFoundException si no existe
            $shift = Shift::findOrFail($shiftId);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Jornada no encontrada.'], 404);
        }

        //lista de relaciones
        $currentDayIds = $shift->days()->select('days.id')->pluck('id')->toArray();
        
        //validar cruce de dias si es necesario
        foreach ($currentDayIds as $currentDayId) {
            if (!in_array($currentDayId, $request->day_ids)) {
                // Eliminar días que no están en la nueva asignación
                $shift->days()->detach($currentDayId);
            }
        }
        
        //ver la vigencia de las relaciones anteriores con los nuevos parametros
        //array_diff compara el primer con el segundo y devuelve los valores primer que no este en el segundo
        $daysAdd = array_diff($request->day_ids, $currentDayIds);
        if(!empty($daysAdd)){
            $shift->days()->attach($daysAdd);
        }

        $shift->load('days');
        return response()->json([
            'message' => 'Días actualizados correctamente.',
            'shift' => $shift,
        ]);
    }

    /// asignar jornadas a cursos
    
}
