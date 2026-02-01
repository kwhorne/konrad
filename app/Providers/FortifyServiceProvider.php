<?php

namespace App\Providers;

use App\Models\TwoFactorIpWhitelist;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Custom views for 2FA
        Fortify::twoFactorChallengeView(function () {
            return view('auth.two-factor-challenge');
        });

        Fortify::confirmPasswordView(function () {
            return view('auth.confirm-password');
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }

    /**
     * Check if the given IP address is whitelisted for 2FA bypass.
     */
    public static function isIpWhitelisted(string $ip): bool
    {
        return TwoFactorIpWhitelist::query()
            ->where('is_active', true)
            ->where(function ($query) use ($ip) {
                $query->where('ip_address', $ip)
                    ->orWhereRaw('? LIKE CONCAT(SUBSTRING_INDEX(ip_address, ".", 3), ".%")', [$ip]);
            })
            ->exists();
    }
}
