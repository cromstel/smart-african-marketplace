@extends('admin.layout')

@section('title', 'Widgets')
@section('header', 'Widgets')

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
        return route('admin.widgets.index', $query);
    };
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-white">Widgets</h3>
            <p class="text-gray-400 mt-1">Manage widgets by page and region.</p>
        </div>
        <a href="{{ route('admin.widgets.create') }}" class="bg-brand-gradient text-white px-4 py-2 rounded-lg">Add Widget</a>
    </div>

    <div class="flex flex-wrap items-center gap-4 text-sm">
        <a href="{{ $buildQuery(['status' => 'all']) }}" class="{{ $status === 'all' ? 'text-white font-semibold underline' : 'text-gray-300 hover:text-white' }}">
            All ({{ $counts['all'] ?? 0 }})
        </a>
        <a href="{{ $buildQuery(['status' => 'active']) }}" class="{{ $status === 'active' ? 'text-green-300 font-semibold underline' : 'text-green-400 hover:text-green-300' }}">
            Active ({{ $counts['active'] ?? 0 }})
        </a>
        <a href="{{ $buildQuery(['status' => 'inactive']) }}" class="{{ $status === 'inactive' ? 'text-yellow-300 font-semibold underline' : 'text-yellow-400 hover:text-yellow-300' }}">
            Inactive ({{ $counts['inactive'] ?? 0 }})
        </a>
    </div>

    <form id="widgetFilterForm" method="GET" action="{{ route('admin.widgets.index') }}">
        <input type="hidden" name="status" value="{{ $status }}">
    </div>

    <div class="bg-gray-800 rounded-lg border border-gray-700 p-4">
        <div class="flex flex-wrap items-center gap-3">
            <select name="action" form="bulkWidgetForm" required class="w-full md:w-52 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                <option value="">Bulk actions</option>
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
                <option value="delete">Delete</option>
            </select>
            <button type="submit" form="bulkWidgetForm" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg" onclick="return confirm('Apply action to selected widgets?')">Apply</button>

            <input type="text" name="q" form="widgetFilterForm" value="{{ $search }}" placeholder="Search widgets..." class="w-full md:w-64 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
            <select name="page" form="widgetFilterForm" class="w-full md:w-44 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                <option value="">All Pages</option>
                @foreach($pages as $pageOption)
                    <option value="{{ $pageOption }}" {{ $page === $pageOption ? 'selected' : '' }}>{{ $pageOption }}</option>
                @endforeach
            </select>
            <select name="region" form="widgetFilterForm" class="w-full md:w-36 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                <option value="">All Regions</option>
                @foreach(['header', 'main', 'footer'] as $regionOption)
                    <option value="{{ $regionOption }}" {{ $region === $regionOption ? 'selected' : '' }}>{{ ucfirst($regionOption) }}</option>
                @endforeach
            </select>
            <select name="type" form="widgetFilterForm" class="w-full md:w-44 bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2">
                <option value="">All Types</option>
                @foreach($types as $typeOption)
                    <option value="{{ $typeOption }}" {{ $type === $typeOption ? 'selected' : '' }}>{{ $typeOption }}</option>
                @endforeach
            </select>
            <button type="submit" form="widgetFilterForm" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">Filter</button>
            <a href="{{ route('admin.widgets.index') }}" class="bg-gray-900 hover:bg-gray-700 text-gray-200 px-4 py-2 rounded-lg">Reset</a>
            <a href="{{ route('admin.widgets.preview') }}" class="bg-gray-900 hover:bg-gray-700 text-gray-200 px-4 py-2 rounded-lg">Preview</a>
            <button type="submit" form="reorderWidgetForm" class="bg-gray-900 hover:bg-gray-700 text-gray-200 px-4 py-2 rounded-lg">Update Order</button>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.widgets.bulk') }}" id="bulkWidgetForm" class="space-y-4">
        @csrf
        <div class="bg-gray-800 rounded-lg border border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left"><input type="checkbox" id="selectAllWidgets" class="rounded border-gray-600 bg-gray-700"></th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Title</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Page</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Region</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Order</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @forelse($widgets as $widget)
                            <tr class="group hover:bg-gray-700/30">
                                <td class="px-4 py-3">
                                    <input type="checkbox" name="widget_ids[]" value="{{ $widget->id }}" class="widget-checkbox rounded border-gray-600 bg-gray-700">
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-white font-medium">{{ $widget->title ?: 'Untitled Widget' }}</div>
                                    <div class="text-xs text-gray-400 mt-1 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('admin.widgets.edit', $widget) }}" class="text-blue-400 hover:text-blue-300">Edit</a>
                                        <span class="mx-1 text-gray-600">|</span>
                                        <form action="{{ route('admin.widgets.destroy', $widget) }}" method="POST" class="inline" onsubmit="return confirm('Delete this widget?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                                        </form>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-gray-300">{{ $widget->page_identifier }}</td>
                                <td class="px-4 py-3 text-gray-300 capitalize">{{ $widget->region }}</td>
                                <td class="px-4 py-3 text-gray-300">{{ $widget->type }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs {{ $widget->is_active ? 'bg-green-900 text-green-200' : 'bg-gray-700 text-gray-300' }}">
                                        {{ $widget->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="number" form="reorderWidgetForm" name="order[{{ $widget->id }}]" value="{{ $widget->sort_order }}" class="w-20 bg-gray-700 border border-gray-600 text-white rounded px-2 py-1 text-sm">
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400">No widgets found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.widgets.reorder') }}" id="reorderWidgetForm">@csrf</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAllWidgets');
    const checkboxes = document.querySelectorAll('.widget-checkbox');

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

