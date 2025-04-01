<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleCalendarService;

class GoogleCalendarController extends Controller
{
    protected $calendarService;

    public function __construct(GoogleCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Obtiene los días festivos de un año específico.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y')); // Si no se envía el año, usa el actual
        
        $holidays = $this->calendarService->getHolidays($year);

        // Formatear la respuesta para que sea más limpia (opcional)
        $formattedHolidays = array_map(function ($holiday) {
            return [
                'name' => $holiday->getSummary(),
                'date' => $holiday->getStart()->getDate(), // Fecha en formato YYYY-MM-DD
                'description' => $holiday->getDescription() ?? '',
            ];
        }, $holidays);

        return response()->json([
            'success' => true,
            'holidays' => $formattedHolidays,
        ]);
    }
}
