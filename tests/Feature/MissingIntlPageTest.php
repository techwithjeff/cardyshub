<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The friendly page shown when PHP's intl extension is missing.
 *
 * This exists because of a real deploy: the storefront worked perfectly and
 * every /admin page returned a bare "Server Error", with the cause buried in a
 * log file. Filament calls Illuminate\Support\Number on any page with a table
 * or pagination, and Number throws without intl.
 */
class MissingIntlPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Production conditions. With debug on, Laravel renders its own
        // diagnostic page and none of this is reachable — the whole point of
        // the handler is what a customer-facing server shows.
        config(['app.debug' => false]);
    }

    protected function throwingRoute(string $message): string
    {
        $path = '/__test_throws_'.md5($message);

        Route::get($path, function () use ($message) {
            throw new \RuntimeException($message);
        });

        return $path;
    }

    public function test_a_missing_intl_extension_explains_itself(): void
    {
        // The exact message Laravel's Number class throws.
        $path = $this->throwingRoute('The "intl" PHP extension is required to use the [format] method.');

        $response = $this->get($path);

        $response->assertStatus(500);
        $response->assertSee('intl', false);
        $response->assertSee('Select PHP Version');
        $response->assertSee('extension=intl', false);
    }

    public function test_the_page_leaks_nothing_about_the_server(): void
    {
        $path = $this->throwingRoute('The "intl" PHP extension is required to use the [format] method.');

        $response = $this->get($path);

        // Reachable by anyone who finds the URL, so it must not describe the
        // database, filesystem or versions.
        foreach (['DB_', 'password', 'mysql', PHP_VERSION, base_path()] as $secret) {
            $response->assertDontSee($secret, false);
        }
    }

    public function test_unrelated_runtime_exceptions_are_left_alone(): void
    {
        // The handler matches on the word "intl". Anything else must fall
        // through to Laravel's normal error handling, not this page.
        $path = $this->throwingRoute('Something else went wrong entirely.');

        $this->get($path)
            ->assertStatus(500)
            ->assertDontSee('Select PHP Version');
    }
}
