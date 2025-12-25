<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class InvitationController extends Controller
{
    public function show(string $token)
    {
        $user = User::where('invitation_token', $token)
            ->whereNull('invitation_accepted_at')
            ->first();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'Denne invitasjonslenken er ugyldig eller har allerede blitt brukt.');
        }

        return view('auth.accept-invitation', compact('user', 'token'));
    }

    public function accept(Request $request, string $token)
    {
        $user = User::where('invitation_token', $token)
            ->whereNull('invitation_accepted_at')
            ->first();

        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'Denne invitasjonslenken er ugyldig eller har allerede blitt brukt.');
        }

        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->acceptInvitation($request->password);

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Velkommen! Din konto er nå aktivert.');
    }
}
