<?php

namespace Tests\Feature;

use App\Livewire\Site\InquiryForm;
use App\Models\Inquiry;
use App\Models\Project;
use App\Models\Service;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentSeeder::class);
    }

    public function test_missing_pages_show_the_branded_error_page(): void
    {
        $this->get('/no-such-page')->assertNotFound()->assertSee('পাতাটি খুঁজে পাওয়া যায়নি')->assertSee('হোমপেজে যান');
    }

    public function test_project_page_shows_its_facts_and_own_description(): void
    {
        $project = Project::factory()->create([
            'title' => ['en' => 'Six storey home'],
            'description' => ['en' => '<p>A <strong>six storey</strong> family home.</p>'],
            'status' => 'ongoing',
            'floors' => 6,
            'land_katha' => 5,
        ]);

        $this->withSession(['locale' => 'en'])->get(route('projects.show', $project))
            ->assertOk()
            ->assertSee('Under construction')
            ->assertSee('<strong>six storey</strong>', false)
            ->assertSee('<meta name="description" content="A six storey family home.">', false);
    }

    public function test_faq_page_carries_faq_structured_data(): void
    {
        $this->get('/qa')->assertOk()->assertSee('"@type":"FAQPage"', false);
    }

    public function test_old_long_addresses_redirect_to_the_short_ones(): void
    {
        $this->get('/services')->assertRedirect('/sv')->assertStatus(301);
        $this->get('/services/building-design')->assertRedirect('/sv/building-design');
        $this->get('/contact')->assertRedirect('/ct');
    }

    public function test_every_public_page_loads(): void
    {
        foreach (['/', '/ab', '/sv', '/sv/building-design', '/pt', '/tm', '/qa', '/ct', '/sitemap.xml', '/robots.txt'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_hidden_services_are_not_reachable(): void
    {
        Service::query()->where('slug', 'cost-estimation')->update(['is_active' => false]);

        $this->get('/sv/cost-estimation')->assertNotFound();
    }

    public function test_language_switch_shows_bangla_content_with_english_fallback(): void
    {
        $this->get('/lang/bn')->assertRedirect();

        $this->get('/')
            ->assertSee('lang="bn"', false)
            ->assertSee('বিল্ডিং ডিজাইন')
            ->assertSee('ডিজাইন, অনুমোদন');

        Service::query()->create([
            'slug' => 'english-only',
            'title' => ['en' => 'English Only Service'],
            'summary' => ['en' => 'Only in English'],
            'is_active' => true,
        ]);

        $this->get('/sv')->assertSee('English Only Service');
    }

    public function test_unknown_language_is_rejected(): void
    {
        $this->get('/lang/fr')->assertNotFound();
    }

    public function test_public_pages_send_a_nonce_based_csp(): void
    {
        $csp = $this->get('/')->headers->get('Content-Security-Policy');

        $this->assertMatchesRegularExpression("/script-src 'self' 'nonce-[A-Za-z0-9+\/=]+'/", $csp);
        $this->assertStringNotContainsString("script-src 'self' 'unsafe-inline'", $csp);
    }

    public function test_inquiry_is_saved_with_language(): void
    {
        $this->travelTo(now());

        $component = Livewire::test(InquiryForm::class);
        $this->travel(5)->seconds();

        $component->set('name', 'Rahim Uddin')
            ->set('phone', '01712-345678')
            ->set('service', 'Building Design')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('sent', true);

        $this->assertDatabaseHas('inquiries', ['name' => 'Rahim Uddin', 'phone' => '01712345678', 'locale' => 'en']);
    }

    public function test_service_is_chosen_from_the_list_by_position(): void
    {
        Livewire::test(InquiryForm::class)
            ->call('chooseService', 1)
            ->assertSet('service', 'Approval Sheet')
            ->call('chooseService', 999)
            ->assertSet('service', '');
    }

    public function test_invalid_phone_is_rejected(): void
    {
        $component = Livewire::test(InquiryForm::class);
        $this->travel(5)->seconds();

        $component->set('name', 'Rahim')->set('phone', '12345')->call('submit')->assertHasErrors('phone');
        $this->assertSame(0, Inquiry::query()->count());
    }

    public function test_bots_are_silently_ignored(): void
    {
        // Honeypot filled.
        $component = Livewire::test(InquiryForm::class);
        $this->travel(5)->seconds();
        $component->set('name', 'Bot')->set('phone', '01712345678')->set('website', 'http://spam.example')
            ->call('submit')->assertSet('sent', true);

        // Submitted instantly.
        Livewire::test(InquiryForm::class)->set('name', 'Bot')->set('phone', '01712345678')
            ->call('submit')->assertSet('sent', true);

        $this->assertSame(0, Inquiry::query()->count());
    }

    public function test_inquiries_are_rate_limited_per_ip(): void
    {
        RateLimiter::clear('inquiry:127.0.0.1');

        foreach (range(1, 6) as $i) {
            $component = Livewire::test(InquiryForm::class);
            $this->travel(5)->seconds();
            $component->set('name', 'Person')->set('phone', '01712345678')->call('submit');
        }

        $this->assertSame(5, Inquiry::query()->count());
    }
}
