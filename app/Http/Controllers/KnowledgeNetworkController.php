<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeNetwork;
use Illuminate\Http\Request;

class KnowledgeNetworkController extends Controller
{
    public function index()
    {
        $networks = KnowledgeNetwork::included()->get();
        return response()->json($networks);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $network = KnowledgeNetwork::create($request->all());

        return response()->json($network);
    }

    public function show($id)
    {
        $network = KnowledgeNetwork::with('instructors')->findOrFail($id);
        
        return response()->json($network);
    }

    public function update(Request $request, KnowledgeNetwork $knowledgeNetwork)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $knowledgeNetwork->update($request->all());

        return response()->json($knowledgeNetwork);
    }

    public function destroy(KnowledgeNetwork $knowledgeNetwork)
    {
        $knowledgeNetwork->delete();

        return response()->json(['message' => 'Red de aprendizaje eliminada exitosamente.']);
    }

}
