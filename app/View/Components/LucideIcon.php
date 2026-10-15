<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Log;
use Illuminate\View\Component;
use Illuminate\View\View;

class LucideIcon extends Component
{
    public function __construct(public string $name, public string $strokeWidth = '1.5') {}

    public function render(): View
    {
        $path = resource_path("svg/icons/{$this->name}.svg");

        if (! is_file($path)) {
            Log::warning("Icon [{$this->name}] not found at {$path}");

            return app('view')->make('components.lucide-icon', ['svg' => '']);
        }

        $svg = file_get_contents($path);
        $svg = preg_replace('/stroke-width="[^"]*"/', 'stroke-width="'.$this->strokeWidth.'"', $svg, 1);

        return app('view')->make('components.lucide-icon', ['svg' => $svg]);
    }
}
