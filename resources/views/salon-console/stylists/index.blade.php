@extends('layouts.salon-console')

@section('title', 'Stylists - Salon Console')

@section('content')
<div class="flex flex-col lg:flex-row">
    <div class="lg:w-1/2">
        <h2 class="text-lg font-semibold text-white mb-6">Manage Stylists</h2>
    </div>
    <div class="lg:w-1/2 flex justify-end mt-4 lg:mt-0">
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded text-sm font-medium transition-colors" disabled>
            Add Stylist (Coming Soon)
        </button>
    </div>
</div>

<div class="bg-gray-800 border border-gray-700 rounded-lg p-6 mt-6">
    <table class="w-full text-left">
        <thead>
            <tr class="border-b border-gray-700">
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Stylist Name</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Specialty</th>
                <th class="py-3 px-4 text-gray-400 text-sm font-medium">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stylists as $stylist)
            <tr class="border-b border-gray-700">
                <td class="py-3 px-4 text-white">{{ $stylist->name }}</td>
                <td class="py-3 px-4 text-white">{{ $stylist->specialty }}</td>
                <td class="py-3 px-4 text-white">
                    <span class="px-2 py-1 bg-green-900/30 text-green-400 rounded text-xs">Active</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="py-4 text-center text-gray-400">No stylists found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
