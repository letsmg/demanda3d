<?php

namespace App\Http\Controllers\Inertia;

use App\Enums\UserAccessLevel;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    /**
     * Lista todos os usuários (admin + vendedores staff + customers).
     * Exclusivo para Admin (access_level = 10).
     */
    public function index(): Response
    {
        $users = User::orderBy('access_level', 'asc')
            ->orderBy('display_name', 'asc')
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'display_name' => $user->display_name ?? $user->email,
                'email' => $user->email,
                'access_level' => $user->access_level->value,
                'access_label' => $user->access_level->label(),
                'is_active' => (bool) $user->is_active,
                'created_at' => $user->created_at?->format('Y-m-d'),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Atualiza dados básicos de um vendedor (Admin).
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'access_level' => ['required', 'integer', Rule::in(UserAccessLevel::staffPanelValues())],
        ]);

        $user->update($validated);

        return back()->with('success', 'Vendedor atualizado com sucesso.');
    }

    /**
     * Alterna o estado ativo/bloqueado de um vendedor.
     *
     * Admins (access_level = 10) não podem ser bloqueados.
     */
    public function toggle(User $user): RedirectResponse
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Não é possível bloquear um Administrador.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        $status = $user->is_active ? 'ativado' : 'bloqueado';

        return back()->with('success', "Vendedor {$status} com sucesso.");
    }

    /**
     * Reseta a senha de um vendedor para uma senha aleatória segura.
     */
    public function resetPassword(User $user): RedirectResponse
    {
        $newPassword = Str::password(16, true, true, true);

        $user->update(['password' => Hash::make($newPassword)]);

        return back()->with('success', "Senha resetada com sucesso. Nova senha: {$newPassword}")
            ->with('reset_password', $newPassword)
            ->with('reset_user_id', $user->id);
    }
}