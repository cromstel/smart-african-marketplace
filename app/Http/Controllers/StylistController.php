<?php

namespace App\Http\Controllers;

use App\Models\SalonStylist;
use App\Services\SeoService;
use Illuminate\Http\Request;

class StylistController extends Controller
{
    public function index(Request $request)
    {
        $query = SalonStylist::with(['salon'])
            ->where('is_active', true);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('bio', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('salon')) {
            $query->where('salon_id', $request->salon);
        }

        $stylists = $query->latest()->paginate(12)->withQueryString();

        $seo = app(SeoService::class);
        $seo->setTitle('Salon Stylists')
            ->setDescription('Browse professional salon stylists and book services.')
            ->setCanonical(route('stylists.index'));

        return view('stylists.index', compact('stylists'));
    }

    public function show(SalonStylist $stylist)
    {
        if (! $stylist->is_active) {
            abort(404);
        }

        $stylist->load(['salon', 'services' => function ($query) {
            $query->active()->with('category');
        }]);

        $seo = app(SeoService::class);
        $seo->setTitle($stylist->name)
            ->setDescription($stylist->bio ? substr(strip_tags($stylist->bio), 0, 160) : 'View stylist profile and services.')
            ->setCanonical(route('stylists.show', $stylist->id));

        return view('stylists.show', compact('stylist'));
    }
}
