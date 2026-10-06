<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesignTokensTest extends TestCase
{
    use RefreshDatabase;

    public function test_layout_links_tokens_stylesheet(): void
    {
        $admin = User::create([
            'name' => 'Admin Token',
            'email' => 'admin_token@nopal.local',
            'google_id' => 'google-token-'.uniqid(),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('css/tokens.css', false);
    }

    public function test_tokens_file_defines_core_variables(): void
    {
        $css = (string) file_get_contents(public_path('css/tokens.css'));

        foreach (['--surface', '--ink', '--muted', '--accent', '--danger', '--text-lg', '--space-4', '--radius-md', '--section-gap'] as $token) {
            $this->assertStringContainsString($token, $css);
        }
    }

    public function test_tokens_file_defines_u3_layout_variables(): void
    {
        $css = (string) file_get_contents(public_path('css/tokens.css'));

        foreach (['--sidebar-ink', '--nav-link', '--avatar-bg', '--table-head-bg', '--label-ink', '--btn-ink', '--badge-bg', '--success-bg', '--danger-bg', '--metric-tint-2'] as $token) {
            $this->assertStringContainsString($token, $css);
        }
    }

    public function test_layout_has_no_hardcoded_hex_colors(): void
    {
        $blade = (string) file_get_contents(resource_path('views/layouts/app.blade.php'));

        $this->assertDoesNotMatchRegularExpression('/#[0-9a-fA-F]{3,6}\b/', $blade);
    }
}
