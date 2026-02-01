<?php

namespace App\Listeners;

use App\Models\Company;
use App\Models\Module;
use App\Services\ModuleService;
use Illuminate\Events\Dispatcher;
use Laravel\Cashier\Events\WebhookHandled;

class StripeEventSubscriber
{
    public function __construct(protected ModuleService $moduleService) {}

    public function handleWebhookHandled(WebhookHandled $event): void
    {
        $payload = $event->payload;

        match ($payload['type'] ?? null) {
            'customer.subscription.created',
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($payload),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($payload),
            'invoice.payment_succeeded' => $this->handlePaymentSucceeded($payload),
            'invoice.payment_failed' => $this->handlePaymentFailed($payload),
            default => null,
        };
    }

    protected function handleSubscriptionUpdated(array $payload): void
    {
        $subscription = $payload['data']['object'] ?? null;
        if (! $subscription) {
            return;
        }

        $stripeCustomerId = $subscription['customer'] ?? null;
        $status = $subscription['status'] ?? null;
        $subscriptionId = $subscription['id'] ?? null;

        $company = Company::where('stripe_id', $stripeCustomerId)->first();
        if (! $company) {
            return;
        }

        // Get the price IDs from subscription items
        $items = $subscription['items']['data'] ?? [];

        foreach ($items as $item) {
            $priceId = $item['price']['id'] ?? null;
            if (! $priceId) {
                continue;
            }

            $module = Module::where('stripe_price_id', $priceId)->first();
            if (! $module) {
                continue;
            }

            // Enable or update module based on subscription status
            if (in_array($status, ['active', 'trialing'])) {
                $this->moduleService->enableForCompany(
                    $company,
                    $module,
                    'stripe',
                    null,
                    $subscriptionId,
                    $status
                );
            } elseif (in_array($status, ['past_due', 'unpaid'])) {
                // Keep enabled but update status
                $companyModule = $company->modules()->where('module_id', $module->id)->first();
                if ($companyModule) {
                    $company->modules()->updateExistingPivot($module->id, [
                        'stripe_subscription_status' => $status,
                    ]);
                }
            } elseif (in_array($status, ['canceled', 'incomplete_expired'])) {
                $this->moduleService->disableForCompany($company, $module);
            }
        }
    }

    protected function handleSubscriptionDeleted(array $payload): void
    {
        $subscription = $payload['data']['object'] ?? null;
        if (! $subscription) {
            return;
        }

        $stripeCustomerId = $subscription['customer'] ?? null;
        $subscriptionId = $subscription['id'] ?? null;

        $company = Company::where('stripe_id', $stripeCustomerId)->first();
        if (! $company) {
            return;
        }

        // Disable all modules associated with this subscription
        $company->modules()
            ->wherePivot('stripe_subscription_id', $subscriptionId)
            ->get()
            ->each(function ($module) use ($company) {
                $this->moduleService->disableForCompany($company, $module);
            });
    }

    protected function handlePaymentSucceeded(array $payload): void
    {
        $invoice = $payload['data']['object'] ?? null;
        if (! $invoice) {
            return;
        }

        $subscriptionId = $invoice['subscription'] ?? null;
        if (! $subscriptionId) {
            return;
        }

        // Update subscription status to active for associated modules
        $stripeCustomerId = $invoice['customer'] ?? null;
        $company = Company::where('stripe_id', $stripeCustomerId)->first();
        if (! $company) {
            return;
        }

        $company->modules()
            ->wherePivot('stripe_subscription_id', $subscriptionId)
            ->get()
            ->each(function ($module) use ($company) {
                $company->modules()->updateExistingPivot($module->id, [
                    'stripe_subscription_status' => 'active',
                ]);
            });
    }

    protected function handlePaymentFailed(array $payload): void
    {
        $invoice = $payload['data']['object'] ?? null;
        if (! $invoice) {
            return;
        }

        $subscriptionId = $invoice['subscription'] ?? null;
        if (! $subscriptionId) {
            return;
        }

        $stripeCustomerId = $invoice['customer'] ?? null;
        $company = Company::where('stripe_id', $stripeCustomerId)->first();
        if (! $company) {
            return;
        }

        // Update status to past_due for associated modules
        $company->modules()
            ->wherePivot('stripe_subscription_id', $subscriptionId)
            ->get()
            ->each(function ($module) use ($company) {
                $company->modules()->updateExistingPivot($module->id, [
                    'stripe_subscription_status' => 'past_due',
                ]);
            });
    }

    public function subscribe(Dispatcher $events): array
    {
        return [
            WebhookHandled::class => 'handleWebhookHandled',
        ];
    }
}
