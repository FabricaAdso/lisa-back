<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    protected $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

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

    // Obtener Tipos de Documentos
    public function getDocument(){
        $document = DocumentType::all();
        return response()->json($document);
    }

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

        $token = JWTAuth::claims([
            'training_center_id' => $encryptedTrainingCenterId
        ])->fromUser($user);

        return $this->respondWithToken($token);
    }

    public function me()
    {
        $user = User::find(Auth::id());

        // Cargar los centros de formación y los roles asociados al usuario
        $userWithTrainingCenters = $user->load(['trainingCenters' => function($query) {
            $query->withPivot('role_id');
        }]);

        return response()->json($userWithTrainingCenters);
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

    //Desencriptar Trainig_center_id
    public function getTrainingCenterIdFromToken()
    {
        $trainingCenterId = $this->tokenService->getTrainingCenterIdFromToken();

        return response()->json(['training_center_id' => $trainingCenterId]);
    }

    // Centros de Formacion para Usuarios.
    public function addTrainingCenter(Request $request, $userId)
    {
        $request->validate([
            'training_center_id' => 'required|integer|exists:training_centers,id',
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        $user = User::findOrFail($userId);

        if ($user->trainingCenters()->where('training_center_id', $request->training_center_id)->exists()) {
            return response()->json(['error' => 'El usuario ya está asociado a este centro de formación.'], 400);
        }

        $user->trainingCenters()->attach($request->training_center_id, ['role_id' => $request->role_id]);

        return response()->json(['message' => 'Centro de formación agregado exitosamente.']);
    }

    public function getUserTrainingCenters($userId)
    {
        $user = User::findOrFail($userId);

        $trainingCenters = $user->trainingCenters()->withPivot('role_id')->get();

        return response()->json($trainingCenters);
    }

    public function removeTrainingCenter(Request $request, $userId)
    {
        $request->validate([
            'training_center_id' => 'required|integer|exists:training_centers,id',
        ]);

        $user = User::findOrFail($userId);

        $user->trainingCenters()->detach($request->training_center_id);

        return response()->json(['message' => 'Centro de formación eliminado exitosamente.']);
    }

}
