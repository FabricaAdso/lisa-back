<?php

namespace App\Http\Controllers;

use App\Models\Regional;
use Illuminate\Http\Request;

class RegionalController extends Controller
{
    public function index()
    {
        $regionals = Regional::all();
        return response()->json($regionals);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $regional = Regional::create($request->all());
        return response()->json($regional);
    }

    public function show($id)
    {
        $regional = Regional::findOrFail($id);
        return response()->json($regional);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
        ]);
        $regional = Regional::findOrFail($id);
        $regional->update($request->all());
        return response()->json($regional);
    }

    public function destroy($id)
    {
        $regional = Regional::findOrFail($id);
        $regional->delete();
        return response()->json(['message' => 'Regional deleted successfully']);
    }
}
