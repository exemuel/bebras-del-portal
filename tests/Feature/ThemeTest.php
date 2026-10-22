<?php

namespace Tests\Feature;

use App\Models\Certificate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeTest extends TestCase
{
    use RefreshDatabase;

    private function css(string $name): string
    {
        return file_get_contents(public_path("css/{$name}.css"));
    }

    public function test_public_layout_loads_shared_theme_before_site_styles(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('css/theme.css', $html);
        $this->assertStringContainsString('family=Fredoka', $html);
        $this->assertLessThan(strpos($html, 'css/style.css'), strpos($html, 'css/theme.css'));
    }

    public function test_admin_panel_loads_the_same_shared_theme(): void
    {
        $html = $this->get('/admin/login')->assertOk()->getContent();

        $this->assertStringContainsString('css/theme.css', $html);
        $this->assertStringContainsString('css/admin.css', $html);
        $this->assertStringContainsString('family=Fredoka', $html);
    }

    public function test_palette_is_defined_only_in_theme_css(): void
    {
        preg_match_all('/#[0-9a-fA-F]{6}\b/', $this->css('theme'), $matches);
        // White is a neutral, not part of the palette.
        $palette = array_diff(array_unique(array_map('strtolower', $matches[0])), ['#ffffff']);
        $this->assertNotEmpty($palette);

        foreach (['style', 'admin'] as $file) {
            $css = strtolower($this->css($file));
            foreach ($palette as $hex) {
                $this->assertStringNotContainsString($hex, $css, "{$hex} is duplicated in {$file}.css");
            }
        }

        $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,8}\b/', $this->css('admin'), 'admin.css must use theme variables only');
    }

    public function test_every_category_has_one_color_in_theme_public_css_and_admin(): void
    {
        $theme = $this->css('theme');
        $admin = $this->css('admin');
        $site = $this->css('style');

        foreach (array_keys(Certificate::CATEGORIES) as $category) {
            $key = Certificate::categoryKey($category);

            $this->assertSame(strtolower($category), $key);
            $this->assertStringContainsString("--cat-{$key}:", $theme);
            $this->assertStringContainsString("--cat-{$key}-ink:", $theme);
            $this->assertStringContainsString(".cat-{$key} ", $site);
            $this->assertStringContainsString("--{$key}-500:", $admin);
        }

        $this->assertSame('gray', Certificate::categoryKey('Unknown'));
    }

    public function test_certificate_results_use_category_colors(): void
    {
        Certificate::create([
            'bebras_username' => 'tema_user',
            'participant_name' => 'Tema User',
            'category' => 'Siaga',
            'award_type' => 'Participant',
            'year' => 2024,
            'file_path' => 'certificates/x.pdf',
        ]);

        $this->get('/sertifikat?username=tema_user')->assertSee('cat-badge cat-siaga', false);
    }

    public function test_home_category_cards_use_one_color_each(): void
    {
        $html = $this->get('/')->getContent();

        foreach (['sikecil', 'siaga', 'penggalang', 'penegak'] as $key) {
            $this->assertStringContainsString("cat-card cat-{$key}", $html);
        }
    }

    public function test_motion_and_focus_rules_are_present(): void
    {
        $site = $this->css('style');

        $this->assertStringContainsString('prefers-reduced-motion: reduce', $site);
        $this->assertStringContainsString(':focus-visible', $site);
        $this->assertStringContainsString('prefers-reduced-motion: reduce', $this->css('admin'));
    }
}
