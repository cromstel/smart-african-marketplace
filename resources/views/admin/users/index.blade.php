@extends('admin.layout')

@section('title', 'User Management')
@section('header', 'User Management')

@php
    $buildQuery = function (array $overrides = []) {
        $query = request()->query();
        foreach ($overrides as $key => $value) {
            if ($value === null || $value === '') {
                unset($query[$key]);
            } else {
                $query[$key] = $value;
            }
        }
        return route('admin.users.index', $query);
    };

    $sortLink = function (string $column) use ($sort, $direction, $buildQuery) {
        $nextDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';
        return $buildQuery(['sort' => $column, 'direction' => $nextDirection]);
    };
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center gap-4 text-sm">
        <a href="{{ $buildQuery(['role' => null, 'status' => null]) }}" class="text-gray-200 hover:text-white {{ !$role && !$status ? 'font-semibold underline' : '' }}">
            All ({{ $counts['all'] ?? 0 }})
        </a>
        <a href="{{ $buildQuery(['status' => 'active', 'role' => null]) }}" class="text-green-300 hover:text-green-200 {{ $status === 'active' ? 'font-semibold underline' : '' }}">
            Active ({{ $counts['active'] ?? 0 }})
        </a>
        <a href="{{ $buildQuery(['status' => 'inactive', 'role' => null]) }}" class="text-red-300 hover:text-red-200 {{ $status === 'inactive' ? 'font-semibold underline' : '' }}">
            Inactive ({{ $counts['inactive'] ?? 0 }})
        </a>
        <a href="{{ $buildQuery(['role' => 'admin', 'status' => null]) }}" class="text-blue-300 hover:text-blue-200 {{ $role === 'admin' ? 'font-semibold underline' : '' }}">
            Admins ({{ $counts['admin'] ?? 0 }})
        </a>
        <a href="{{ $buildQuery(['role' => 'vendor', 'status' => null]) }}" class="text-indigo-300 hover:text-indigo-200 {{ $role === 'vendor' ? 'font-semibold underline' : '' }}">
            Vendors ({{ $counts['vendor'] ?? 0 }})
        </a>
        <a href="{{ $buildQuery(['role' => 'customer', 'status' => null]) }}" class="text-orange-300 hover:text-orange-200 {{ $role === 'customer' ? 'font-semibold underline' : '' }}">
            Customers ({{ $counts['customer'] ?? 0 }})
        </a>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col md:flex-row gap-3 w-full">
            <input type="hidden" name="sort" value="{{ $sort }}">
            <input type="hidden" name="direction" value="{{ $direction }}">
            <input
                type="text"
                name="q"
                value="{{ $search }}"
                placeholder="Search users by name, email"
                class="w-full md:w-72 bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2"
            >
            <select name="role" class="w-full md:w-48 bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                <option value="">All Roles</option>
                <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="vendor" {{ $role === 'vendor' ? 'selected' : '' }}>Vendor</option>
                <option value="salon_owner" {{ $role === 'salon_owner' ? 'selected' : '' }}>Salon Owner</option>
                <option value="customer" {{ $role === 'customer' ? 'selected' : '' }}>Customer</option>
            </select>
            <select name="status" class="w-full md:w-40 bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                <option value="">All Statuses</option>
                <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <select name="per_page" class="w-full md:w-32 bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                <option value="10" {{ (int) $perPage === 10 ? 'selected' : '' }}>10</option>
                <option value="20" {{ (int) $perPage === 20 ? 'selected' : '' }}>20</option>
                <option value="50" {{ (int) $perPage === 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ (int) $perPage === 100 ? 'selected' : '' }}>100</option>
            </select>
            <button type="submit" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">Filter</button>
            <a href="{{ route('admin.users.index') }}" class="bg-gray-900 hover:bg-gray-700 text-gray-200 px-4 py-2 rounded-lg text-center">Reset</a>
        </form>

        <a href="{{ route('admin.users.create') }}" class="bg-brand-gradient text-white px-4 py-2 rounded-lg whitespace-nowrap">
            Add User
        </a>
    </div>

    @if($errors->has('user'))
        <div class="bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded">
            {{ $errors->first('user') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.bulk') }}" class="space-y-4" id="bulkActionForm">
        @csrf
        <div class="flex flex-col md:flex-row md:items-center gap-3">
            <select name="action" required class="w-full md:w-72 bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
                <option value="">Bulk actions</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="soft_delete">Soft Delete (Deactivate)</option>
                <option value="set_role_admin">Set Role: Admin</option>
                <option value="set_role_vendor">Set Role: Vendor</option>
                <option value="set_role_salon_owner">Set Role: Salon Owner</option>
                <option value="set_role_customer">Set Role: Customer</option>
                <option value="send_reset_link">Send Password Reset Link</option>
            </select>
            <button type="submit" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Apply
            </button>
        </div>

        <div class="bg-gray-800 border border-gray-700 rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <input type="checkbox" id="selectAllUsers" class="rounded border-gray-600 bg-gray-700">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                <a href="{{ $sortLink('name') }}" class="hover:text-white">User</a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Roles</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                <a href="{{ $sortLink('email') }}" class="hover:text-white">Email</a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                <a href="{{ $sortLink('created_at') }}" class="hover:text-white">Created</a>
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-700/30">
                                <td class="px-4 py-4">
                                    <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox rounded border-gray-600 bg-gray-700">
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-white">{{ $user->name }}</div>
                                    <div class="text-sm text-gray-400">{{ $user->firstname }} {{ $user->lastname }}</div>
                                </td>
                                <td class="px-6 py-4 text-gray-300">
                                    @php
                                        $roleNames = $user->roles->pluck('name')->all();
                                        if (empty($roleNames)) {
                                            $roleNames = [str_replace('_', ' ', ucfirst($user->role))];
                                        }
                                    @endphp
                                    <div>{{ implode(', ', $roleNames) }}</div>
                                    <div class="text-xs text-gray-400">Primary: {{ str_replace('_', ' ', ucfirst($user->role)) }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $user->is_active ? 'bg-green-800 text-green-100' : 'bg-red-800 text-red-100' }}">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-300 text-sm">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-gray-400 text-sm">{{ $user->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-400 hover:text-blue-300 text-sm">Edit</a>

                                        <form method="POST" action="{{ route('admin.users.status', $user) }}" class="inline">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}">
                                            <button type="submit" class="text-amber-400 hover:text-amber-300 text-sm">
                                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.users.send-reset-link', $user) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-indigo-400 hover:text-indigo-300 text-sm">
                                                Send Reset
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="text-red-400 hover:text-red-300 text-sm"
                                                onclick="return confirm('This will deactivate the user account. Continue?')"
                                            >
                                                Soft Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-400">No users found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-700">
                {{ $users->links() }}
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAllUsers');
    const checkboxes = document.querySelectorAll('.user-checkbox');

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });
        });
    }
});
</script>
@endsection

