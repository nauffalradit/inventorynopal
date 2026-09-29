<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportSyncTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin Laporan',
            'email' => 'admin_laporan@nopal.local',
            'google_id' => 'google-laporan-'.uniqid(),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_report_completes_without_worker(): void
    {
        Storage::fake(); // disk default (local)
        config(['queue.default' => 'database']); // dispatch() biasa butuh worker → bukti jalur sinkron
        $admin = $this->admin();

        $this->actingAs($admin)
            ->post(route('reports.store'))
            ->assertRedirect(route('reports.index'));

        $report = Report::latest()->firstOrFail();
        $this->assertEquals('completed', $report->status);
        $this->assertNotNull($report->file_path);
        Storage::assertExists($report->file_path);
        $this->assertEquals(0, DB::table('jobs')->count()); // tidak ada sisa antrean
    }
}
