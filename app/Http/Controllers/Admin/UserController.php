<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\BitacoraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $q = trim((string) $request->get('q', ''));

        $users = User::query()
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($x) use ($q) {
                    $x->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->with(['role:id,nombre'])
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Usuarios/Index', [
            'users'   => $users,
            'filters' => ['q' => $q],
        ]);
    }

    public function create(): Response
    {
        $roles = DB::table('roles')->select('id', 'nombre')->orderBy('id')->get();

        return Inertia::render('Admin/Usuarios/Create', [
            'roles' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role_id'  => ['required', 'integer', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'role_id'           => $data['role_id'],
            'password'          => Hash::make($data['password']),
            'email_verified_at' => now(),
        ]);

        // Bitácora
        BitacoraService::add('usuarios', 'crear', $user->id, [
            'nombre' => $user->name,
            'rol'    => (int) $user->role_id,
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$user->name} creado.");
    }

    public function edit(User $user): Response
    {
        $roles = DB::table('roles')->select('id', 'nombre')->orderBy('id')->get();

        return Inertia::render('Admin/Usuarios/Edit', [
            'user'  => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'role_id' => (int) $user->role_id,
            ],
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', "unique:users,email,{$user->id}"],
            'role_id'  => ['required', 'integer', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        // Evitar dejar al sistema sin administradores
        if ($user->role_id === 1 && (int) $data['role_id'] !== 1) {
            $admins = User::where('role_id', 1)->count();
            if ($admins <= 1) {
                return back()->withErrors([
                    'role_id' => 'No puedes quitar el rol de Administrador al único admin del sistema.',
                ])->withInput();
            }
        }

        $payload = [
            'name'    => $data['name'],
            'email'   => $data['email'],
            'role_id' => (int) $data['role_id'],
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        // Bitácora
        BitacoraService::add('usuarios', 'actualizar', $user->id, [
            'rol' => (int) $payload['role_id'],
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$user->name} actualizado.");
    }

    public function destroy(User $user)
    {
        if ($user->role_id === 1 && User::where('role_id', 1)->count() <= 1) {
            return back()->withErrors(['base' => 'No puedes eliminar al único administrador.']);
        }

        // Bitácora (antes de borrar)
        BitacoraService::add('usuarios', 'eliminar', $user->id, [
            'nombre' => $user->name,
            'rol'    => (int) $user->role_id,
        ]);

        $user->delete();

        return back()->with('success', "Usuario eliminado.");
    }
}
