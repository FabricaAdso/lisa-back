<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function toggleRole(Request $request, $userId)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
            'action' => 'required|string|in:assign,revoke',
        ]);

        $user = User::findOrFail($userId);

        if ($request->action === 'assign') {
            $user->assignRole($request->role);
            return response()->json(['message' => 'Role assigned successfully.'], 200);
            
        } elseif ($request->action === 'revoke') {
            $user->removeRole($request->role);
            return response()->json(['message' => 'Role revoked successfully.'], 200);
        }

        return response()->json(['message' => 'Invalid action.'], 400);
    }
}
