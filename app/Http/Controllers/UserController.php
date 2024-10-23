<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('filter')) {
            $filter = $request->input('filter');
            $query->where(function ($q) use ($filter) {
                $q->where('identity_document', 'like', "%{$filter}%")
                  ->orWhere('first_name', 'like', "%{$filter}%")
                  ->orWhere('last_name', 'like', "%{$filter}%");
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'second_last_name' => 'nullable|string|max:255',
            'identity_document' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'document_type_id' => 'required|integer',
        ]);

        $user = User::create([
            'identity_document' => $request->input('identity_document'),
            'first_name' => $request->input('first_name'),
            'middle_name' => $request->input('middle_name'),
            'last_name' => $request->input('last_name'),
            'second_last_name' => $request->input('second_last_name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'document_type_id' => $request->input('document_type_id'),
        ]);

        return response()->json($user, 201);
    }

    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);

        return response()->json([
            'user' => $user,
            'roles' => $user->getRoleNames(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
           'email' => 'email|unique:users,email,' . $user->id,
         ]);

        $user->update($request->all());

        return response()->json($user);
    }

    public function deactivate($id)
    {
        $user = User::findOrFail($id);
        $user->deactivation_date = now();
        $user->save();

        return response()->json(['message' => 'Usuario desactivado']);
    }

    public function deactivated()
    {
        $users = User::whereNotNull('deactivation_date')->get();
        return response()->json($users);
    }

    public function active()
    {
        $users = User::whereNull('deactivation_date')->get();
        return response()->json($users);
    }

}
