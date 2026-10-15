<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_stores_order_number(): void
    {
        $r = $this->post('/contact', [
            'name' => 'Test Person',
            'email' => 'test@example.com',
            'subject' => 'An order I have placed',
            'order_number' => 'CH1042',
            'message' => 'Where is my parcel?',
        ]);
        $r->assertSessionHas('success');
        $this->assertDatabaseHas('contact_submissions', ['order_number' => 'CH1042', 'subject' => 'An order I have placed']);
    }

    public function test_subject_is_now_required(): void
    {
        $this->post('/contact', ['name' => 'A', 'email' => 'a@b.com', 'message' => 'hi'])
             ->assertSessionHasErrors('subject');
    }

    public function test_hero_copy_matches_deck(): void
    {
        $html = $this->get('/')->getContent();
        foreach (['Skincare that actually fits your skin', 'Stop starting over', 'Four steps. No guesswork.',
                  'Find your routine', 'Build my routine'] as $needle) {
            $this->assertStringContainsString($needle, $html, "missing: $needle");
        }

        $this->assertStringNotContainsString('Loved by 1,000+ customers', $html);

        // Both hero support lines were removed at the client's request.
        $this->assertStringNotContainsString('Cleanse · Treat · Hydrate · Protect', $html);
        $this->assertStringNotContainsString('30 day returns · Secure checkout', $html);
    }

    public function test_about_page_sections(): void
    {
        $html = $this->get('/about')->getContent();
        foreach (['Built by someone who needed it first', 'Skincare, without the guesswork', 'What we believe',
                  'Confidence is the actual point', 'Meet the founder', 'Ready when you are'] as $needle) {
            $this->assertStringContainsString($needle, $html, "missing: $needle");
        }
    }
}
