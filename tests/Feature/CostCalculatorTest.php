<?php

namespace Tests\Feature;

use App\Livewire\Site\Calculator;
use App\Models\Setting;
use App\Support\CostCalculator;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CostCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ContentSeeder::class);
    }

    private function enable(): void
    {
        Setting::write(['calc_enabled' => 'yes', 'calc_rate_standard' => '2500', 'calc_rate_premium' => '3200', 'calc_coverage' => '60', 'calc_range' => '10']);
    }

    public function test_calculator_stays_hidden_until_enabled_with_a_rate(): void
    {
        $this->get('/cc')->assertNotFound();
        $this->get('/')->assertDontSee(route('calculator').'"', false);

        // Turned on but no rate entered: still hidden, so the site never shows a made-up price.
        Setting::write(['calc_enabled' => 'yes']);
        $this->get('/cc')->assertNotFound();

        $this->enable();
        $this->get('/cc')->assertOk();
        $this->get('/')->assertSee(route('calculator').'"', false);
    }

    public function test_estimate_uses_bangladeshi_land_units_and_admin_rates(): void
    {
        $this->enable();

        // 3 katha = 2160 sq ft; 60% coverage = 1296 sq ft per floor; 5 floors = 6480 sq ft; × ৳2500.
        $result = CostCalculator::estimate(3, 'katha', 5, 'standard');

        $this->assertSame(2160, $result['land_sqft']);
        $this->assertSame(1296, $result['floor_sqft']);
        $this->assertSame(6480, $result['total_sqft']);
        $this->assertSame(16_200_000, $result['estimate']);
        $this->assertSame(14_580_000, $result['low']);
        $this->assertSame(17_820_000, $result['high']);

        $this->assertSame(871, CostCalculator::estimate(2, 'shotok', 1, 'standard')['land_sqft']);
        $this->assertNull(CostCalculator::estimate(3, 'katha', 5, 'luxury'), 'No luxury rate set');
    }

    public function test_taka_is_written_in_lakh_and_crore(): void
    {
        $this->assertSame('৳ 1.46 crore', CostCalculator::taka(14_580_000, 'en'));
        $this->assertSame('৳ ১.৪৬ কোটি', CostCalculator::taka(14_580_000, 'bn'));
        $this->assertSame('৳ 45.6 lakh', CostCalculator::taka(4_560_000, 'en'));
        $this->assertSame('৳ 2,500', CostCalculator::taka(2500, 'en'));
        $this->assertSame('12,34,567', CostCalculator::groupIndian(1_234_567));
    }

    public function test_calculator_component_updates_and_rejects_bad_input(): void
    {
        $this->enable();

        Livewire::test(Calculator::class)
            ->set('landSize', '3')->set('unit', 'katha')
            ->call('changeFloors', 10)
            ->assertSet('floors', 15)
            ->call('changeFloors', 50)
            ->assertSet('floors', 20)
            ->call('setLevel', 'luxury')
            ->assertSet('level', 'standard')
            ->call('setLevel', 'premium')
            ->assertSet('level', 'premium')
            ->set('unit', 'acre')
            ->assertSee('Enter a land size');
    }

    public function test_result_link_prefills_the_contact_form(): void
    {
        $this->get('/ct?estimate='.rawurlencode('Calculator: 3 katha'))->assertSee('Calculator: 3 katha');
        $this->get('/contact?estimate=x')->assertRedirect('/ct?estimate=x');
    }
}
