<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotFoundPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_generic_404_renders_branded_page(): void
    {
        $r = $this->get('/this-route-does-not-exist');
        $r->assertStatus(404);
        $r->assertSee('This page has gone missing');
        $r->assertSee('Error 404');
        $r->assertSee('Shop all products');
        $r->assertDontSee('Coming soon');
    }

    public function test_previously_planned_pages_now_resolve(): void
    {
        // These used to render a "coming soon" 404. They are real pages now, so
        // the 404 page must no longer claim otherwise.
        foreach (['/delivery', '/returns', '/faqs', '/terms', '/privacy', '/cookies', '/wholesale'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_404_still_has_site_chrome(): void
    {
        \App\Models\Setting::set('whatsapp_number', '+447448255453');
        \Illuminate\Support\Facades\Cache::forget('settings');

        $r = $this->get('/nope');
        $r->assertSee('Cardyshub');
        $r->assertSee('All rights reserved', false);   // footer present
        $r->assertSee('wa.me/447448255453', false);   // chat widget present
    }

    public function test_missing_product_and_brand_use_the_page(): void
    {
        $this->get('/shop/no-such-product')->assertStatus(404)->assertSee('This page has gone missing');
        $this->get('/brands/no-such-brand')->assertStatus(404)->assertSee('This page has gone missing');
    }
}
