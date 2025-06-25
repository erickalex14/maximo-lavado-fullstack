<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Mostrar vista de usuarios
    public function indexView()
    {
        return view('usuarios.index');
    }

    // Listar usuarios (API)
    public function index()
    {
        $users = User::select('id', 'name', 'email', 'created_at')->get();
        return response()->json([
            'users' => $users
        ]);
    }

    // Crear usuario
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'message' => 'Usuario creado exitosamente',
            'user' => $user->only(['id', 'name', 'email', 'created_at'])
        ], 201);
    }

    // Mostrar usuario específico
    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json([
            'user' => $user->only(['id', 'name', 'email', 'created_at'])
        ]);
    }

    // Actualizar usuario
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'user' => $user->only(['id', 'name', 'email', 'created_at'])
        ]);
    }

    // Eliminar usuario
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // No permitir eliminar el usuario actual
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'No puedes eliminar tu propio usuario'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente'
        ]);
    }

    // Mostrar perfil del usuario actual
    public function profile()
    {
        return view('usuarios.profile');
    }

    // Actualizar perfil del usuario actual
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'current_password' => 'required_with:password',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        // Verificar contraseña actual si se quiere cambiar
        if (!empty($validated['password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return response()->json([
                    'message' => 'La contraseña actual es incorrecta'
                ], 422);
            }
        }

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'user' => $user->only(['id', 'name', 'email'])
        ]);
    }

    // Mostrar formulario de creación de usuario
    public function create()
    {
        return view('usuarios.create');
    }

    // Mostrar formulario de edición de usuario
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('usuarios.edit', compact('user'));
    }

    // Actualizar contraseña del usuario actual
    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        
        $validated = $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Verificar contraseña actual
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta'
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return response()->json([
            'message' => 'Contraseña actualizada exitosamente'
        ]);
    }
}
