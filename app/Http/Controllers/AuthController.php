<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\User;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
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

    public function getDocument(){
        $document = DocumentType::all();
        return response()->json($document);
    }

    public function login(Request $request)
    {
        $request->validate([
            'identity_document' => '',
            'password' => '',
        ]);

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

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $token = Str::random(60);
        PasswordReset::create([
            'email' => $request->email,
            'token' => $token,
            'created_at' => now(),
        ]);

        // Enviar el correo de restablecimiento de contraseña
        Mail::send('emails.reset', ['token' => $token], function ($message) use ($request) {
            $message->to($request->email);
            $message->subject('Restablecer contraseña');
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

        // Cambiar la contraseña del usuario
        $user = User::where('email', $passwordReset->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();

        // Eliminar el token usado
        $passwordReset->delete();

        return response()->json(['message' => 'Contraseña restablecida correctamente.']);
    }

}
