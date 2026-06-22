<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query()->latest();

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $role = $request->query('role');
        if (in_array($role, ['admin', 'vendor', 'salon_owner', 'customer'], true)) {
            $query->where(function ($builder) use ($role) {
                $builder->where('role', $role)
                    ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', $role));
            });
        }

        $status = $request->query('status');
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $sort = $request->query('sort', 'created_at');
        $direction = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (in_array($sort, ['name', 'email', 'created_at'], true)) {
            $query->orderBy($sort, $direction);
        }

        $perPage = (int) $request->query('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $users = $query->with('roles')->paginate($perPage)->withQueryString();
        $counts = $this->getUserCounts();

        return view('admin.users.index', compact('users', 'search', 'role', 'status', 'counts', 'sort', 'direction', 'perPage'));
    }

    public function create(): View
    {
        $availableRoles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('availableRoles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:255'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', Rule::in(['admin', 'vendor', 'salon_owner', 'customer'])],
            'primary_role' => ['required', Rule::in(['admin', 'vendor', 'salon_owner', 'customer'])],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! in_array($validated['primary_role'], $validated['roles'], true)) {
            return back()->withErrors(['primary_role' => 'Primary role must be included in assigned roles.'])->withInput();
        }

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'firstname' => $validated['firstname'] ?? null,
                'lastname' => $validated['lastname'] ?? null,
                'name' => $this->resolveDisplayName(
                    $validated['firstname'] ?? null,
                    $validated['lastname'] ?? null,
                    $validated['name'] ?? null
                ),
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'role' => $validated['primary_role'],
                'is_active' => (bool) ($validated['is_active'] ?? false),
                'password' => $validated['password'],
                'email_verified_at' => now(),
            ]);

            $user->assignRoles($validated['roles']);
            $this->ensureVendorProfileForRole($user);
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $availableRoles = Role::orderBy('name')->get();
        $assignedRoles = $user->roles()->pluck('slug')->all();
        if (empty($assignedRoles) && $user->role) {
            $assignedRoles = [$user->role];
        }

        return view('admin.users.edit', compact('user', 'availableRoles', 'assignedRoles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'firstname' => ['nullable', 'string', 'max:255'],
            'lastname' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:255'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', Rule::in(['admin', 'vendor', 'salon_owner', 'customer'])],
            'primary_role' => ['required', Rule::in(['admin', 'vendor', 'salon_owner', 'customer'])],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (! in_array($validated['primary_role'], $validated['roles'], true)) {
            return back()->withErrors(['primary_role' => 'Primary role must be included in assigned roles.'])->withInput();
        }

        $targetIsActive = (bool) ($validated['is_active'] ?? false);
        $targetRole = $validated['primary_role'];

        if ((int) auth()->id() === (int) $user->id && ! $targetIsActive) {
            return back()->withErrors(['user' => 'You cannot deactivate your own account.'])->withInput();
        }

        if ($this->wouldRemoveLastActiveAdmin($user, $targetRole, $targetIsActive, $validated['roles'])) {
            return back()->withErrors(['user' => 'You cannot deactivate or demote the last active admin account.'])->withInput();
        }

        DB::transaction(function () use ($user, $validated, $targetRole, $targetIsActive) {
            $payload = [
                'firstname' => $validated['firstname'] ?? null,
                'lastname' => $validated['lastname'] ?? null,
                'name' => $this->resolveDisplayName(
                    $validated['firstname'] ?? null,
                    $validated['lastname'] ?? null,
                    $validated['name'] ?? null
                ),
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'role' => $targetRole,
                'is_active' => $targetIsActive,
            ];

            if (! empty($validated['password'])) {
                $payload['password'] = $validated['password'];
            }

            $user->update($payload);
            $user->assignRoles($validated['roles']);
            $this->ensureVendorProfileForRole($user);

            if (! $targetIsActive && $user->vendor) {
                $user->vendor->update(['is_active' => false]);
            }
        });

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $targetIsActive = (bool) $validated['is_active'];

        if ((int) auth()->id() === (int) $user->id && ! $targetIsActive) {
            return back()->withErrors(['user' => 'You cannot deactivate your own account.']);
        }

        if ($this->wouldRemoveLastActiveAdmin($user, $user->role, $targetIsActive, $user->roles()->pluck('slug')->all())) {
            return back()->withErrors(['user' => 'You cannot deactivate the last active admin account.']);
        }

        $user->update(['is_active' => $targetIsActive]);

        if (! $targetIsActive && $user->vendor) {
            $user->vendor->update(['is_active' => false]);
        }

        return back()->with('success', 'User status updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ((int) auth()->id() === (int) $user->id) {
            return back()->withErrors(['user' => 'You cannot deactivate your own account.']);
        }

        if ($this->wouldRemoveLastActiveAdmin($user, $user->role, false, $user->roles()->pluck('slug')->all())) {
            return back()->withErrors(['user' => 'You cannot deactivate the last active admin account.']);
        }

        $user->update(['is_active' => false]);

        if ($user->vendor) {
            $user->vendor->update(['is_active' => false]);
        }

        return back()->with('success', 'User has been deactivated successfully.');
    }

    public function sendResetLink(User $user): RedirectResponse
    {
        $result = Password::broker()->sendResetLink(['email' => $user->email]);

        if ($result !== Password::RESET_LINK_SENT) {
            return back()->withErrors(['user' => 'Failed to send password reset link.']);
        }

        return back()->with('success', "Password reset link sent to {$user->email}.");
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in([
                'activate',
                'deactivate',
                'soft_delete',
                'set_role_admin',
                'set_role_vendor',
                'set_role_salon_owner',
                'set_role_customer',
                'send_reset_link',
            ])],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $users = User::whereIn('id', $validated['user_ids'])->get();
        $processed = 0;
        $skipped = 0;

        foreach ($users as $user) {
            if ($this->cannotApplyBulkAction($user, $validated['action'])) {
                $skipped++;

                continue;
            }

            if (str_starts_with($validated['action'], 'set_role_')) {
                $targetRole = str_replace('set_role_', '', $validated['action']);
                $user->update(['role' => $targetRole]);
                $user->assignRoles([$targetRole]);
                $this->ensureVendorProfileForRole($user);
                $processed++;

                continue;
            }

            switch ($validated['action']) {
                case 'activate':
                    $user->update(['is_active' => true]);
                    if ($user->vendor) {
                        $user->vendor->update(['is_active' => true]);
                    }
                    $processed++;
                    break;
                case 'deactivate':
                case 'soft_delete':
                    $user->update(['is_active' => false]);
                    if ($user->vendor) {
                        $user->vendor->update(['is_active' => false]);
                    }
                    $processed++;
                    break;
                case 'send_reset_link':
                    $result = Password::broker()->sendResetLink(['email' => $user->email]);
                    if ($result === Password::RESET_LINK_SENT) {
                        $processed++;
                    } else {
                        $skipped++;
                    }
                    break;
            }
        }

        if ($processed === 0) {
            return back()->withErrors(['user' => 'No users were updated.']);
        }

        $message = "{$processed} user(s) updated successfully.";
        if ($skipped > 0) {
            $message .= " {$skipped} skipped due to safety constraints.";
        }

        return back()->with('success', $message);
    }

    private function resolveDisplayName(?string $firstName, ?string $lastName, ?string $fallbackName): string
    {
        $fullName = trim(sprintf('%s %s', $firstName ?? '', $lastName ?? ''));

        if ($fullName !== '') {
            return $fullName;
        }

        return $fallbackName ?: 'User';
    }

    private function ensureVendorProfileForRole(User $user): void
    {
        if (! $user->hasAnyRole(['vendor', 'salon_owner'])) {
            return;
        }

        Vendor::updateOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => $user->name ?: $user->email,
                'business_email' => $user->email,
                'business_phone' => $user->phone ?: '0000000000',
                'business_address' => $user->address ?: 'Pending vendor address update',
                'status' => 'pending',
                'is_active' => false,
                'commission_rate' => 15.00,
            ]
        );
    }

    private function wouldRemoveLastActiveAdmin(User $user, string $targetRole, bool $targetIsActive, array $targetRoles = []): bool
    {
        if (! $user->hasRole('admin') || ! $user->is_active) {
            return false;
        }

        $targetRoles = empty($targetRoles) ? [$targetRole] : $targetRoles;
        $remainsActiveAdmin = in_array('admin', $targetRoles, true) && $targetIsActive;
        if ($remainsActiveAdmin) {
            return false;
        }

        $otherActiveAdmins = User::query()
            ->where('is_active', true)
            ->where('id', '!=', $user->id)
            ->where(function ($query) {
                $query->where('role', 'admin')
                    ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', 'admin'));
            })
            ->count();

        return $otherActiveAdmins === 0;
    }

    private function getUserCounts(): array
    {
        return [
            'all' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'admin' => User::where(function ($query) {
                $query->where('role', 'admin')
                    ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', 'admin'));
            })->count(),
            'vendor' => User::where(function ($query) {
                $query->whereIn('role', ['vendor', 'salon_owner'])
                    ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->whereIn('slug', ['vendor', 'salon_owner']));
            })->count(),
            'customer' => User::where(function ($query) {
                $query->where('role', 'customer')
                    ->orWhereHas('roles', fn ($roleQuery) => $roleQuery->where('slug', 'customer'));
            })->count(),
        ];
    }

    private function cannotApplyBulkAction(User $user, string $action): bool
    {
        if ((int) auth()->id() === (int) $user->id && in_array($action, ['deactivate', 'soft_delete', 'set_role_vendor', 'set_role_salon_owner', 'set_role_customer'], true)) {
            return true;
        }

        if (in_array($action, ['deactivate', 'soft_delete'], true) &&
            $this->wouldRemoveLastActiveAdmin($user, $user->role, false, $user->roles()->pluck('slug')->all())) {
            return true;
        }

        if (str_starts_with($action, 'set_role_')) {
            $targetRole = str_replace('set_role_', '', $action);
            if ($this->wouldRemoveLastActiveAdmin($user, $targetRole, (bool) $user->is_active, [$targetRole])) {
                return true;
            }
        }

        return false;
    }
}
