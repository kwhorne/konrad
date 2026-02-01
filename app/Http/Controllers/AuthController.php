<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Project;
use App\Models\Quote;
use App\Models\User;
use App\Models\WorkOrder;
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

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Record last login
            Auth::user()->recordLogin();

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

    public function dashboard()
    {
        $hour = now()->hour;
        $greeting = match (true) {
            $hour < 12 => 'morgen',
            $hour < 17 => 'ettermiddag',
            default => 'kveld'
        };

        // Financial stats (customer invoices only)
        $unpaidInvoices = Invoice::invoices()->unpaid()->sum('balance');
        $overdueInvoices = Invoice::invoices()->overdue()->sum('balance');
        $overdueInvoicesCount = Invoice::invoices()->overdue()->count();

        // Feature-dependent stats
        $stats = [];

        if (config('features.sales')) {
            $stats['activeQuotes'] = Quote::active()->whereHas('quoteStatus', fn ($q) => $q->whereIn('code', ['draft', 'sent']))->count();
            $stats['activeQuotesValue'] = Quote::active()->whereHas('quoteStatus', fn ($q) => $q->whereIn('code', ['draft', 'sent']))->sum('total');
            $stats['openOrders'] = Order::active()->whereHas('orderStatus', fn ($q) => $q->whereNotIn('code', ['completed', 'cancelled']))->count();
        }

        if (config('features.projects')) {
            $stats['activeProjects'] = Project::active()->count();
        }

        if (config('features.work_orders')) {
            $stats['openWorkOrders'] = WorkOrder::active()->whereHas('workOrderStatus', fn ($q) => $q->whereNotIn('code', ['completed', 'cancelled']))->count();
        }

        if (config('features.contacts')) {
            $stats['totalContacts'] = Contact::active()->count();
        }

        // Recent customer invoices
        $recentInvoices = Invoice::invoices()
            ->with(['contact', 'invoiceStatus'])
            ->latest()
            ->take(5)
            ->get();

        return view('app.dashboard', compact(
            'greeting',
            'unpaidInvoices',
            'overdueInvoices',
            'overdueInvoicesCount',
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
