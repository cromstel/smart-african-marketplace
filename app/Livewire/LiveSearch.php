<?php

namespace App\Livewire;

use App\Models\BlogPost;
use App\Models\Product;
use App\Models\SalonService;
use Livewire\Component;

class LiveSearch extends Component
{
    public $query = '';

    public $showDropdown = false;

    public function updatedQuery()
    {
        $this->showDropdown = strlen($this->query) >= 2;
    }

    public function render()
    {
        $products = [];
        $posts = [];
        $services = [];

        if (strlen($this->query) >= 2) {
            $products = Product::where('name', 'like', "%{$this->query}%")
                ->orWhere('description', 'like', "%{$this->query}%")
                ->where('is_active', true)
                ->take(5)
                ->get();

            $posts = BlogPost::where('title', 'like', "%{$this->query}%")
                ->orWhere('content', 'like', "%{$this->query}%")
                ->where('status', 'published')
                ->where('published_at', '<=', now())
                ->take(3)
                ->get();

            $services = SalonService::where('name', 'like', "%{$this->query}%")
                ->orWhere('description', 'like', "%{$this->query}%")
                ->where('is_active', true)
                ->take(3)
                ->get();
        }

        return view('livewire.live-search', [
            'products' => $products,
            'posts' => $posts,
            'services' => $services,
        ]);
    }

    public function clearSearch()
    {
        $this->query = '';
        $this->showDropdown = false;
    }
}
