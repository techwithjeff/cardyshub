<?php

namespace App\Support;

/**
 * Palette + ornamentation for each hero slide theme.
 *
 * Gold and pink keep their gradients in app.css (`hero-bg-gold` / `hero-bg-pink`).
 * Sage was added after the Node toolchain became unavailable, so its gradient is
 * inlined here instead — a new theme can be introduced without a Tailwind rebuild.
 * Ornament colours are literal values for the same reason: `bg-citrus` and friends
 * only exist in the compiled stylesheet if Tailwind has seen them.
 */
class HeroTheme
{
    public static function all(): array
    {
        return [
            'gold' => [
                'class' => 'hero-bg-gold',
                'gradient' => null,
                'bokeh' => [
                    ['color' => '#FFD84D', 'size' => 180, 'top' => '4%', 'left' => '2%', 'delay' => 0],
                    ['color' => '#FFCCE0', 'size' => 90, 'top' => '68%', 'left' => '10%', 'delay' => 1.2],
                    ['color' => '#FFD84D', 'size' => 120, 'top' => '78%', 'left' => '34%', 'delay' => 2.4],
                    ['color' => '#FFFFFF', 'size' => 60, 'top' => '14%', 'left' => '40%', 'delay' => 0.6],
                    ['color' => '#FA0467', 'size' => 50, 'top' => '40%', 'left' => '2%', 'delay' => 1.8, 'opacity' => 0.25],
                ],
                'petals' => [],
            ],

            'pink' => [
                'class' => 'hero-bg-pink',
                'gradient' => null,
                'bokeh' => [
                    ['color' => '#FFFFFF', 'size' => 160, 'top' => '6%', 'left' => '3%', 'delay' => 0.3],
                    ['color' => '#FFCCE0', 'size' => 100, 'top' => '64%', 'left' => '4%', 'delay' => 1.6],
                    ['color' => '#FFFFFF', 'size' => 70, 'top' => '80%', 'left' => '30%', 'delay' => 0.9],
                ],
                'petals' => [
                    ['color' => 'rgba(250,4,103,0.40)', 'w' => 26, 'h' => 40, 'top' => '16%', 'left' => '40%', 'delay' => 0.4],
                    ['color' => 'rgba(250,4,103,0.30)', 'w' => 18, 'h' => 30, 'top' => '72%', 'left' => '36%', 'delay' => 1.8],
                    ['color' => 'rgba(250,4,103,0.25)', 'w' => 16, 'h' => 26, 'top' => '30%', 'left' => '8%', 'delay' => 1.1],
                ],
            ],

            // Matches hero2's sage-and-marble body care shot.
            'sage' => [
                'class' => '',
                'gradient' => 'radial-gradient(circle at 15% 18%, rgba(203,224,201,0.75) 0, rgba(203,224,201,0) 48%), '
                    . 'radial-gradient(circle at 88% 12%, rgba(126,178,131,0.18) 0, rgba(126,178,131,0) 42%), '
                    . 'radial-gradient(circle at 82% 82%, rgba(226,238,224,0.85) 0, rgba(226,238,224,0) 50%), '
                    . 'radial-gradient(circle at 8% 85%, rgba(250,4,103,0.08) 0, rgba(250,4,103,0) 40%), '
                    . 'linear-gradient(135deg, #EEF5EC 0%, #DCEBDA 45%, #F5F9F3 100%)',
                'bokeh' => [
                    ['color' => '#FFFFFF', 'size' => 160, 'top' => '6%', 'left' => '3%', 'delay' => 0.3],
                    ['color' => '#C7DFC5', 'size' => 100, 'top' => '64%', 'left' => '4%', 'delay' => 1.6],
                    ['color' => '#FFFFFF', 'size' => 70, 'top' => '80%', 'left' => '30%', 'delay' => 0.9],
                    ['color' => '#FA0467', 'size' => 50, 'top' => '38%', 'left' => '2%', 'delay' => 2.1, 'opacity' => 0.18],
                ],
                'petals' => [
                    ['color' => 'rgba(110,163,116,0.38)', 'w' => 26, 'h' => 40, 'top' => '16%', 'left' => '40%', 'delay' => 0.4],
                    ['color' => 'rgba(110,163,116,0.28)', 'w' => 18, 'h' => 30, 'top' => '72%', 'left' => '36%', 'delay' => 1.8],
                    ['color' => 'rgba(110,163,116,0.22)', 'w' => 16, 'h' => 26, 'top' => '30%', 'left' => '8%', 'delay' => 1.1],
                ],
            ],
        ];
    }
}
