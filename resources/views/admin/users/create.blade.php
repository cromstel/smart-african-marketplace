@extends('admin.layout')

@section('title', 'Add User')
@section('header', 'Add User')

@section('content')
<div class="max-w-3xl">
    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-300 mb-2">First Name</label>
                    <input type="text" name="firstname" value="{{ old('firstname') }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Last Name</label>
                    <input type="text" name="lastname" value="{{ old('lastname') }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-300 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>

            <div>
                <label class="block text-sm text-gray-300 mb-2">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Assigned Roles</label>
                    <select name="roles[]" multiple required class="w-full h-36 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        @foreach($availableRoles as $roleOption)
                            <option value="{{ $roleOption->slug }}" {{ in_array($roleOption->slug, old('roles', ['customer']), true) ? 'selected' : '' }}>
                                {{ $roleOption->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Hold Ctrl/Cmd to select multiple roles.</p>
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Primary Role</label>
                    <select name="primary_role" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="customer" {{ old('primary_role', 'customer') === 'customer' ? 'selected' : '' }}>Customer</option>
                        <option value="vendor" {{ old('primary_role') === 'vendor' ? 'selected' : '' }}>Vendor</option>
                        <option value="salon_owner" {{ old('primary_role') === 'salon_owner' ? 'selected' : '' }}>Salon Owner</option>
                        <option value="admin" {{ old('primary_role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm text-gray-300 mb-2">Status</label>
                    <select name="is_active" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                        <option value="1" {{ old('is_active', '1') === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ old('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Password</label>
                    <input type="password" name="password" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm text-gray-300 mb-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" required class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                </div>
            </div>

            @if($errors->any())
                <div class="bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.users.index') }}" class="bg-gray-700 hover:bg-gray-600 text-white px-5 py-2 rounded-lg">Cancel</a>
            <button type="submit" class="bg-brand-gradient text-white px-5 py-2 rounded-lg">Create User</button>
        </div>
    </form>
</div>
@endsection

