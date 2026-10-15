<?php
namespace Tests\Feature;
use App\Models\Setting;
use App\Models\WholesaleEnquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyPagesTest extends TestCase
{
    use RefreshDatabase;

    public static function pages(): array
    {
        return [
            ['/delivery', 'Delivery'],
            ['/returns', 'Returns and refunds'],
            ['/faqs', 'Frequently asked questions'],
            ['/terms', 'Terms and conditions'],
            ['/privacy', 'Privacy policy'],
            ['/cookies', 'Cookie policy'],
            ['/wholesale', 'Stock Cardyshub'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pages')]
    public function test_page_renders(string $path, string $heading): void
    {
        $this->get($path)->assertOk()->assertSee($heading);
    }

    public function test_footer_links_all_resolve(): void
    {
        foreach (['/delivery', '/returns', '/faqs', '/terms', '/privacy', '/cookies', '/wholesale'] as $p) {
            $this->get($p)->assertOk();
        }
    }

    public function test_terms_hides_entity_clause_until_type_is_set(): void
    {
        $this->get('/terms')->assertSee('Set')->assertDontSee('a company registered in');

        Setting::set('legal_entity_type', 'ltd');
        Setting::set('legal_company_name', 'Cardyshub Ltd');
        $this->get('/terms')->assertSee('a company registered in');

        Setting::set('legal_entity_type', 'sole_trader');
        $this->get('/terms')->assertSee('trading as Cardyshub, a business established in England');
    }

    public function test_draft_banner_shows_while_values_outstanding(): void
    {
        $this->get('/privacy')->assertSee('This page is not ready to publish');
    }

    public function test_cookie_policy_matches_reality(): void
    {
        $r = $this->get('/cookies');
        $r->assertSee(config('session.cookie'));
        $r->assertSee('We do not currently use analytics or advertising cookies');
        // must not claim cookies the site never sets
        $r->assertDontSee('_ga');
        $r->assertDontSee('_fbp');
        $r->assertDontSee('Google Analytics');
    }

    public function test_wholesale_enquiry_stores_and_requires_consent(): void
    {
        $payload = [
            'name' => 'Jo Buyer', 'business_name' => 'Salon Nine', 'business_type' => 'Salon',
            'email' => 'jo@salonnine.co.uk', 'message' => 'We would like to stock the range.',
        ];

        $this->post('/wholesale', $payload)->assertSessionHasErrors('consent');
        $this->assertDatabaseCount('wholesale_enquiries', 0);

        $this->post('/wholesale', $payload + ['consent' => '1'])->assertSessionHas('success');
        $this->assertDatabaseHas('wholesale_enquiries', ['email' => 'jo@salonnine.co.uk', 'business_type' => 'Salon']);
    }

    public function test_wholesale_rejects_unknown_business_type(): void
    {
        $this->post('/wholesale', [
            'name' => 'X', 'business_name' => 'Y', 'business_type' => 'Nightclub',
            'email' => 'a@b.com', 'message' => 'hi', 'consent' => '1',
        ])->assertSessionHasErrors('business_type');
    }

    public function test_404_no_longer_claims_pages_are_coming_soon(): void
    {
        $this->get('/made-up')->assertStatus(404)->assertDontSee('Coming soon');
    }
}
