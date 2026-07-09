<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\User;
use App\Services\ImageModerationService;
use App\Services\ImageOptimizationService;
use App\Services\UserService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(private UserService $userService) {}

    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $tenant = $user->tenant;

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'tenant' => $tenant ? [
                'company_name' => $tenant->company_name,
                'fantasy_name' => $tenant->fantasy_name,
                'document' => $tenant->document,
                'phone' => $tenant->phone,
                'address' => $tenant->address,
                'number' => $tenant->number,
                'district' => $tenant->district,
                'city' => $tenant->city,
                'state' => $tenant->state,
                'zipcode' => $tenant->zipcode,
                'logo_url' => $tenant->logo_url,
                'banner_url' => $tenant->banner_url,
                'fantasy_slug' => $tenant->fantasy_slug,
            ] : null,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        $emailChanged = $user->email !== $request->input('email');

        $this->userService->update($user, $request->validated());

        if ($emailChanged) {
            DB::table('users')->where('id', $user->id)->update(['email_verified_at' => null]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('profile.edit');
    }

    /**
     * Update the tenant (store) profile data including logo and banner.
     */
    public function updateTenant(
        Request $request,
        ImageOptimizationService $imageService,
        ImageModerationService $moderationService,
    ): RedirectResponse {
        $user = $request->user();
        $tenant = $user->tenant;

        if (!$tenant) {
            return redirect()->route('profile.edit')
                ->with('error', 'Perfil de loja não encontrado.');
        }

        $data = $request->validate([
            'company_name' => ['nullable', 'string', 'max:255'],
            'fantasy_name' => ['nullable', 'string', 'max:255'],
            'document' => ['nullable', 'string', 'max:18'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'number' => ['nullable', 'string', 'max:20'],
            'district' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'size:2'],
            'zipcode' => ['nullable', 'string', 'max:10'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'banner' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'logo.max' => 'O logo deve ter no máximo 2MB.',
            'banner.max' => 'O banner deve ter no máximo 4MB.',
        ]);

        $tenantData = [];

        // Campos de texto (aplicar paridade LGPD)
        $lgpdFields = ['company_name', 'fantasy_name', 'document', 'phone', 'address', 'number', 'district', 'city'];
        foreach ($lgpdFields as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null) {
                $tenantData = array_merge(
                    $tenantData,
                    \App\Services\EncryptionService::buildEncryptedFields([$field => $data[$field]], $field)
                );
            }
        }

        // Campos sem criptografia
        foreach (['state', 'zipcode'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null) {
                $tenantData[$field] = $data[$field];
            }
        }

        // Logo upload com moderação
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');

            $moderationResult = $moderationService->analyze($file);
            if ($moderationResult['status'] === 'rejected' || $moderationResult['category']->value === 'illegal') {
                return redirect()->route('profile.edit')
                    ->with('error', "Logo rejeitada: {$moderationResult['details']}");
            }

            // Deleta logo antiga se existir
            if ($tenant->logo_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($tenant->logo_path);
            }

            $tenantData['logo_path'] = $imageService->processTenantProfileUpload($file, $tenant->id, 'logo');
        }

        // Banner upload com moderação
        if ($request->hasFile('banner')) {
            $file = $request->file('banner');

            $moderationResult = $moderationService->analyze($file);
            if ($moderationResult['status'] === 'rejected' || $moderationResult['category']->value === 'illegal') {
                return redirect()->route('profile.edit')
                    ->with('error', "Banner rejeitado: {$moderationResult['details']}");
            }

            // Deleta banner antigo se existir
            if ($tenant->banner_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($tenant->banner_path);
            }

            $tenantData['banner_path'] = $imageService->processTenantProfileUpload($file, $tenant->id, 'banner');
        }

        if (!empty($tenantData)) {
            $tenant->update($tenantData);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Dados da loja atualizados com sucesso.']);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}