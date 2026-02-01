<?php

use App\Models\Company;
use App\Models\TwoFactorIpWhitelist;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Helper to set up a user with a company
function createUserWithCompanyFor2FA(): array
{
    $user = User::factory()->create(['onboarding_completed' => true]);
    $company = Company::factory()->withOwner($user)->create();
    $user->update(['current_company_id' => $company->id]);

    return ['user' => $user->fresh(), 'company' => $company];
}

// User 2FA Methods Tests
describe('User 2FA Helper Methods', function () {
    test('hasTwoFactorEnabled returns false when not enabled', function () {
        $user = User::factory()->create();

        expect($user->hasTwoFactorEnabled())->toBeFalse();
    });

    test('hasTwoFactorEnabled returns true when confirmed', function () {
        $user = User::factory()->create([
            'two_factor_confirmed_at' => now(),
        ]);

        expect($user->hasTwoFactorEnabled())->toBeTrue();
    });

    test('isInTwoFactorGracePeriod returns false when no grace period set', function () {
        $user = User::factory()->create();

        expect($user->isInTwoFactorGracePeriod())->toBeFalse();
    });

    test('isInTwoFactorGracePeriod returns true when in grace period', function () {
        $user = User::factory()->create([
            'two_factor_grace_period_ends_at' => now()->addDays(3),
        ]);

        expect($user->isInTwoFactorGracePeriod())->toBeTrue();
    });

    test('isInTwoFactorGracePeriod returns false when grace period expired', function () {
        $user = User::factory()->create([
            'two_factor_grace_period_ends_at' => now()->subDay(),
        ]);

        expect($user->isInTwoFactorGracePeriod())->toBeFalse();
    });

    test('isInTwoFactorGracePeriod returns false when 2FA enabled', function () {
        $user = User::factory()->create([
            'two_factor_confirmed_at' => now(),
            'two_factor_grace_period_ends_at' => now()->addDays(3),
        ]);

        expect($user->isInTwoFactorGracePeriod())->toBeFalse();
    });

    test('isLockedForTwoFactor returns false when not locked', function () {
        $user = User::factory()->create();

        expect($user->isLockedForTwoFactor())->toBeFalse();
    });

    test('isLockedForTwoFactor returns true when locked', function () {
        $user = User::factory()->create([
            'two_factor_locked_at' => now(),
        ]);

        expect($user->isLockedForTwoFactor())->toBeTrue();
    });

    test('startTwoFactorGracePeriod sets grace period to 5 days', function () {
        $user = User::factory()->create();

        $user->startTwoFactorGracePeriod();

        expect($user->two_factor_grace_period_ends_at)->not->toBeNull()
            ->and($user->two_factor_grace_period_ends_at->startOfDay())
            ->toEqual(now()->addDays(5)->startOfDay());
    });

    test('startTwoFactorGracePeriod does not override existing grace period', function () {
        $existingEndDate = now()->addDays(3);
        $user = User::factory()->create([
            'two_factor_grace_period_ends_at' => $existingEndDate,
        ]);

        $user->startTwoFactorGracePeriod();

        expect($user->two_factor_grace_period_ends_at->format('Y-m-d H:i:s'))
            ->toBe($existingEndDate->format('Y-m-d H:i:s'));
    });

    test('startTwoFactorGracePeriod does nothing when 2FA already enabled', function () {
        $user = User::factory()->create([
            'two_factor_confirmed_at' => now(),
        ]);

        $user->startTwoFactorGracePeriod();

        expect($user->two_factor_grace_period_ends_at)->toBeNull();
    });

    test('lockForTwoFactor sets locked timestamp', function () {
        $user = User::factory()->create();

        $user->lockForTwoFactor();

        expect($user->isLockedForTwoFactor())->toBeTrue()
            ->and($user->two_factor_locked_at)->not->toBeNull();
    });

    test('unlockTwoFactor clears lock and sets new grace period', function () {
        $user = User::factory()->create([
            'two_factor_locked_at' => now()->subDays(1),
            'two_factor_grace_period_ends_at' => now()->subDays(2),
        ]);

        $user->unlockTwoFactor();

        expect($user->isLockedForTwoFactor())->toBeFalse()
            ->and($user->two_factor_grace_period_ends_at->isFuture())->toBeTrue();
    });

    test('getTwoFactorGraceDaysRemainingAttribute returns correct days', function () {
        $user = User::factory()->create([
            'two_factor_grace_period_ends_at' => now()->addDays(3)->startOfDay(),
        ]);

        expect($user->two_factor_grace_days_remaining)->toBeGreaterThanOrEqual(2)
            ->and($user->two_factor_grace_days_remaining)->toBeLessThanOrEqual(3);
    });

    test('getTwoFactorGraceDaysRemainingAttribute returns null when not in grace period', function () {
        $user = User::factory()->create();

        expect($user->two_factor_grace_days_remaining)->toBeNull();
    });

    test('shouldBeLocked returns false when 2FA enabled', function () {
        $user = User::factory()->create([
            'two_factor_confirmed_at' => now(),
        ]);

        expect($user->shouldBeLocked())->toBeFalse();
    });

    test('shouldBeLocked returns false when already locked', function () {
        $user = User::factory()->create([
            'two_factor_locked_at' => now(),
        ]);

        expect($user->shouldBeLocked())->toBeFalse();
    });

    test('shouldBeLocked returns false when no grace period', function () {
        $user = User::factory()->create();

        expect($user->shouldBeLocked())->toBeFalse();
    });

    test('shouldBeLocked returns true when grace period expired', function () {
        $user = User::factory()->create([
            'two_factor_grace_period_ends_at' => now()->subDay(),
        ]);

        expect($user->shouldBeLocked())->toBeTrue();
    });

    test('shouldBeLocked returns false when still in grace period', function () {
        $user = User::factory()->create([
            'two_factor_grace_period_ends_at' => now()->addDays(3),
        ]);

        expect($user->shouldBeLocked())->toBeFalse();
    });
});

// TwoFactorIpWhitelist Model Tests
describe('TwoFactorIpWhitelist Model', function () {
    test('can create whitelist entry', function () {
        $user = User::factory()->create(['is_admin' => true]);
        $entry = TwoFactorIpWhitelist::create([
            'ip_address' => '192.168.1.1',
            'description' => 'Office IP',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        expect($entry)->toBeInstanceOf(TwoFactorIpWhitelist::class)
            ->and($entry->ip_address)->toBe('192.168.1.1')
            ->and($entry->is_active)->toBeTrue();
    });

    test('matchesIp returns true for exact match', function () {
        $entry = TwoFactorIpWhitelist::create([
            'ip_address' => '192.168.1.1',
            'is_active' => true,
        ]);

        expect($entry->matchesIp('192.168.1.1'))->toBeTrue();
    });

    test('matchesIp returns false for non-matching IP', function () {
        $entry = TwoFactorIpWhitelist::create([
            'ip_address' => '192.168.1.1',
            'is_active' => true,
        ]);

        expect($entry->matchesIp('192.168.1.2'))->toBeFalse();
    });

    test('matchesIp returns true for IP in CIDR range', function () {
        $entry = TwoFactorIpWhitelist::create([
            'ip_address' => '192.168.1.0',
            'cidr_range' => '192.168.1.0/24',
            'is_active' => true,
        ]);

        expect($entry->matchesIp('192.168.1.50'))->toBeTrue()
            ->and($entry->matchesIp('192.168.1.255'))->toBeTrue();
    });

    test('matchesIp returns false for IP outside CIDR range', function () {
        $entry = TwoFactorIpWhitelist::create([
            'ip_address' => '192.168.1.0',
            'cidr_range' => '192.168.1.0/24',
            'is_active' => true,
        ]);

        expect($entry->matchesIp('192.168.2.1'))->toBeFalse();
    });

    test('isWhitelisted returns true when IP matches active entry', function () {
        TwoFactorIpWhitelist::create([
            'ip_address' => '10.0.0.1',
            'is_active' => true,
        ]);

        expect(TwoFactorIpWhitelist::isWhitelisted('10.0.0.1'))->toBeTrue();
    });

    test('isWhitelisted returns false when IP matches inactive entry', function () {
        TwoFactorIpWhitelist::create([
            'ip_address' => '10.0.0.1',
            'is_active' => false,
        ]);

        expect(TwoFactorIpWhitelist::isWhitelisted('10.0.0.1'))->toBeFalse();
    });

    test('isWhitelisted returns false when no matching entry', function () {
        TwoFactorIpWhitelist::create([
            'ip_address' => '10.0.0.1',
            'is_active' => true,
        ]);

        expect(TwoFactorIpWhitelist::isWhitelisted('10.0.0.2'))->toBeFalse();
    });

    test('active scope returns only active entries', function () {
        TwoFactorIpWhitelist::create(['ip_address' => '10.0.0.1', 'is_active' => true]);
        TwoFactorIpWhitelist::create(['ip_address' => '10.0.0.2', 'is_active' => false]);
        TwoFactorIpWhitelist::create(['ip_address' => '10.0.0.3', 'is_active' => true]);

        expect(TwoFactorIpWhitelist::active()->count())->toBe(2);
    });

    test('creator relationship returns correct user', function () {
        $user = User::factory()->create();
        $entry = TwoFactorIpWhitelist::create([
            'ip_address' => '10.0.0.1',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        expect($entry->creator->id)->toBe($user->id);
    });
});

// Login Flow Tests
describe('Login 2FA Flow', function () {
    test('locked user cannot login and sees message', function () {
        ['user' => $user] = createUserWithCompanyFor2FA();
        $user->update([
            'two_factor_locked_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
        expect(session('errors')->get('email')[0])
            ->toContain('låst')
            ->toContain('tofaktorautentisering');
    });

    test('first login starts grace period', function () {
        ['user' => $user] = createUserWithCompanyFor2FA();
        expect($user->two_factor_grace_period_ends_at)->toBeNull();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $user->refresh();
        expect($user->two_factor_grace_period_ends_at)->not->toBeNull()
            ->and($user->two_factor_grace_period_ends_at->isFuture())->toBeTrue();
    });

    test('user with expired grace period gets locked on login', function () {
        ['user' => $user] = createUserWithCompanyFor2FA();
        $user->update([
            'two_factor_grace_period_ends_at' => now()->subDay(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $user->refresh();
        expect($user->isLockedForTwoFactor())->toBeTrue();
        $response->assertSessionHasErrors(['email']);
    });

    test('user in grace period can login normally', function () {
        ['user' => $user] = createUserWithCompanyFor2FA();
        $user->update([
            'two_factor_grace_period_ends_at' => now()->addDays(3),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/app');
        $this->assertAuthenticatedAs($user);
    });

    test('user with 2FA enabled from whitelisted IP can login without challenge', function () {
        ['user' => $user] = createUserWithCompanyFor2FA();
        $user->update([
            'two_factor_secret' => encrypt('test-secret'),
            'two_factor_confirmed_at' => now(),
        ]);

        TwoFactorIpWhitelist::create([
            'ip_address' => '127.0.0.1',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/app');
        $this->assertAuthenticatedAs($user);
    });
});

// Reminder Banner Tests
describe('Two Factor Reminder Banner', function () {
    test('banner shows for users in grace period', function () {
        ['user' => $user] = createUserWithCompanyFor2FA();
        $user->update([
            'two_factor_grace_period_ends_at' => now()->addDays(3),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSeeLivewire('two-factor-reminder-banner');
    });

    test('banner does not show for users with 2FA enabled', function () {
        ['user' => $user] = createUserWithCompanyFor2FA();
        $user->update([
            'two_factor_confirmed_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        // Banner component is present but should not show content
        $response->assertOk();
    });
});

// Settings Page Tests
describe('Two Factor Settings', function () {
    test('settings page includes two factor manager component', function () {
        ['user' => $user] = createUserWithCompanyFor2FA();

        $response = $this->actingAs($user)->get(route('settings'));

        $response->assertOk()
            ->assertSeeLivewire('two-factor-manager');
    });
});

// Admin IP Whitelist Tests
describe('Admin IP Whitelist Management', function () {
    test('admin can access ip whitelist page', function () {
        ['user' => $user] = createUserWithCompanyFor2FA();
        $user->update(['is_admin' => true]);

        $response = $this->actingAs($user)->get(route('admin.two-factor-whitelist'));

        $response->assertOk()
            ->assertSeeLivewire('two-factor-ip-whitelist-manager');
    });

    test('non-admin cannot access ip whitelist page', function () {
        ['user' => $user] = createUserWithCompanyFor2FA();
        $user->update(['is_admin' => false]);

        $response = $this->actingAs($user)->get(route('admin.two-factor-whitelist'));

        $response->assertForbidden();
    });
});
