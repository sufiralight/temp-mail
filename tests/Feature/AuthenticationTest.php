<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->path = sys_get_temp_dir().'/rip-auth-'.Str::uuid();
        File::makeDirectory($this->path, 0700);
        $this->app->useEnvironmentPath($this->path);
        File::put($this->path.'/.env', '');
        File::put($this->path.'/state', 'complete-v1');
        config(['installation.path' => $this->path, 'cache.stores.file.lock_path' => $this->path]);
        Notification::fake();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->path);
        parent::tearDown();
    }

    public function test_preinstall_authentication_is_unavailable(): void
    {
        File::delete($this->path.'/state');
        $this->get('/login')->assertNotFound();
        $this->post('/register')->assertNotFound();
        $this->get('/account')->assertNotFound();
    }

    public function test_login_regenerates_session_and_ignores_external_intended_url(): void
    {
        $user = User::factory()->create(['is_super_admin' => true]);
        $this->get('/login')->assertOk()->assertSee('Remember me');
        $this->get('/account')->assertRedirect('/login');
        $before = session()->getId();
        $this->withSession(['url.intended' => 'https://example.invalid/'])
            ->post('/login', ['email' => strtoupper($user->email), 'password' => 'password', 'remember' => '1'])
            ->assertRedirect('/account')->assertCookie(auth()->guard()->getRecallerName());
        $this->assertNotSame($before, session()->getId());
        $this->assertAuthenticatedAs($user);
        $this->get('/account')->assertOk()->assertSee('Super Admin');
        $this->get('/logout')->assertMethodNotAllowed();
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_login_errors_are_generic_and_throttled(): void
    {
        for ($attempt = 0; $attempt < 8; $attempt++) {
            $this->post('/login', ['email' => 'unknown@example.test', 'password' => 'wrong'])
                ->assertSessionHasErrors(['email' => 'The email or password is incorrect.']);
        }
        $this->post('/login')->assertTooManyRequests();
    }

    public function test_registration_ignores_privilege_fields_and_requires_verification(): void
    {
        $password = Str::random(30);
        $this->post('/register', [
            'name' => 'Normal User', 'email' => 'USER@EXAMPLE.TEST',
            'password' => $password, 'password_confirmation' => $password,
            'is_super_admin' => true, 'role' => 'super-admin',
        ])->assertRedirect('/email/verify');

        $user = User::firstOrFail();
        $this->assertSame('user@example.test', $user->email);
        $this->assertFalse($user->is_super_admin);
        $this->assertFalse($user->can('super-admin'));
        $this->assertTrue(Hash::check($password, $user->password));
        Notification::assertSentTo($user, VerifyEmail::class);
        $this->get('/account')->assertRedirect('/email/verify');
        $params = ['id' => $user->id, 'hash' => sha1($user->email)];
        $this->get(route('verification.verify', $params))->assertForbidden();
        $this->get(URL::temporarySignedRoute('verification.verify', now()->addMinutes(10), $params))->assertRedirect('/account');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_resend_is_throttled(): void
    {
        $user = User::factory()->unverified()->create();
        $this->actingAs($user);
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $this->post('/email/verification-notification')->assertRedirect();
        }
        $this->post('/email/verification-notification')->assertTooManyRequests();
        Notification::assertSentToTimes($user, VerifyEmail::class, 3);
    }

    public function test_password_reset_is_generic_expiring_and_single_use(): void
    {
        $user = User::factory()->create(['remember_token' => Str::random(60)]);
        $this->post('/forgot-password', ['email' => $user->email])->assertRedirect();
        $message = session('status');
        $this->post('/forgot-password', ['email' => 'unknown@example.test'])->assertSessionHas('status', $message);
        Notification::assertSentTo($user, ResetPassword::class);

        $expired = Password::createToken($user);
        $this->travel(61)->minutes();
        $password = Str::random(30);
        $fields = ['email' => $user->email, 'password' => $password, 'password_confirmation' => $password];
        $this->post('/reset-password', $fields + ['token' => $expired])->assertUnprocessable()->assertSee('This reset link is invalid or expired.');
        $token = Password::createToken($user);
        $this->get('/reset-password/'.$token)->assertOk()->assertHeader('Referrer-Policy', 'no-referrer');
        $this->post('/reset-password', $fields + ['token' => $token])->assertRedirect('/login');
        $this->assertTrue(Hash::check($password, $user->fresh()->password));
        $this->assertNotSame($user->remember_token, $user->fresh()->remember_token);
        $this->post('/reset-password', $fields + ['token' => $token])->assertUnprocessable()->assertSee('This reset link is invalid or expired.');
    }

    public function test_authentication_posts_enforce_csrf_outside_the_test_bypass(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        $this->post('/login', ['email' => 'user@example.test', 'password' => 'invalid'])->assertStatus(419);
    }

    public function test_mail_failure_does_not_expose_transport_detail_or_block_registration(): void
    {
        config(['mail.default' => 'smtp']);
        Notification::shouldReceive('send')->once()
            ->andThrow(new TransportException('private transport detail'));
        $password = Str::random(30);
        $this->post('/register', [
            'name' => 'Mail Failure', 'email' => 'mail@example.test',
            'password' => $password, 'password_confirmation' => $password,
        ])->assertRedirect('/email/verify')
            ->assertSessionHas('status', 'Verification email is unavailable right now. Try resending later.');
        $this->assertAuthenticated();
    }
}
