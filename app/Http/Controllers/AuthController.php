<?php

namespace App\Http\Controllers;

use App\Models\IncomingVoucher;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Project;
use App\Models\Quote;
use App\Models\Timesheet;
use App\Models\TwoFactorIpWhitelist;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\TimesheetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Check if user exists and is active
        $user = User::where('email', $credentials['email'])->first();

        if ($user && ! $user->is_active) {
            return back()->withErrors([
                'email' => 'Denne kontoen er deaktivert. Kontakt administrator.',
            ])->onlyInput('email');
        }

        // Check if user is locked for not enabling 2FA
        if ($user && $user->isLockedForTwoFactor()) {
            return back()->withErrors([
                'email' => 'Din konto er låst fordi tofaktorautentisering ikke ble aktivert innen fristen. Kontakt support@konradoffice.no for å låse opp kontoen.',
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            // Record last login
            $user->recordLogin();

            // Check if user should be locked (grace period expired)
            if ($user->shouldBeLocked()) {
                $user->lockForTwoFactor();
                Auth::logout();
                $request->session()->invalidate();

                return back()->withErrors([
                    'email' => 'Din konto er låst fordi tofaktorautentisering ikke ble aktivert innen fristen. Kontakt support@konradoffice.no for å låse opp kontoen.',
                ])->onlyInput('email');
            }

            // Start grace period for new users without 2FA
            if (! $user->hasEnabledTwoFactorAuthentication() && ! $user->two_factor_grace_period_ends_at) {
                $user->startTwoFactorGracePeriod();
            }

            // Check if 2FA is enabled and IP is not whitelisted
            if ($user->hasEnabledTwoFactorAuthentication() && ! TwoFactorIpWhitelist::isWhitelisted($request->ip())) {
                // Store user ID in session for 2FA challenge
                $request->session()->put('login.id', $user->id);
                $request->session()->put('login.remember', $request->boolean('remember'));

                // Logout user until 2FA is verified
                Auth::logout();

                return redirect()->route('two-factor.login');
            }

            return redirect()->intended('app');
        }

        return back()->withErrors([
            'email' => 'E-post eller passord er feil.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function dashboard(TimesheetService $timesheetService)
    {
        $user = Auth::user();
        $hour = now()->hour;
        $greeting = match (true) {
            $hour < 12 => 'morgen',
            $hour < 17 => 'ettermiddag',
            default => 'kveld'
        };

        // ===== TIMESHEET DATA (alle brukere) =====
        $currentWeekStart = Carbon::now()->startOfWeek();
        $myTimesheet = $timesheetService->getOrCreateTimesheet($user, $currentWeekStart);
        $myHoursThisWeek = $myTimesheet->total_hours ?? 0;
        $myTimesheetStatus = $myTimesheet->status;

        // Timesheets til godkjenning (for ledere)
        $pendingTimesheets = [];
        $pendingTimesheetsCount = 0;
        if ($user->is_admin || $user->is_economy) {
            $pendingTimesheets = Timesheet::where('status', Timesheet::STATUS_SUBMITTED)
                ->with('user')
                ->latest('submitted_at')
                ->take(5)
                ->get();
            $pendingTimesheetsCount = Timesheet::where('status', Timesheet::STATUS_SUBMITTED)->count();
        }

        // ===== ECONOMY DATA (for økonomi-brukere) =====
        $unpaidInvoices = 0;
        $overdueInvoices = 0;
        $overdueInvoicesCount = 0;
        $incomingVouchersCount = 0;

        if ($user->is_admin || $user->is_economy) {
            $unpaidInvoices = Invoice::invoices()->unpaid()->sum('balance');
            $overdueInvoices = Invoice::invoices()->overdue()->sum('balance');
            $overdueInvoicesCount = Invoice::invoices()->overdue()->count();
            $incomingVouchersCount = IncomingVoucher::whereIn('status', ['pending', 'parsing', 'parsed'])->count();
        }

        // ===== SALES DATA (for salgs-brukere) =====
        $stats = [];
        if ($user->is_admin || $user->is_sales) {
            if (config('features.sales')) {
                $stats['activeQuotes'] = Quote::active()->whereHas('quoteStatus', fn ($q) => $q->whereIn('code', ['draft', 'sent']))->count();
                $stats['activeQuotesValue'] = Quote::active()->whereHas('quoteStatus', fn ($q) => $q->whereIn('code', ['draft', 'sent']))->sum('total');
                $stats['openOrders'] = Order::active()->whereHas('orderStatus', fn ($q) => $q->whereNotIn('code', ['completed', 'cancelled']))->count();
            }
        }

        // ===== PROJECT DATA (for prosjekt-brukere) =====
        if ($user->is_admin || config('features.projects')) {
            $stats['activeProjects'] = Project::active()->count();
        }

        if ($user->is_admin || config('features.work_orders')) {
            $stats['openWorkOrders'] = WorkOrder::active()->whereHas('workOrderStatus', fn ($q) => $q->whereNotIn('code', ['completed', 'cancelled']))->count();
        }

        // Siste fakturaer (kun for økonomi/admin)
        $recentInvoices = collect();
        if ($user->is_admin || $user->is_economy || $user->is_sales) {
            $recentInvoices = Invoice::invoices()
                ->with(['contact', 'invoiceStatus'])
                ->latest()
                ->take(5)
                ->get();
        }

        return view('app.dashboard', compact(
            'greeting',
            'myHoursThisWeek',
            'myTimesheetStatus',
            'myTimesheet',
            'pendingTimesheets',
            'pendingTimesheetsCount',
            'unpaidInvoices',
            'overdueInvoices',
            'overdueInvoicesCount',
            'incomingVouchersCount',
            'stats',
            'recentInvoices'
        ));
    }

    public function settings()
    {
        return view('app.settings');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (! Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors([
                'current_password' => 'The current password is incorrect.',
            ]);
        }

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    // Admin methods
    public function adminUsers()
    {
        return view('admin.users');
    }

    public function adminAnalytics()
    {
        $totalUsers = User::count();
        $adminUsers = User::where('is_admin', true)->count();
        $recentUsers = User::where('created_at', '>=', now()->subDays(7))->count();

        return view('admin.analytics', compact('totalUsers', 'adminUsers', 'recentUsers'));
    }

    public function adminSystem()
    {
        return view('admin.system');
    }

    public function adminHelp()
    {
        return view('admin.help');
    }
}
