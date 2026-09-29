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

    public function test_admin_can_delete_completed_report_and_file(): void
    {
        Storage::fake();
        config(['queue.default' => 'database']);
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('reports.store'))->assertRedirect();
        $report = Report::latest()->firstOrFail();
        Storage::assertExists($report->file_path);

        $this->actingAs($admin)
            ->delete(route('reports.destroy', $report))
            ->assertRedirect(route('reports.index'));

        $this->assertDatabaseMissing('reports', ['id' => $report->id]);
        Storage::assertMissing($report->file_path);
    }

    public function test_admin_can_delete_failed_report_without_file(): void
    {
        $admin = $this->admin();
        $report = Report::create(['title' => 'Laporan gagal', 'status' => 'failed']);

        $this->actingAs($admin)
            ->delete(route('reports.destroy', $report))
            ->assertRedirect(route('reports.index'));

        $this->assertDatabaseMissing('reports', ['id' => $report->id]);
    }

    public function test_staff_cannot_delete_report(): void
    {
        $staff = User::create([
            'name' => 'Staff Hapus',
            'email' => 'staff_hapus@nopal.local',
            'google_id' => 'google-hapus-'.uniqid(),
            'role' => 'staff',
            'is_active' => true,
        ]);
        $report = Report::create(['title' => 'Laporan jaga', 'status' => 'failed']);

        $this->actingAs($staff)
            ->delete(route('reports.destroy', $report))
            ->assertForbidden();

        $this->assertDatabaseHas('reports', ['id' => $report->id]);
    }
}
