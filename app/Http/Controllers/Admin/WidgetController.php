<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use Illuminate\Http\Request;

class WidgetController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request)
    {
        $query = Widget::query();

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('title', 'like', "%{$search}%")
                    ->orWhere('page_identifier', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        $page = trim((string) $request->query('page', ''));
        if ($page !== '') {
            $query->where('page_identifier', $page);
        }

        $region = trim((string) $request->query('region', ''));
        if (in_array($region, ['header', 'main', 'footer'], true)) {
            $query->where('region', $region);
        } else {
            $region = '';
        }

        $type = trim((string) $request->query('type', ''));
        if ($type !== '') {
            $query->where('type', $type);
        }

        $status = trim((string) $request->query('status', 'all'));
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        } else {
            $status = 'all';
        }

        $widgets = $query->orderBy('page_identifier')
            ->orderBy('region')
            ->orderBy('sort_order')
            ->get();

        $counts = [
            'all' => Widget::count(),
            'active' => Widget::where('is_active', true)->count(),
            'inactive' => Widget::where('is_active', false)->count(),
        ];
        $pages = Widget::query()->select('page_identifier')->distinct()->orderBy('page_identifier')->pluck('page_identifier');
        $types = Widget::query()->select('type')->distinct()->orderBy('type')->pluck('type');

        return view('admin.widgets.index', compact('widgets', 'search', 'page', 'region', 'type', 'status', 'counts', 'pages', 'types'));
    }

    public function preview(Request $request)
    {
        $pages = Widget::query()
            ->select('page_identifier')
            ->distinct()
            ->orderBy('page_identifier')
            ->pluck('page_identifier')
            ->values();

        $selectedPage = $request->get('page', $pages->first() ?? 'home');

        return view('admin.widgets.preview', compact('pages', 'selectedPage'));
    }

    public function create()
    {
        $widget = new Widget;
        $pageOptions = $this->pageOptions();

        return view('admin.widgets.create', compact('widget', 'pageOptions'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateWidget($request);
        $validated['is_active'] = $request->has('is_active');
        $validated['settings'] = $this->buildSettings($request);

        Widget::create($validated);

        return redirect()->route('admin.widgets.index')
            ->with('success', 'Widget created successfully');
    }

    public function edit(Widget $widget)
    {
        $pageOptions = $this->pageOptions();

        return view('admin.widgets.edit', compact('widget', 'pageOptions'));
    }

    public function update(Request $request, Widget $widget)
    {
        $validated = $this->validateWidget($request, $widget->id);
        $validated['is_active'] = $request->has('is_active');
        $validated['settings'] = $this->buildSettings($request);

        $widget->update($validated);

        return redirect()->route('admin.widgets.index')
            ->with('success', 'Widget updated successfully');
    }

    public function destroy(Widget $widget)
    {
        $widget->delete();

        return redirect()->route('admin.widgets.index')
            ->with('success', 'Widget deleted successfully');
    }

    public function reorder(Request $request)
    {
        $order = $request->input('order', []);
        foreach ($order as $id => $sortOrder) {
            Widget::where('id', (int) $id)->update(['sort_order' => (int) $sortOrder]);
        }

        return redirect()->route('admin.widgets.index')
            ->with('success', 'Widget order updated');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'widget_ids' => 'required|array|min:1',
            'widget_ids.*' => 'integer|exists:widgets,id',
        ]);

        $widgets = Widget::whereIn('id', $validated['widget_ids'])->get();
        $processed = 0;

        foreach ($widgets as $widget) {
            if ($validated['action'] === 'delete') {
                $widget->delete();
                $processed++;

                continue;
            }

            $widget->update(['is_active' => $validated['action'] === 'activate']);
            $processed++;
        }

        return redirect()->route('admin.widgets.index')
            ->with('success', "{$processed} widget(s) updated.");
    }

    private function validateWidget(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'page_identifier' => 'required|string|max:255',
            'region' => 'required|in:header,main,footer',
            'type' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer',
        ]);
    }

    private function buildSettings(Request $request): array
    {
        $json = $request->input('settings_json');
        if ($json) {
            $decoded = json_decode($json, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $request->input('settings', []);
    }

    private function pageOptions(): array
    {
        return [
            'home' => 'Home',
            'shop' => 'Shop',
            'blog-index' => 'Blog Index',
            'blog-post' => 'Blog Post',
            'salon-index' => 'Salon Index',
            'vendor-guide' => 'Vendor Guide',
            'about' => 'About',
            'contact' => 'Contact',
            'page-*' => 'Custom Page (page-<slug>)',
        ];
    }
}
