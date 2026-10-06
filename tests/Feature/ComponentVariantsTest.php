<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class ComponentVariantsTest extends TestCase
{
    public function test_badge_default_keeps_legacy_purple(): void
    {
        $html = Blade::render('<x-badge>Berhasil</x-badge>');

        $this->assertStringContainsString('class="badge"', $html);
        $this->assertStringNotContainsString('background:', $html);
    }

    public function test_badge_tones_use_companion_tokens(): void
    {
        $this->assertStringContainsString('var(--success-bg)', Blade::render('<x-badge tone="success">x</x-badge>'));
        $this->assertStringContainsString('var(--warn-bg)', Blade::render('<x-badge tone="warn">x</x-badge>'));
        $this->assertStringContainsString('var(--accent-soft)', Blade::render('<x-badge tone="info">x</x-badge>'));
        $this->assertStringContainsString('var(--danger-bg)', Blade::render('<x-badge tone="danger">x</x-badge>'));
    }

    public function test_btn_variants_render_expected_classes(): void
    {
        $this->assertStringContainsString('class="btn primary"', Blade::render('<x-btn variant="primary" href="/x">x</x-btn>'));
        $this->assertStringContainsString('class="btn ghost"', Blade::render('<x-btn variant="ghost" href="/x">x</x-btn>'));
        $this->assertStringContainsString('class="btn ghost-danger"', Blade::render('<x-btn variant="ghost-danger" type="submit">x</x-btn>'));
        $this->assertStringContainsString('class="btn"', Blade::render('<x-btn href="/x">x</x-btn>'));
    }

    public function test_stat_renders_label_and_value(): void
    {
        $html = Blade::render('<x-stat label="Sudah dibayar">Rp 0</x-stat>');

        $this->assertStringContainsString('<small>Sudah dibayar</small>', $html);
        $this->assertStringContainsString('<b>Rp 0</b>', $html);
    }
}
