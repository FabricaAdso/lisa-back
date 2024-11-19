<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function register(Request $request)
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
            'training_center_id' => 'required|integer',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'second_last_name' => $request->second_last_name,
            'identity_document' => $request->identity_document,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'document_type_id' => $request->document_type_id,
        ]);

        $user->trainingCenters()->attach($request->training_center_id, ['role_id' => 1]);

        $token = JWTAuth::fromUser($user);

        return response()->json(['user' => $user, 'token' => $token], 201);
    }


    public function getDocument(){
        $document = DocumentType::all();
        return response()->json($document);
    }

    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'identity_document' => 'required|string',
    //         'password' => 'required|string',
    //         'training_center_id' => 'required|integer',
    //     ]);

    //     $credentials = $request->only('identity_document', 'password');

    //     if (!$user = User::where('identity_document', $credentials['identity_document'])->first()) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $roleCenter = $user->trainingCenters()->where('training_center_id', $request->training_center_id)->first();

    //     if (!$roleCenter) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     if (!Hash::check($credentials['password'], $user->password)) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     $token = JWTAuth::fromUser($user);

    //     return $this->respondWithToken($token);
    // }


    // public function me()
    // {
    //     return response()->json(Auth::user());
    // }

    public function login(Request $request)
    {
        $request->validate([
            'identity_document' => 'required|string',
            'password' => 'required|string',
            'training_center_id' => 'required|integer',
        ]);

        $credentials = $request->only('identity_document', 'password');

        if (!$user = User::where('identity_document', $credentials['identity_document'])->first()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $roleCenter = $user->trainingCenters()->where('training_center_id', $request->training_center_id)->first();

        if (!$roleCenter) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $encryptedTrainingCenterId = Crypt::encrypt($request->training_center_id);

        $token = JWTAuth::fromUser($user, [
            'training_center_id' => $encryptedTrainingCenterId
        ]);

        return $this->respondWithToken($token);
    }

    public function me()
    {
        $user = Auth::user();

        $trainingCenters = $user->trainingCenters()->get()->map(function ($trainingCenter) {
            return [
                'training_center_id' => $trainingCenter->id,
                'role_id' => $trainingCenter->pivot->role_id,
                'role_name' => $this->getRoleName($trainingCenter->pivot->role_id),
            ];
        });

        return response()->json([
            'user' => $user,
            'training_centers' => $trainingCenters,
        ]);
    }

    private function getRoleName($roleId)
    {
        $roles = [
            1 => 'Usuario',
            2 => 'Coordinador academico',
            3 => 'Instructor',
            4 => 'Aprendiz',
            5 => 'Administrador',
        ];

        return $roles[$roleId] ?? 'Unknown';
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        $token = JWTAuth::refresh(JWTAuth::getToken());
        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ]);
    }

    public function getTrainingCenterIdFromToken()
    {
        $token = JWTAuth::getToken();

        $payload = JWTAuth::getPayload($token);

        $encryptedTrainingCenterId = $payload['training_center_id'];
        $trainingCenterId = Crypt::decrypt($encryptedTrainingCenterId);

        return $trainingCenterId;
    }

}
