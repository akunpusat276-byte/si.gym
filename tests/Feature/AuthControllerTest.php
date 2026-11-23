<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_shows_a_forgot_password_link(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Remember me')
            ->assertSee('Lupa Password?')
            ->assertSee(route('password.request'));
    }

    public function test_guest_can_open_the_password_recovery_form(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('Lupa Password?')
            ->assertSee('name="email"', false)
            ->assertSee(route('password.email'));
    }

    public function test_password_recovery_sends_a_reset_notification_to_registered_email(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'member@example.test']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status', 'Jika alamat email terdaftar, kami akan mengirimkan tautan untuk mengatur ulang password.');

        Notification::assertSentTo($user, ResetPassword::class);
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_password_recovery_does_not_disclose_unknown_email_addresses(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'unknown@example.test'])
            ->assertRedirect()
            ->assertSessionHas('status', 'Jika alamat email terdaftar, kami akan mengirimkan tautan untuk mengatur ulang password.');

        Notification::assertNothingSent();
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_password_recovery_rejects_an_invalid_email_address(): void
    {
        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'not-an-email'])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors(['email']);

        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_valid_reset_token_changes_password_and_returns_to_login(): void
    {
        $user = User::factory()->create(['password' => 'Old-Password1']);
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'New-Password2',
            'password_confirmation' => 'New-Password2',
        ])
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Password berhasil diatur ulang. Silakan masuk dengan password baru.');

        $this->assertTrue(Hash::check('New-Password2', $user->refresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_invalid_reset_token_does_not_change_password(): void
    {
        $user = User::factory()->create(['password' => 'Old-Password1']);

        $this->from(route('password.reset', ['token' => 'invalid-token']))
            ->post(route('password.update'), [
                'token' => 'invalid-token',
                'email' => $user->email,
                'password' => 'New-Password2',
                'password_confirmation' => 'New-Password2',
            ])
            ->assertRedirect(route('password.reset', ['token' => 'invalid-token']))
            ->assertSessionHasErrors(['email']);

        $this->assertTrue(Hash::check('Old-Password1', $user->refresh()->password));
    }

    public function test_reset_link_opens_password_form_with_email_and_token(): void
    {
        $user = User::factory()->create();

        $this->get(route('password.reset', ['token' => 'reset-token']).'?email='.urlencode($user->email))
            ->assertOk()
            ->assertSee('Password Baru')
            ->assertSee('value="reset-token"', false)
            ->assertSee('value="'.$user->email.'"', false);
    }

    public function test_member_profile_update_synchronizes_member_contact_information(): void
    {
        $memberUser = User::factory()->create(['role' => 'member']);
        $member = Member::create([
            'user_id' => $memberUser->id,
            'member_code' => 'MBR-'.$memberUser->id,
            'phone' => '08000000000',
        ]);

        $response = $this->actingAs($memberUser)->put(route('member.profile.update'), [
            'name' => 'Budi Santoso',
            'phone' => '08123456789',
            'gender' => 'Laki-laki',
            'birth_date' => '2000-01-01',
            'address' => 'Jl. Sehat No. 1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $memberUser->id,
            'name' => 'Budi Santoso',
            'phone' => '08123456789',
            'gender' => 'Laki-laki',
            'address' => 'Jl. Sehat No. 1',
        ]);
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'phone' => '08123456789',
            'gender' => 'Laki-laki',
            'address' => 'Jl. Sehat No. 1',
        ]);
    }

    public function test_registrant_can_update_only_their_own_profile(): void
    {
        $registrant = User::factory()->create(['role' => 'pendaftar']);
        $otherRegistrant = User::factory()->create(['role' => 'pendaftar']);

        $this->actingAs($registrant)
            ->put(route('registrant.profile.update'), [
                'name' => 'Calon Member',
                'phone' => '0811222333',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $registrant->id,
            'name' => 'Calon Member',
            'phone' => '0811222333',
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $otherRegistrant->id,
            'name' => $otherRegistrant->name,
        ]);
    }

    public function test_member_can_update_their_email_and_owner_can_manage_their_own_profile(): void
    {
        $member = User::factory()->create(['role' => 'member', 'email' => 'member-old@example.test']);
        $this->actingAs($member)
            ->put(route('member.profile.update'), [
                'name' => $member->name,
                'email' => 'member-new@example.test',
            ])
            ->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $member->id, 'email' => 'member-new@example.test']);

        $owner = User::factory()->create(['role' => 'owner', 'email' => 'owner-old@example.test']);
        $this->actingAs($owner)
            ->get(route('owner.profile'))
            ->assertOk()
            ->assertSee('owner-old@example.test');
        $this->put(route('owner.profile.update'), [
            'name' => 'Owner SI-GYM',
            'email' => 'owner-new@example.test',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $owner->id,
            'name' => 'Owner SI-GYM',
            'email' => 'owner-new@example.test',
        ]);
        $this->put('/owner/settings', ['gym_name' => 'Tidak boleh diubah'])->assertForbidden();
    }
}
