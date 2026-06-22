@extends('admin.layout')

@section('title', 'All Pages')
@section('header', 'Pages')

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

        return route('admin.pages.index', $query);
    };

    $sortLink = function (string $column) use ($sort, $direction, $buildQuery) {
        $nextDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';
        return $buildQuery(['sort' => $column, 'direction' => $nextDirection]);
    };
@endphp

@section('content')
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h3 class="text-2xl font-bold text-white">All Pages</h3>
            <p class="text-gray-400 mt-1">Manage site pages with quick filtering, bulk actions, and date sorting.</p>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="bg-brand-gradient text-white px-4 py-2 rounded-lg inline-flex items-center justify-center">
            Add Page
        </a>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 text-sm">
        <div class="flex flex-wrap items-center gap-4">
            <a href="{{ $buildQuery(['status' => 'all']) }}" class="{{ $status === 'all' ? 'text-white font-semibold underline' : 'text-gray-300 hover:text-white' }}">
                All ({{ $counts['all'] ?? 0 }})
            </a>
            <a href="{{ $buildQuery(['status' => 'published']) }}" class="{{ $status === 'published' ? 'text-green-300 font-semibold underline' : 'text-green-400 hover:text-green-300' }}">
                Published ({{ $counts['published'] ?? 0 }})
            </a>
            <a href="{{ $buildQuery(['status' => 'draft']) }}" class="{{ $status === 'draft' ? 'text-yellow-300 font-semibold underline' : 'text-yellow-400 hover:text-yellow-300' }}">
                Drafts ({{ $counts['draft'] ?? 0 }})
            </a>
        </div>
        <div class="flex items-center gap-2 w-full lg:w-auto lg:justify-end">
            <input type="text" name="q" form="pageFilterForm" value="{{ $search }}" placeholder="Search pages..." class="w-full lg:w-80 bg-gray-800 border border-gray-700 text-white rounded-lg px-4 py-2">
            <button type="submit" form="pageFilterForm" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">Search</button>
        </div>
    </div>

    @if($errors->has('pages'))
        <div class="bg-red-900 border border-red-700 text-red-100 px-4 py-3 rounded-lg">
            {{ $errors->first('pages') }}
        </div>
    @endif

    <div class="bg-gray-800 border border-gray-700 rounded-xl overflow-hidden">
        <form id="pageFilterForm" method="GET" action="{{ route('admin.pages.index') }}">
            <input type="hidden" name="status" value="{{ $status }}">
        </form>

        <div class="p-4 border-b border-gray-700 flex flex-wrap items-center gap-3">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="selectAllPages" class="rounded border-gray-600 bg-gray-700">
                <span class="text-sm text-gray-300">Select all</span>
            </div>
            <select name="action" form="pageBulkForm" required class="w-full md:w-56 bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2">
                <option value="">Bulk actions</option>
                <option value="publish">Publish</option>
                <option value="draft">Move to Draft</option>
                <option value="delete">Delete Permanently</option>
            </select>
            <button type="submit" form="pageBulkForm" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg" onclick="return confirm('Apply this action to selected pages?')">
                Apply
            </button>
            <select name="month" form="pageFilterForm" class="w-full md:w-40 bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2">
                <option value="">All dates</option>
                @foreach($months as $monthOption)
                    <option value="{{ $monthOption }}" {{ $month === $monthOption ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::createFromFormat('Y-m', $monthOption)->format('F Y') }}
                    </option>
                @endforeach
            </select>
            <select name="sort" form="pageFilterForm" class="w-full md:w-44 bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2">
                <option value="updated_at" {{ $sort === 'updated_at' ? 'selected' : '' }}>Last Modified</option>
                <option value="created_at" {{ $sort === 'created_at' ? 'selected' : '' }}>Date Created</option>
                <option value="title" {{ $sort === 'title' ? 'selected' : '' }}>Title</option>
            </select>
            <select name="direction" form="pageFilterForm" class="w-full md:w-36 bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2">
                <option value="desc" {{ $direction === 'desc' ? 'selected' : '' }}>Descending</option>
                <option value="asc" {{ $direction === 'asc' ? 'selected' : '' }}>Ascending</option>
            </select>
            <select name="per_page" form="pageFilterForm" class="w-full md:w-36 bg-gray-900 border border-gray-700 text-white rounded-lg px-4 py-2">
                <option value="10" {{ (int) $perPage === 10 ? 'selected' : '' }}>10 per page</option>
                <option value="20" {{ (int) $perPage === 20 ? 'selected' : '' }}>20 per page</option>
                <option value="50" {{ (int) $perPage === 50 ? 'selected' : '' }}>50 per page</option>
                <option value="100" {{ (int) $perPage === 100 ? 'selected' : '' }}>100 per page</option>
            </select>
            <button type="submit" form="pageFilterForm" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">Filter</button>
            <a href="{{ route('admin.pages.index') }}" class="bg-gray-900 hover:bg-gray-700 text-gray-200 px-4 py-2 rounded-lg">Reset</a>
        </div>

        <form id="pageBulkForm" method="POST" action="{{ route('admin.pages.bulk') }}">
            @csrf

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-700">
                    <thead class="bg-gray-900/70">
                        <tr>
                            <th class="px-4 py-3 text-left"></th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                <a href="{{ $sortLink('title') }}" class="hover:text-white">Title</a>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Slug</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                <a href="{{ $sortLink('updated_at') }}" class="hover:text-white">Date</a>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        @forelse($pages as $page)
                            <tr class="group hover:bg-gray-700/30">
                                <td class="px-4 py-4 align-top">
                                    <input type="checkbox" name="page_ids[]" value="{{ $page->id }}" class="page-checkbox rounded border-gray-600 bg-gray-700">
                                </td>
                                <td class="px-6 py-4 align-top">
                                    <div class="font-medium text-white">{{ $page->title }}</div>
                                    <div class="text-xs text-gray-400 mt-1 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                                        <a href="{{ route('admin.pages.edit', $page) }}" class="text-blue-400 hover:text-blue-300">Edit</a>
                                        <span class="mx-1 text-gray-600">|</span>
                                        <a href="{{ route('pages.show', $page) }}" target="_blank" class="text-indigo-400 hover:text-indigo-300">View</a>
                                        <span class="mx-1 text-gray-600">|</span>
                                        <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" class="inline" onsubmit="return confirm('Delete this page permanently?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                                        </form>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-300 font-mono align-top">/pages/{{ $page->slug }}</td>
                                <td class="px-6 py-4 align-top">
                                    <span class="px-2 py-1 rounded-full text-xs {{ $page->is_published ? 'bg-green-900 text-green-200' : 'bg-yellow-900 text-yellow-200' }}">
                                        {{ $page->is_published ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-300 align-top">
                                    <div>Updated {{ $page->updated_at->format('M d, Y') }}</div>
                                    <div class="text-xs text-gray-500 mt-1">Created {{ $page->created_at->format('M d, Y') }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                                    No pages found for the current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        @if($pages->hasPages())
            <div class="p-4 border-t border-gray-700">
                {{ $pages->links() }}
            </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAllPages');
    const checkboxes = document.querySelectorAll('.page-checkbox');

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

