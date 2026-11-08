<?php

namespace Tests\Feature\Admin;

use App\Models\AboutPageContent;
use App\Models\Announcement;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogPostRevision;
use App\Models\BlogTag;
use App\Models\BookingType;
use App\Models\Category;
use App\Models\HeroSection;
use App\Models\SalonServiceCategory;
use App\Models\SitePage;
use App\Models\User;
use App\Models\Widget;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Extends the Phase 4 N+1 audit (see AdminIndexPagesSmokeTest) from admin
 * index pages to admin show/edit pages -- a single record plus its
 * relationships is exactly where a lazy-loaded relation slips past index
 * pages (which mostly just paginate a flat list). Covers the CMS/content
 * family of controllers: static pages, taxonomy-style CRUD, and widgets.
 *
 * This file also carries fixture coverage for two real bugs this same
 * pattern surfaced and were fixed alongside it:
 *  - admin.categories.show was registered via a bare Route::resource even
 *    though CategoryController has no show() and no show.blade.php exists;
 *    it now excludes 'show', matching salon-categories/blog-categories.
 *  - AnnouncementController had no show() at all despite
 *    admin/announcements/show.blade.php already existing and being linked
 *    to from the index page; show() was added and the view's references to
 *    fields that don't exist on the current Announcement model
 *    (type_icon, target_audience, creator) were fixed to match it.
 */
class AdminContentShowEditPagesSmokeTest extends TestCase
{
    use DatabaseTransactions;

    #[DataProvider('showEditRoutes')]
    public function test_admin_show_edit_page_loads_without_a_server_error(string $routeName, string $fixtureMethod): void
    {
        $admin = User::factory()->admin()->create();
        $routeParams = $this->$fixtureMethod();

        $response = $this->actingAs($admin)->get(route($routeName, $routeParams));

        $response->assertOk();
    }

    public static function showEditRoutes(): array
    {
        return collect([
            'admin.about.edit' => 'aboutFixture',
            'admin.announcements.edit' => 'announcementFixture',
            'admin.announcements.show' => 'announcementFixture',
            'admin.blog.categories.edit' => 'blogCategoryFixture',
            'admin.blog.edit' => 'blogPostFixture',
            'admin.blog.tags.edit' => 'blogTagFixture',
            'admin.booking-types.edit' => 'bookingTypeFixture',
            'admin.categories.edit' => 'categoryFixture',
            'admin.hero-sections.edit' => 'heroSectionFixture',
            'admin.pages.edit' => 'sitePageFixture',
            'admin.salon-categories.edit' => 'salonCategoryFixture',
            'admin.widgets.edit' => 'widgetFixture',
        ])->map(fn ($fixtureMethod, $routeName) => [$routeName, $fixtureMethod])->all();
    }

    // --------------------------------------------------------------- Fixtures

    private function aboutFixture(): array
    {
        $about = AboutPageContent::create([
            'section_key' => 'section-'.uniqid(),
            'title' => 'About Section '.uniqid(),
            'content' => 'Fixture content.',
            'items' => ['One', 'Two'],
            'sort_order' => 0,
            'is_active' => true,
        ]);

        return ['about' => $about];
    }

    private function announcementFixture(): array
    {
        $announcement = Announcement::create([
            'title' => 'Announcement '.uniqid(),
            'content' => 'Fixture announcement body.',
            'type' => 'info',
            'target' => 'all_vendors',
            'is_active' => true,
            'is_pinned' => false,
            'published_at' => null,
            'expires_at' => null,
        ]);

        // Reads relation is eager loaded in show(); give it something to load.
        $reader = User::factory()->create();
        $announcement->markAsReadBy($reader->id);

        return ['announcement' => $announcement];
    }

    private function blogCategoryFixture(): array
    {
        $blogCategory = BlogCategory::create([
            'name' => 'Blog Category '.uniqid(),
            'slug' => 'blog-category-'.uniqid(),
            'description' => 'Fixture category.',
        ]);

        return ['blog_category' => $blogCategory];
    }

    private function blogPostFixture(): array
    {
        $author = User::factory()->admin()->create();

        $blog = BlogPost::create([
            'title' => 'Blog Post '.uniqid(),
            'slug' => 'blog-post-'.uniqid(),
            'excerpt' => 'Fixture excerpt.',
            'content' => 'Fixture content body.',
            'author_id' => $author->id,
            'status' => 'draft',
            'views' => 0,
        ]);

        $category = BlogCategory::create([
            'name' => 'Blog Category '.uniqid(),
            'slug' => 'blog-category-'.uniqid(),
        ]);
        $tag = BlogTag::create([
            'name' => 'Blog Tag '.uniqid(),
            'slug' => 'blog-tag-'.uniqid(),
        ]);
        $blog->categories()->attach($category);
        $blog->tags()->attach($tag);

        BlogPostRevision::create([
            'blog_post_id' => $blog->id,
            'user_id' => $author->id,
            'title' => $blog->title,
            'slug' => $blog->slug,
            'content' => $blog->content,
        ]);

        return ['blog' => $blog];
    }

    private function blogTagFixture(): array
    {
        $blogTag = BlogTag::create([
            'name' => 'Blog Tag '.uniqid(),
            'slug' => 'blog-tag-'.uniqid(),
        ]);

        return ['blog_tag' => $blogTag];
    }

    private function bookingTypeFixture(): array
    {
        $bookingType = BookingType::create([
            'name' => 'Booking Type '.uniqid(),
            'slug' => 'booking-type-'.uniqid(),
            'description' => 'Fixture booking type.',
            'default_duration_minutes' => 60,
            'booking_window_days' => 30,
            'cancellation_policy_hours' => 24,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return ['booking_type' => $bookingType];
    }

    private function categoryFixture(): array
    {
        $category = Category::create([
            'name' => 'Category '.uniqid(),
            'slug' => 'category-'.uniqid(),
            'description' => 'Fixture category.',
            'is_active' => true,
        ]);

        return ['category' => $category];
    }

    private function heroSectionFixture(): array
    {
        $heroSection = HeroSection::create([
            'page_identifier' => 'page-'.uniqid(),
            'title' => 'Hero '.uniqid(),
            'subtitle' => 'Fixture subtitle.',
            'background_type' => 'gradient',
            'is_active' => true,
            'order' => 0,
        ]);

        return ['hero_section' => $heroSection];
    }

    private function sitePageFixture(): array
    {
        $page = SitePage::create([
            'title' => 'Page '.uniqid(),
            'slug' => 'page-'.uniqid(),
            'excerpt' => 'Fixture excerpt.',
            'content' => 'Fixture content.',
            'is_published' => true,
            'sort_order' => 0,
        ]);

        return ['page' => $page];
    }

    private function salonCategoryFixture(): array
    {
        $salonCategory = SalonServiceCategory::create([
            'name' => 'Salon Category '.uniqid(),
            'slug' => 'salon-category-'.uniqid(),
            'description' => 'Fixture category.',
            'is_active' => true,
            'sort_order' => 0,
        ]);

        return ['salon_category' => $salonCategory];
    }

    private function widgetFixture(): array
    {
        $widget = Widget::create([
            'page_identifier' => 'home',
            'region' => 'main',
            'type' => 'html',
            'title' => 'Widget '.uniqid(),
            'settings' => ['content' => 'Fixture widget content.'],
            'sort_order' => 0,
            'is_active' => true,
        ]);

        return ['widget' => $widget];
    }
}
