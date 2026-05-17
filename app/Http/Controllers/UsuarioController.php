<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UsuarioController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $usuarios = User::where('empresa_id', $empresaId)
            ->orderBy('name')
            ->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create()
    {
        return view('usuarios.create');
    }

    public function store(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'role'     => ['in:admin,user'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'empresa_id' => $empresaId,
            'role'       => $data['role'] ?? 'user',
            'ativo'      => true,
        ]);

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuário criado com sucesso.');
    }

    public function edit(User $usuario)
    {
        $this->authorizeUser($usuario);
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, User $usuario)
    {
        $this->authorizeUser($usuario);

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $usuario->id],
            'role'  => ['in:admin,user'],
            'ativo' => ['boolean'],
        ]);

        $usuario->update([
            'name'  => $data['name'],
            'email' => $data['email'],
            'role'  => $data['role'] ?? $usuario->role,
            'ativo' => $request->boolean('ativo', true),
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => ['confirmed', Password::min(8)]]);
            $usuario->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuário atualizado.');
    }

    public function destroy(User $usuario)
    {
        $this->authorizeUser($usuario);
        abort_if($usuario->id === auth()->id(), 403, 'Não é possível excluir sua própria conta.');
        $usuario->update(['ativo' => false]);
        return back()->with('success', 'Usuário desativado.');
    }

    private function authorizeUser(User $usuario): void
    {
        abort_if($usuario->empresa_id !== auth()->user()->empresa_id, 403);
    }
}
