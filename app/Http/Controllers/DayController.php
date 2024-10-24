<?php

namespace App\Http\Controllers;

use App\Models\Day;
use Illuminate\Http\Request;

class DayController extends Controller
{
    public function index()
    {
        $days = Day::all();

        return response()->json($days);
    }

    public function show($id)
    {
        $day = Day::find($id);

        return response()->json($day);
    }

    public function destroy($id)
    {
        $day = Day::find($id);

        $day->delete();

        return response()->json($day);
    }
}
