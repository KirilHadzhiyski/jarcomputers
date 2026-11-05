<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->latest()->paginate(15),
            'contactChannels' => User::CONTACT_CHANNEL_LABELS,
            'accountStatuses' => User::ACCOUNT_STATUS_LABELS,
            'seo' => [
                'title' => 'Admin потребители',
                'description' => 'Управление на потребители и техните данни за контакт.',
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'contactChannels' => User::CONTACT_CHANNEL_LABELS,
            'accountStatuses' => User::ACCOUNT_STATUS_LABELS,
            'seo' => [
                'title' => 'Нов потребител',
                'description' => 'Създаване на нов потребител.',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);
        $validated = $this->normalizeAccountAccess($request, $validated);
        $validated['email_verified_at'] = now();
        $validated['email_verification_code'] = null;
        $validated['email_verification_expires_at'] = null;

        $user = User::query()->create($validated);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'Потребителят беше създаден.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'contactChannels' => User::CONTACT_CHANNEL_LABELS,
            'accountStatuses' => User::ACCOUNT_STATUS_LABELS,
            'seo' => [
                'title' => "Редакция на {$user->name}",
                'description' => 'Редакция на потребител и настройките му за контакт.',
            ],
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validated($request, $user);
        $validated = $this->normalizeAccountAccess($request, $validated, $user);

        abort_if($user->id === $request->user()->id && $validated['account_status'] !== 'active', 422, 'Не можете да спрете собствения си профил.');

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', 'Потребителят беше обновен.');
    }

    public function destroy(User $user, Request $request): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 422, 'Не можете да изтриете собствения си профил.');

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Потребителят беше изтрит.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'phone' => ['nullable', 'string', 'max:40'],
            'preferred_contact_channel' => ['required', 'in:email,phone'],
            'role' => ['required', 'in:user,admin'],
            'account_status' => ['sometimes', Rule::in(array_keys(User::ACCOUNT_STATUS_LABELS))],
            'suspended_until' => ['nullable', 'date'],
            'account_notes' => ['nullable', 'string', 'max:5000'],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
        ]);
    }

    private function normalizeAccountAccess(Request $request, array $validated, ?User $user = null): array
    {
        $status = $validated['account_status'] ?? $user?->account_status ?? 'active';
        $validated['account_status'] = $status;
        $validated['admin_metadata'] = [
            'notes' => $validated['account_notes'] ?? ($user?->admin_metadata['notes'] ?? null),
        ];
        unset($validated['account_notes']);

        if ($status === 'active') {
            $validated['suspended_until'] = null;
            $validated['deactivated_at'] = null;
        } elseif ($status === 'suspended') {
            $validated['deactivated_at'] = null;
            $validated['suspended_until'] = $validated['suspended_until'] ?? null;
        } else {
            $validated['suspended_until'] = null;
            $validated['deactivated_at'] = $user?->deactivated_at ?? now();
        }

        return $validated;
    }
}
