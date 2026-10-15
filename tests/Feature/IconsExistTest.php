<?php
namespace Tests\Feature;

use Tests\TestCase;

class IconsExistTest extends TestCase
{
    /**
     * LucideIcon renders an empty string and only logs a warning when an icon
     * is missing, so a typo or an un-vendored name disappears silently in the
     * UI. Only 36 of Lucide's icons are vendored into resources/svg/icons, so
     * "it exists in node_modules" is not enough.
     */
    public function test_every_icon_referenced_in_a_view_is_vendored(): void
    {
        $views = collect(\Illuminate\Support\Facades\File::allFiles(resource_path('views')))
            ->filter(fn ($f) => str_ends_with($f->getFilename(), '.blade.php'));

        // Names handled by x-social-icon rather than by a vendored file.
        preg_match_all(
            "/^\s*'([a-z0-9-]+)'\s*=>\s*'M/m",
            file_get_contents(resource_path('views/components/social-icon.blade.php')),
            $sm
        );
        $socialNames = $sm[1];
        $this->assertNotEmpty($socialNames, 'could not read the social icon list');

        $missing = [];

        foreach ($views as $view) {
            $src = file_get_contents($view->getPathname());
            $names = [];

            // <x-lucide-icon name="foo" />
            preg_match_all('/<x-lucide-icon\s+name="([a-z0-9-]+)"/', $src, $m);
            $names = array_merge($names, $m[1]);

            // 'icon' => 'foo'  (array-driven lists)
            preg_match_all("/'icon'\s*=>\s*'([a-z0-9-]+)'/", $src, $m2);
            $names = array_merge($names, $m2[1]);

            foreach (array_unique($names) as $name) {
                // Social marks are inline SVG paths in x-social-icon, not files.
                if (in_array($name, $socialNames, true)) {
                    continue;
                }

                if (! is_file(resource_path("svg/icons/{$name}.svg"))) {
                    $missing[] = $name.'  ('.$view->getRelativePathname().')';
                }
            }
        }

        $this->assertSame([], array_values(array_unique($missing)),
            "Icons referenced but not vendored into resources/svg/icons:\n  ".implode("\n  ", array_unique($missing)));
    }
}
