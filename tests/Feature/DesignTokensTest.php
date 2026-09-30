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
}
