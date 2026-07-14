<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class Seo extends Component
{
    /**
     * The page title.
     */
    public string $title;

    /**
     * The page description.
     */
    public ?string $description;

    /**
     * The page keywords.
     */
    public ?string $keywords;

    /**
     * The canonical URL.
     */
    public ?string $canonical;

    /**
     * The OG image URL.
     */
    public ?string $image;

    /**
     * The OG type (website, article, etc.).
     */
    public string $type;

    /**
     * Whether to index the page.
     */
    public bool $index;

    /**
     * Whether to follow links on the page.
     */
    public bool $follow;

    /**
     * Create a new component instance.
     */
    public function __construct(
        ?string $title = null,
        ?string $description = null,
        ?string $keywords = null,
        ?string $canonical = null,
        ?string $image = null,
        string $type = 'website',
        bool $index = true,
        bool $follow = true
    ) {
        $this->title = $title ?? config('app.name', 'Smart African Marketplace');
        $this->description = $description;
        $this->keywords = $keywords;
        $this->canonical = $canonical;
        $this->image = $image;
        $this->type = $type;
        $this->index = $index;
        $this->follow = $follow;
    }

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('components.seo');
    }
}
