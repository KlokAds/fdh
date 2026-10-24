<?php

namespace App\Livewire\Site;

use App\Support\CostCalculator;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.site')]
class Calculator extends Component
{
    public string $landSize = '3';

    public string $unit = 'katha';

    public int $floors = 5;

    public string $level = 'standard';

    public function mount(): void
    {
        abort_unless(CostCalculator::isAvailable(), 404);

        $this->level = array_key_first(CostCalculator::rates());
    }

    public function setLevel(string $level): void
    {
        if (array_key_exists($level, CostCalculator::rates())) {
            $this->level = $level;
        }
    }

    public function changeFloors(int $delta): void
    {
        $this->floors = max(1, min(20, $this->floors + $delta));
    }

    public function updatedFloors(): void
    {
        $this->floors = max(1, min(20, $this->floors));
    }

    /**
     * Summary passed to the contact form so the engineer sees what the visitor calculated.
     */
    private function summary(?array $result): string
    {
        if ($result === null) {
            return '';
        }

        return __('Calculator: :land :unit land, :floors floors, :level finishing — about :low to :high.', [
            'land' => app()->getLocale() === 'bn' ? CostCalculator::bnDigits($this->landSize) : $this->landSize,
            'unit' => __(ucfirst($this->unit)),
            'floors' => CostCalculator::number($this->floors),
            'level' => __(ucfirst($this->level)),
            'low' => CostCalculator::taka($result['low']),
            'high' => CostCalculator::taka($result['high']),
        ]);
    }

    public function render(): View
    {
        $size = is_numeric($this->landSize) ? (float) $this->landSize : 0.0;
        $unitAllowed = array_key_exists($this->unit, CostCalculator::LAND_UNITS);
        $size = $size > 0 && $size * (CostCalculator::LAND_UNITS[$this->unit] ?? 1) <= 500_000 ? $size : 0.0;

        $result = $unitAllowed ? CostCalculator::estimate($size, $this->unit, $this->floors, $this->level) : null;

        return view('livewire.site.calculator', [
            'rates' => CostCalculator::rates(),
            'result' => $result,
            'summary' => $this->summary($result),
        ])->title(__('Construction cost calculator').' — '.site('site_name'));
    }
}
