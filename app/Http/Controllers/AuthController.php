<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\TokenService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
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
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'identity_document' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'document_type_id' => 'required|integer',
            'training_center_id' => 'required|integer',
        ]);

        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
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
        $user = User::with(['trainingCenters' => function($query) {
            $query->withPivot('role_id');
        }, 'roles'])->find(Auth::id());

        $userWithRoles = [
            'id' => $user->id,
            'identity_document' => $user->identity_document,
            'name' => $user->name,
            'last_name' => $user->last_name,
            'deactivation_date' => $user->deactivation_date,
            'is_superuser' => $user->is_superuser,
            'email' => $user->email,
            'email_verified_at' => $user->email_verified_at,
            'document_type_id' => $user->document_type_id,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'roles' => $user->training_centers_with_roles,
        ];

        return response()->json($userWithRoles);
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

    public function broadcastAuth(Request $request)
    {
        $user = Auth::user(); // Obtener el usuario autenticado

        if (!$user) {
            return response('Unauthorized', 401);
        }

        // Aquí se maneja la autenticación de canales privados o de presencia
        return Broadcast::auth($request);
    }

    public function showResetForm(Request $request)
    {
        return view('auth.reset')->with(['token' => $request->token]);
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'No se encontró un usuario con este correo electrónico.'], 404);
        }

        $token = Str::random(60);
        PasswordReset::create([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        Mail::send('emails.reset', ['user' => $user, 'token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Restablecer Contraseña');
        });

        return response()->json(['message' => 'Se ha enviado el enlace de restablecimiento de contraseña.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|confirmed|min:6',
        ]);

        $passwordReset = PasswordReset::where('token', $request->token)
            ->where('created_at', '>=', now()->subMinutes(30))
            ->first();

        if (!$passwordReset) {
            return response()->json(['error' => 'Este token es inválido o ha expirado.'], 400);
        }

        $user = User::where('email', $passwordReset->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();

        $passwordReset->delete();

        return response()->json(['message' => 'Contraseña restablecida correctamente.']);
    }

}
