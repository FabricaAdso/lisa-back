<?php

namespace App\Helpers;
use Carbon\Carbon;

class HolidayHelper
{
    public static function calcularFestivos($year): array
    {
        // Festivos fijos
        $festivosFijos = [
            Carbon::create($year, 1, 1)->toDateString(),   // Año Nuevo
            Carbon::create($year, 5, 1)->toDateString(),   // Día del Trabajo
            Carbon::create($year, 7, 20)->toDateString(),  // Independencia de Colombia
            Carbon::create($year, 8, 7)->toDateString(),   // Batalla de Boyacá
            Carbon::create($year, 12, 25)->toDateString(), // Navidad
        ];

        // Festivos móviles
        $festivosMoviles = [
            self::calcularFestivoMovil($year, 1, 6),   // Reyes Magos
            self::calcularFestivoMovil($year, 3, 19),  // Día de San José
            self::calcularFestivoMovil($year, 6, 29),  // San Pedro y San Pablo
            self::calcularFestivoMovil($year, 8, 15),  // Asunción de la Virgen
            self::calcularFestivoMovil($year, 10, 12), // Día de la Diversidad Étnica y Cultural
            self::calcularFestivoMovil($year, 11, 1),  // Todos los Santos
            self::calcularFestivoMovil($year, 11, 11), // Independencia de Cartagena
        ];

        return array_merge($festivosFijos, $festivosMoviles);
    }

    private static function calcularFestivoMovil($year, $month, $day): string
    {
        $fecha = Carbon::create($year, $month, $day);
        if ($fecha->isSunday()) {
            return $fecha->toDateString();
        }
        return $fecha->next(Carbon::MONDAY)->toDateString();
    }
}
