<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The admin sidebar's home link (<a href="{{ route('home') }}">...) wrapped
 * the logo, brand text, AND the #sidebar-collapse-toggle <button> but was
 * never closed with </a> -- the markup jumped straight from the button to
 * two stray </div>s. That unbalanced tag doesn't just mis-nest one element:
 * it throws off every open tag that follows it for the rest of the page
 * (the sidebar <nav>, and even <main>), so browsers' error-recovery parsing
 * left <nav> and <main> as top-level children of <body> instead of nested
 * inside the intended flex layout. Combined with the sidebar's mobile
 * off-canvas classes lacking `position: fixed`, this pushed all page
 * content ~750px+ below the fold on any viewport under the "lg" (1024px)
 * breakpoint -- every single admin page rendered as an entirely blank
 * screen on tablet-width or narrower windows. Reproduced live (screenshot
 * + DOM inspection) before fixing both the missing `fixed` positioning and
 * this tag-nesting bug in resources/views/admin/layout.blade.php.
 *
 * This test guards specifically against the tag-nesting regression: nested
 * interactive elements (a <button> inside an <a>) are invalid HTML and
 * exactly the kind of thing that's easy to silently reintroduce during a
 * future edit to the sidebar header markup.
 */
class AdminLayoutMarkupTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_sidebar_home_link_does_not_nest_the_collapse_toggle_button(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();

        $html = $response->getContent();

        preg_match('/<a\s[^>]*aria-label="[^"]*Go to homepage"[^>]*>(.*?)<\/a>/s', $html, $matches);

        $this->assertNotEmpty($matches, 'Could not locate the sidebar home link in the rendered admin page.');
        $this->assertStringNotContainsString(
            'sidebar-collapse-toggle',
            $matches[1],
            'The sidebar collapse-toggle button is nested inside the home <a> link -- the </a> is missing again.'
        );
    }

    public function test_admin_sidebar_is_positioned_fixed_for_off_canvas_mobile_display(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertOk();

        $html = $response->getContent();

        preg_match('/<aside id="sidebar" class="([^"]*)"/', $html, $matches);

        $this->assertNotEmpty($matches, 'Could not locate the #sidebar aside element.');
        $this->assertStringContainsString(
            'fixed',
            $matches[1],
            'The sidebar must be position:fixed below the "lg" breakpoint so its off-canvas -translate-x-full state removes it from document flow -- otherwise it still reserves its full height and pushes all page content off-screen on tablet/mobile widths.'
        );
    }
}
