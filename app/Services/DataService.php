<?php
namespace App\Services;

use Carbon\Carbon;

class DataService
{
    public function excelDateToDate($dateValue): ? string
    {
        // Verificar si el valor es un número serial (solo números).
        if (is_numeric($dateValue)) {
            // Ajuste para convertir el número serial de Excel.
            $baseDate = \DateTime::createFromFormat('Y-m-d', '1900-01-01');
            if (!$baseDate) return null;
            return $baseDate->modify('+' . ((int)$dateValue - 2) . ' days')->format('Y-m-d');
        } else {
            // Si el valor no es serial, tratar de interpretarlo como una fecha en formato d/m/Y
            $date = \DateTime::createFromFormat('d/m/Y', $dateValue) ?: \DateTime::createFromFormat('m/d/Y', $dateValue);
            return $date ? $date->format('Y-m-d') : null;
        }
    }
}