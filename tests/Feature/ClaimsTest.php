<?php
namespace Tests\Feature;

use App\Models\Product;
use App\Support\Delivery;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClaimsTest extends TestCase
{
    use RefreshDatabase;

    /** Pages that must never assert the product range is cruelty free or vegan. */
    protected function pages(): array
    {
        $this->seed([CategorySeeder::class, BrandSeeder::class, ProductSeeder::class]);
        $product = Product::has('images')->first();

        return ['/', '/about', '/shop', '/cart', '/delivery', '/returns', '/terms',
                '/privacy', '/cookies', '/wholesale', '/shop/'.$product->slug];
    }

    public function test_no_page_claims_cruelty_free_or_vegan(): void
    {
        foreach ($this->pages() as $path) {
            $html = $this->get($path)->getContent();

            $this->assertStringNotContainsString('Cruelty free', $html, "cruelty-free claim on {$path}");
            $this->assertStringNotContainsString('cruelty free,', $html, "cruelty-free claim on {$path}");
            $this->assertStringNotContainsString('tested on animals', $html, "animal testing claim on {$path}");
        }
    }

    public function test_faq_pages_explain_the_position_without_claiming_it(): void
    {
        foreach (['/faqs', '/contact'] as $path) {
            $this->get($path)
                ->assertSee('regulated cosmetics claims')
                ->assertDontSee('Yes. Nothing we sell has been tested on animals');
        }
    }

    public function test_product_page_uses_the_configured_delivery_threshold(): void
    {
        $this->seed([CategorySeeder::class, BrandSeeder::class, ProductSeeder::class]);
        $product = Product::has('images')->first();

        // The badge row now leads with the next-day promise, so the threshold
        // is quoted in the Delivery & returns accordion instead. What matters
        // is unchanged: the figure is the configured one, never a stale £40.
        $this->get('/shop/'.$product->slug)
            ->assertSee('free on orders over '.Delivery::freeThreshold(), false)
            ->assertSee(Delivery::standardMethod()->price(), false)
            ->assertDontSee('£40');
    }

    public function test_no_page_carries_a_stale_delivery_figure(): void
    {
        // The product page kept an old £40 in an accordion that page-specific
        // checks had missed, so this sweeps every public page instead.
        foreach ($this->pages() as $path) {
            $html = $this->get($path)->getContent();

            $this->assertStringNotContainsString('£40', $html, "stale £40 threshold on {$path}");
            $this->assertStringNotContainsString('£3.50', $html, "stale £3.50 rate on {$path}");
            $this->assertStringNotContainsString('£35', $html, "stale £35 threshold on {$path}");
        }
    }
}