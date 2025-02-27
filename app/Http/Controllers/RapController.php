<?php

namespace App\Http\Controllers;

use App\Models\Rap;
use Illuminate\Http\Request;

class RapController extends Controller
{
    //
    public function index()
    {
        $raps = Rap::all();

        return response()->json($raps);
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|String',
            'subject_id' => 'required|exists:subjects,id',
            'number_hours' => 'required|integer'
        ]);

        $rap = Rap::create($request->all());
        return response()->json($rap);
    }

    public function show($id)
    {
        $rap = Rap::find($id);
        return response()->json($rap);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'description' => 'required|String',
            'subject_id' => 'required|exists:subjects,id',
            'number_hours' => 'required|integer'
        ]);
        
        $rap = Rap::find($id);
        $rap->update($request->all());
        return response()->json($rap);
    }

    public function destroy($id)
    {
        $rap =  Rap::find($id);
        $rap->delete();
        return response()->json(['message' => 'Rap deleted successfully']);
    }
}
