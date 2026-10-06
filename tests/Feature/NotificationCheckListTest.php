<?php

namespace Tests\Feature;

use App\Models\NotificationMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCheckListTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin Checklist',
            'email' => 'admin_checklist@nopal.local',
            'google_id' => 'google-checklist-'.uniqid(),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_form_renders_searchable_check_list_with_preview(): void
    {
        $admin = $this->admin();
        $target = User::create([
            'name' => 'Target Checklist',
            'email' => 'target_checklist@nopal.local',
            'google_id' => 'google-checklist-target-'.uniqid(),
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('data-check-list="recipients"', false)
            ->assertSee('Cari nama atau email', false)
            ->assertSee('data-check-all', false)
            ->assertSee('name="recipients[]"', false)
            ->assertSee('value="'.$target->email.'"', false)
            ->assertSee('Pratinjau', false)
            ->assertSee('data-preview-subject', false)
            ->assertSee('data-preview-count', false)
            ->assertSee('data-char-count', false)
            ->assertSee('id="notif-send"', false);
    }

    public function test_old_recipients_stay_checked_after_validation_error(): void
    {
        $admin = $this->admin();
        $target = User::create([
            'name' => 'Target Old',
            'email' => 'target_old@nopal.local',
            'google_id' => 'google-checklist-old-'.uniqid(),
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->actingAs($admin)->from(route('notifications.index'))->followingRedirects()
            ->post(route('notifications.store'), [
                'channel' => 'internal',
                'scope' => 'specific',
                'subject' => '',
                'message' => 'Isi pesan.',
                'recipients' => [$target->email],
            ])
            ->assertSee('checked', false);

        $this->assertEquals(0, NotificationMessage::count());
    }

    public function test_history_status_uses_badge_and_action_buttons(): void
    {
        $admin = $this->admin();
        $notification = NotificationMessage::create([
            'channel' => 'internal',
            'recipient' => $admin->email,
            'subject' => 'Subjek Badge',
            'message' => 'Isi.',
            'status' => 'sent',
        ]);

        $this->actingAs($admin)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('<span class="badge', false)
            ->assertSee('>sent</span>', false)
            ->assertSee('Tandai dibaca', false);
    }
}
