<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
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
                $w->where(function($x) use ($q) {
                    $x->where('name','like',"%{$q}%")
                      ->orWhere('email','like',"%{$q}%");
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
        $roles = DB::table('roles')->select('id','nombre')->orderBy('id')->get();

        return Inertia::render('Admin/Usuarios/Create', [
            'roles' => $roles,
        ]);
        // Cliente por defecto lo seteamos en el formulario (role_id=4)
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','string','email','max:255','unique:users,email'],
            'role_id'  => ['required','integer','exists:roles,id'],
            'password' => ['required','string','min:8','confirmed'], // incluye password_confirmation
        ]);

        $user = User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'role_id'           => $data['role_id'],
            'password'          => Hash::make($data['password']),
            'email_verified_at' => now(), // opcional
        ]);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$user->name} creado.");
    }

    public function edit(User $user): Response
    {
        $roles = DB::table('roles')->select('id','nombre')->orderBy('id')->get();

        return Inertia::render('Admin/Usuarios/Edit', [
            'user'  => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->role_id,
            ],
            'roles' => $roles,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','string','email','max:255',"unique:users,email,{$user->id}"],
            'role_id'  => ['required','integer','exists:roles,id'],
            // password opcional
            'password' => ['nullable','string','min:8','confirmed'],
        ]);

        $payload = [
            'name'    => $data['name'],
            'email'   => $data['email'],
            'role_id' => $data['role_id'],
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return redirect()->route('admin.usuarios.index')
            ->with('success', "Usuario {$user->name} actualizado.");
    }

    public function destroy(User $user)
    {
        // evita borrar el propio admin logueado si quieres: if (auth()->id() === $user->id) abort(403);
        $user->delete();

        return back()->with('success', "Usuario eliminado.");
    }
}
