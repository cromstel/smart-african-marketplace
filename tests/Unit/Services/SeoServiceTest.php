<?php

namespace Tests\Unit\Services;

use App\Services\SeoService;
use Tests\TestCase;

/**
 * Covers Roadmap 0.3: App\Services\SeoService previously read
 * env('SEO_DESCRIPTION') / env('SEO_KEYWORDS') / env('*_URL') directly.
 * env() returns null outside config files once `php artisan config:cache`
 * runs -- a step the deployment runbook mandates -- which silently emptied
 * the page description, keywords and social profile links in production.
 *
 * The fix moved the defaults into config/seo.php and made SeoService read
 * through config('seo.*') instead. A cached config file is a serialized,
 * fully-resolved config array, so setting config() directly here (rather
 * than the .env file) is exactly what SeoService sees after config:cache --
 * this proves the getters are wired to config(), not env(), without the
 * cost and side effects of shelling out to the artisan command itself.
 */
class SeoServiceTest extends TestCase
{
    public function test_description_falls_back_to_config_not_env(): void
    {
        config(['seo.description' => 'Config-cache-safe description.']);

        $seo = new SeoService;

        $this->assertSame('Config-cache-safe description.', $seo->getDescription());
    }

    public function test_keywords_fall_back_to_config_not_env(): void
    {
        config(['seo.keywords' => 'african market, config cache safe']);

        $seo = new SeoService;

        $this->assertSame('african market, config cache safe', $seo->getKeywords());
    }

    public function test_page_specific_description_still_overrides_the_config_default(): void
    {
        config(['seo.description' => 'Fallback description.']);

        $seo = (new SeoService)->setDescription('Page-specific description.');

        $this->assertSame('Page-specific description.', $seo->getDescription());
    }

    public function test_organization_schema_social_links_are_read_through_config_not_env(): void
    {
        config(['seo.social' => [
            'facebook' => 'https://facebook.com/example',
            'twitter' => 'https://twitter.com/example',
            'instagram' => null,
            'youtube' => null,
        ]]);

        $schema = (new SeoService)->generateOrganizationSchema();

        $this->assertSame(
            ['https://facebook.com/example', 'https://twitter.com/example'],
            $schema['sameAs']
        );
    }

    public function test_unset_social_links_are_omitted_rather_than_emitted_as_empty(): void
    {
        config(['seo.social' => [
            'facebook' => null,
            'twitter' => null,
            'instagram' => null,
            'youtube' => null,
        ]]);

        $schema = (new SeoService)->generateOrganizationSchema();

        $this->assertSame([], $schema['sameAs']);
    }
}
