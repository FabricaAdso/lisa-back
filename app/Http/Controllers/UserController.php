<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Services\TokenService;
use App\Services\RoleService;
use Exception;

class UserController extends Controller
{
    protected $token_service;
    protected $roleService;

    public function __construct(TokenService $token_service, RoleService $roleService)
    {
        $this->token_service = $token_service;
        $this->roleService = $roleService;
    }

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('filter')) {
            $filter = $request->input('filter');
            $query->where(function ($q) use ($filter) {
                $q->where('identity_document', 'like', "%{$filter}%")
                    ->orWhere('name', 'like', "%{$filter}%")
                    ->orWhere('last_name', 'like', "%{$filter}%");
            });
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'identity_document' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'document_type_id' => 'required|integer',
        ]);

        $user = User::create([
            'identity_document' => $request->input('identity_document'),
            'name' => $request->input('name'),
            'last_name' => $request->input('last_name'),
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

        if ($user->deactivation_date) {
            $user->deactivation_date = null;
            $message = 'Usuario reactivado';
        } else {
            $user->deactivation_date = now();
            $message = 'Usuario desactivado';
        }

        $user->save();

        return response()->json(['message' => $message]);
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

    public function getUserRolesById($userId)
    {
        try {
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['error' => 'Usuario no encontrado'], 404);
            }

            $roles = $user->trainingCenters()
                ->withPivot('role_id')
                ->join('roles', 'role_training_center_user.role_id', '=', 'roles.id')
                ->pluck('roles.name')
                ->unique()
                ->values();

            return response()->json([
                'roles' => $roles
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getUsersByTrainingCenter()
    {
        $elementos = request()->query('elementos', 10);
        $page = request()->query('page', 1);

        try {
            $trainingCenterId = $this->token_service->getTrainingCenterIdFromToken();

            if (!is_numeric($trainingCenterId)) {
                return response()->json(['error' => 'Training center ID inválido'], 400);
            }

            // Preparando la consulta
            $query = User::whereHas('trainingCenters', function ($query) use ($trainingCenterId) {
                $query->where('training_center_id', $trainingCenterId);
            })
                ->with(['trainingCenters' => function ($query) use ($trainingCenterId) {
                    $query->where('training_center_id', $trainingCenterId)
                        ->withPivot('role_id');
                }, 'roles']);

            // Aplicando la paginación directamente sobre la consulta
            $users = $query->paginate(intval($elementos), ['*'], 'page', $page);

            // Formateando los resultados
            $formattedUsers = $users->getCollection()->map(function ($user) {
                return [
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
            });

            // Retornando la respuesta con los usuarios paginados y formateados
            return response()->json([
                'data' => $formattedUsers,
                'current_page' => $users->currentPage(),
                'total_pages' => $users->lastPage(),
                'total_items' => $users->total(),
            ], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function getUsersByTrainingCenterSearch(Request $request)
    {
        try {
            $searchTerm = $request->query('search', null);
            $trainingCenterId = $this->token_service->getTrainingCenterIdFromToken();

            if (!is_numeric($trainingCenterId)) {
                return response()->json(['error' => 'Training center ID inválido'], 400);
            }

            $query = User::whereHas('trainingCenters', function ($query) use ($trainingCenterId) {
                $query->where('training_center_id', $trainingCenterId);
            })
                ->with(['trainingCenters' => function ($query) use ($trainingCenterId) {
                    $query->where('training_center_id', $trainingCenterId)
                        ->withPivot('role_id');
                }, 'roles']);

            if ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('last_name', 'like', '%' . $searchTerm . '%');
                });
            }

            $users = $query->limit(20)->get()->map(function ($user) {
                return [
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
            });

            return response()->json($users, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
