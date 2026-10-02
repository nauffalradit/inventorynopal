<?php

namespace Tests\Feature;

use App\Http\Controllers\NotificationController;
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

    public function test_admin_and_staff_can_view_notification_detail(): void
    {
        $admin = $this->user('admin');
        $staff = $this->user();
        $notification = NotificationMessage::create([
            'channel' => 'internal',
            'recipient' => $staff->email,
            'subject' => 'Stok opname',
            'message' => 'Isi lengkap pengumuman opname akhir pekan.',
            'status' => 'sent',
        ]);

        foreach ([$admin, $staff] as $user) {
            $this->actingAs($user)->get(route('notifications.show', $notification))
                ->assertOk()
                ->assertSee('Stok opname')
                ->assertSee('Isi lengkap pengumuman opname akhir pekan.');
        }
    }

    public function test_guest_cannot_view_notification_detail(): void
    {
        $notification = NotificationMessage::create([
            'channel' => 'internal',
            'recipient' => 'x@nopal.local',
            'subject' => 'Rahasia',
            'message' => 'Jangan intip.',
            'status' => 'sent',
        ]);

        $this->get(route('notifications.show', $notification))->assertRedirect(route('login'));
        $this->get(route('notifications.show', 999999))->assertRedirect(route('login'));
    }

    private function sentTo(string $email): NotificationMessage
    {
        return NotificationMessage::create([
            'channel' => 'internal',
            'recipient' => $email,
            'subject' => 'Info',
            'message' => 'Isi info.',
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function test_owner_can_mark_own_notification_read(): void
    {
        $owner = $this->user();
        $notification = $this->sentTo($owner->email);

        $this->actingAs($owner)
            ->patch(route('notifications.read', $notification))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_non_owner_staff_cannot_mark_read(): void
    {
        $owner = $this->user();
        $other = $this->user();
        $notification = $this->sentTo($owner->email);

        $this->actingAs($other)
            ->patch(route('notifications.read', $notification))
            ->assertForbidden();

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_admin_can_mark_others_and_double_mark_is_idempotent(): void
    {
        $admin = $this->user('admin');
        $owner = $this->user();
        $notification = $this->sentTo($owner->email);

        $this->actingAs($admin)->patch(route('notifications.read', $notification))->assertRedirect();
        $first = $notification->fresh()->read_at;
        $this->assertNotNull($first);

        $this->actingAs($admin)->patch(route('notifications.read', $notification))->assertRedirect();
        $this->assertEquals($first->toDateTimeString(), $notification->fresh()->read_at->toDateTimeString());
    }

    public function test_admin_can_delete_and_staff_cannot(): void
    {
        $admin = $this->user('admin');
        $staff = $this->user();
        $one = $this->sentTo($staff->email);
        $two = $this->sentTo($staff->email);

        $this->actingAs($staff)->delete(route('notifications.destroy', $one))->assertForbidden();
        $this->assertDatabaseHas('notification_messages', ['id' => $one->id]);

        $this->actingAs($admin)->delete(route('notifications.destroy', $two))
            ->assertRedirect(route('notifications.index'));
        $this->assertDatabaseMissing('notification_messages', ['id' => $two->id]);
    }

    public function test_bell_counts_only_my_unread_sent(): void
    {
        $admin = $this->user('admin');
        $me = $this->user();
        $this->sentTo($me->email);
        $this->sentTo($me->email);
        $read = $this->sentTo($me->email);
        $read->update(['read_at' => now()]);
        $this->sentTo($admin->email); // milik orang lain → tidak ikut

        $this->assertEquals(2, NotificationController::unreadCount($me->id, $me->email));

        $this->actingAs($me)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('title="Notifikasi"', false);
    }
}
