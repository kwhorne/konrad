<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Services\ModuleService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(protected ModuleService $moduleService) {}

    public function index(Request $request)
    {
        $company = $request->user()->currentCompany;

        $premiumModules = $this->moduleService->getPremiumModulesWithStatus($company);

        return view('subscription.index', [
            'company' => $company,
            'premiumModules' => $premiumModules,
        ]);
    }

    public function checkout(Request $request, Module $module)
    {
        $company = $request->user()->currentCompany;

        if (! $module->is_premium || ! $module->stripe_price_id) {
            return back()->with('error', 'Denne modulen kan ikke kjøpes.');
        }

        // Check if already subscribed
        if ($company->hasModule($module->slug)) {
            return back()->with('error', 'Du har allerede tilgang til denne modulen.');
        }

        return $company->newSubscription($module->slug, $module->stripe_price_id)
            ->checkout([
                'success_url' => route('subscription.success', ['module' => $module->id]),
                'cancel_url' => route('subscription.index'),
            ]);
    }

    public function success(Request $request, Module $module)
    {
        return redirect()->route('subscription.index')
            ->with('success', "{$module->name} er nå aktivert for ditt selskap!");
    }

    public function manage(Request $request)
    {
        $company = $request->user()->currentCompany;

        return $company->redirectToBillingPortal(route('subscription.index'));
    }
}
