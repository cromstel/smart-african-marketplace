<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SitePage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageManagementController extends Controller
{
    public function index(Request $request): View
    {
        $query = SitePage::query();

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $status = $request->query('status', 'all');
        if ($status === 'published') {
            $query->where('is_published', true);
        } elseif ($status === 'draft') {
            $query->where('is_published', false);
        } else {
            $status = 'all';
        }

        $month = $request->query('month');
        if (is_string($month) && preg_match('/^\d{4}-\d{2}$/', $month) === 1) {
            [$year, $monthNumber] = explode('-', $month);
            $query->whereYear('created_at', (int) $year)
                ->whereMonth('created_at', (int) $monthNumber);
        } else {
            $month = '';
        }

        $sort = $request->query('sort', 'updated_at');
        $direction = strtolower((string) $request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (! in_array($sort, ['created_at', 'updated_at', 'title'], true)) {
            $sort = 'updated_at';
        }
        $query->orderBy($sort, $direction);

        $perPage = (int) $request->query('per_page', 20);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 20;
        }

        $pages = $query->paginate($perPage)->withQueryString();
        $counts = [
            'all' => SitePage::count(),
            'published' => SitePage::where('is_published', true)->count(),
            'draft' => SitePage::where('is_published', false)->count(),
        ];
        $months = SitePage::query()
            ->whereNotNull('created_at')
            ->orderByDesc('created_at')
            ->get(['created_at'])
            ->map(fn (SitePage $page) => $page->created_at?->format('Y-m'))
            ->filter()
            ->unique()
            ->values();

        return view('admin.pages.index', compact(
            'pages',
            'search',
            'status',
            'month',
            'sort',
            'direction',
            'perPage',
            'counts',
            'months'
        ));
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePage($request);
        $validated['slug'] = $this->resolveSlug($validated['slug'] ?? null, $validated['title']);
        $validated['is_published'] = $request->boolean('is_published');

        SitePage::create($validated);

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function edit(SitePage $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, SitePage $page)
    {
        $validated = $this->validatePage($request, $page->id);
        $validated['slug'] = $this->resolveSlug($validated['slug'] ?? null, $validated['title'], $page->id);
        $validated['is_published'] = $request->boolean('is_published');

        $page->update($validated);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function destroy(SitePage $page)
    {
        $page->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted successfully.');
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['publish', 'draft', 'delete'])],
            'page_ids' => ['required', 'array', 'min:1'],
            'page_ids.*' => ['integer', 'exists:site_pages,id'],
        ]);

        $pages = SitePage::whereIn('id', $validated['page_ids'])->get();
        if ($pages->isEmpty()) {
            return back()->withErrors(['pages' => 'No valid pages selected.']);
        }

        $processed = 0;

        foreach ($pages as $page) {
            switch ($validated['action']) {
                case 'publish':
                    $page->update(['is_published' => true]);
                    $processed++;
                    break;
                case 'draft':
                    $page->update(['is_published' => false]);
                    $processed++;
                    break;
                case 'delete':
                    $page->delete();
                    $processed++;
                    break;
            }
        }

        if ($processed === 0) {
            return back()->withErrors(['pages' => 'No pages were updated.']);
        }

        return back()->with('success', "{$processed} page(s) updated successfully.");
    }

    private function validatePage(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('site_pages', 'slug')->ignore($ignoreId)],
            'excerpt' => ['nullable', 'string'],
            'content' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);
    }

    private function resolveSlug(?string $slug, string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug ?: $title);
        $candidate = $base;
        $counter = 1;

        while (SitePage::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $candidate)
            ->exists()) {
            $candidate = $base.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }
}
