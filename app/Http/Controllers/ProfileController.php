<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        return view('profile.index');
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($request->filled('current_password')) {
            $request->validate([
                'current_password'  => ['required', 'current_password'],
                'password'          => ['required', 'confirmed', Password::min(8)],
            ], [
                'current_password.current_password' => 'A senha atual está incorreta.',
            ]);

            $user->update(['password' => Hash::make($request->password)]);

            return back()->with('success', 'Senha alterada com sucesso.');
        }

        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update($request->only('name', 'email'));

        return back()->with('success', 'Perfil atualizado com sucesso.');
    }
}
