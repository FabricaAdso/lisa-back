<?php

namespace App\Http\Controllers;

use App\Events\SendMessage;
use App\Models\DocumentType;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

        $token = JWTAuth::fromUser($user);

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('identity_document', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function me()
    {
        return response()->json(Auth::user());
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

    public function indexM()
{
    $message = Notification::latest()->get();
    log::debug('mensaje' . $message);
    return response()->json([
        'mensaje' => $message
    ]);
    
}

public function storeM(Request $request)
{
    $request->validate([
        'message' => 'required|string|max:255',
    ]);
    

    // Llamar al método estático createAndSendMessage del modelo Notification
    $message = Notification::createAndSendMessage($request->all());


    // Retornar una respuesta con el mensaje creado
    return response()->json($message);
}


}
