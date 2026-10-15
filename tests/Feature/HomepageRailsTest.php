<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The two product rails on the homepage.
 *
 * These exist because of a bug that only showed up once the site was on a
 * second machine. The catalogue is seeded in one pass, so most products share a
 * created_at to the second, and `latest()->take(4)` gave MySQL free rein over
 * which of the tied rows came back. Locally it picked photographed products; on
 * the server it picked photoless ones, and the homepage led with four grey
 * placeholder cards. Same code, same data, different shop window.
 */
class HomepageRailsTest extends TestCase
{
    use RefreshDatabase;

    protected function product(string $name, bool $featured, bool $withImage, ?string $createdAt = null): Product
    {
        $product = Product::create([
            'category_id' => Category::firstOrCreate(['slug' => 'serums'], ['name' => 'Serums'])->id,
            'name' => $name,
            'slug' => str($name)->slug()->value(),
            'price_pence' => 1999,
            'stock_quantity' => 10,
            'status' => 'published',
            'is_featured' => $featured,
        ]);

        // Every product in a given test shares a timestamp on purpose: that tie
        // is the whole point of these tests.
        $product->forceFill(['created_at' => $createdAt ?? '2026-01-01 00:00:00'])->save();

        if ($withImage) {
            ProductImage::create([
                'product_id' => $product->id,
                'path' => 'products/'.$product->slug.'.webp',
                'sort_order' => 0,
            ]);
        }

        return $product;
    }

    public function test_homepage_rails_never_show_a_product_without_a_photo(): void
    {
        // Photoless products are created last, so a naive "newest first" would
        // put them at the front of the rail.
        $this->product('Photographed Serum A', featured: true, withImage: true);
        $this->product('Photographed Serum B', featured: false, withImage: true);
        $this->product('Photoless Serum C', featured: true, withImage: false);
        $this->product('Photoless Serum D', featured: false, withImage: false);

        $response = $this->get(route('home'));
        $response->assertOk();

        $featured = $response->viewData('featured');
        $newIn = $response->viewData('newIn');

        foreach ($featured->concat($newIn) as $product) {
            $this->assertTrue(
                $product->images->isNotEmpty(),
                "[{$product->name}] reached a homepage rail with no image."
            );
        }
    }

    public function test_rails_are_deterministic_when_products_share_a_created_at(): void
    {
        foreach (range(1, 12) as $i) {
            $this->product("Tied Serum {$i}", featured: $i <= 6, withImage: true);
        }

        $first = $this->get(route('home'));
        $second = $this->get(route('home'));

        $this->assertSame(
            $first->viewData('featured')->modelKeys(),
            $second->viewData('featured')->modelKeys(),
            'Featured rail changed between identical requests.'
        );

        $this->assertSame(
            $first->viewData('newIn')->modelKeys(),
            $second->viewData('newIn')->modelKeys(),
            'New in rail changed between identical requests.'
        );

        // Newest-first must still hold, with id breaking the tie.
        $featuredKeys = $first->viewData('featured')->modelKeys();
        $this->assertSame($featuredKeys, collect($featuredKeys)->sortDesc()->values()->all());
    }

    public function test_a_product_is_not_shown_in_both_rails_at_once(): void
    {
        foreach (range(1, 10) as $i) {
            $this->product("Serum {$i}", featured: $i <= 5, withImage: true);
        }

        $response = $this->get(route('home'));

        $featured = $response->viewData('featured')->modelKeys();
        $newIn = $response->viewData('newIn')->modelKeys();

        $this->assertEmpty(
            array_intersect($featured, $newIn),
            'The same product appeared in both homepage rails.'
        );
    }

    public function test_rails_still_fill_when_only_photoless_products_are_featured(): void
    {
        // Guards the filter against emptying a rail: with nothing featured and
        // photographed, the featured rail is simply empty rather than falling
        // back to placeholder cards, and New in still fills.
        $this->product('Photoless Featured', featured: true, withImage: false);
        foreach (range(1, 4) as $i) {
            $this->product("Plain Serum {$i}", featured: false, withImage: true);
        }

        $response = $this->get(route('home'));
        $response->assertOk();

        $this->assertCount(0, $response->viewData('featured'));
        $this->assertCount(4, $response->viewData('newIn'));
    }
}
