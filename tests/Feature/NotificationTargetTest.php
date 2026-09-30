<?php

namespace Tests\Feature;

use App\Models\NotificationMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTargetTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'staff', bool $active = true): User
    {
        return User::create([
            'name' => ucfirst($role).' Notif '.uniqid(),
            'email' => strtolower($role).'_'.uniqid().'@nopal.local',
            'google_id' => 'google-notif-'.uniqid(),
            'role' => $role,
            'is_active' => $active,
        ]);
    }

    private function payload(array $over = []): array
    {
        return array_merge([
            'channel' => 'internal',
            'scope' => 'specific',
            'subject' => 'Pengumuman',
            'message' => 'Stok opname akhir pekan.',
        ], $over);
    }

    public function test_admin_can_send_to_multiple_specific_accounts(): void
    {
        $admin = $this->user('admin');
        $a = $this->user();
        $b = $this->user();

        $this->actingAs($admin)
            ->post(route('notifications.store'), $this->payload(['recipients' => [$a->email, $b->email]]))
            ->assertRedirect(route('notifications.index'));

        $this->assertEquals(2, NotificationMessage::count());
        $this->assertDatabaseHas('notification_messages', ['recipient' => $a->email]);
        $this->assertDatabaseHas('notification_messages', ['recipient' => $b->email]);
    }

    public function test_scope_staff_targets_only_active_staff(): void
    {
        $admin = $this->user('admin');
        $staff = $this->user('staff');
        $this->user('staff', false); // nonaktif → tidak ikut
        $this->user('admin');

        $this->actingAs($admin)
            ->post(route('notifications.store'), $this->payload(['scope' => 'staff']))
            ->assertRedirect(route('notifications.index'));

        $recipients = NotificationMessage::pluck('recipient')->all();
        $this->assertEquals([$staff->email], $recipients);
    }

    public function test_unknown_email_is_rejected(): void
    {
        $admin = $this->user('admin');

        $this->actingAs($admin)
            ->post(route('notifications.store'), $this->payload(['recipients' => ['hantu@nopal.local']]))
            ->assertSessionHasErrors('recipients.0');

        $this->assertEquals(0, NotificationMessage::count());
    }

    public function test_staff_cannot_send_notification(): void
    {
        $staff = $this->user();
        $target = $this->user();

        $this->actingAs($staff)
            ->post(route('notifications.store'), $this->payload(['recipients' => [$target->email]]))
            ->assertForbidden();

        $this->assertEquals(0, NotificationMessage::count());
    }

    public function test_form_uses_scope_pills_and_conditional_recipients_box(): void
    {
        $admin = $this->user('admin');
        $this->user();

        $this->actingAs($admin)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('notif-pills', false)
            ->assertSee('recipients-box', false)
            ->assertSee('name="scope"', false);
    }
}
