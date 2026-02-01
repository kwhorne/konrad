<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Quote;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Log;

class MyActivitiesService
{
    /**
     * Gather all actionable items for the user.
     *
     * @return array<string, mixed>
     */
    public function gatherUserData(User $user): array
    {
        $company = $user->currentCompany;

        if (! $company) {
            return ['error' => 'Ingen selskap valgt'];
        }

        return [
            'user' => [
                'name' => $user->name,
                'id' => $user->id,
            ],
            'company' => [
                'name' => $company->name,
            ],
            'analysis_date' => now()->format('Y-m-d'),
            'activities' => $this->getActivitiesData($user),
            'quotes' => $this->getQuotesData($user),
            'work_orders' => $this->getWorkOrdersData($user),
            'projects' => $this->getProjectsData($user),
            'invoices' => $this->getInvoicesData($user),
        ];
    }

    /**
     * Get pending activities assigned to or created by user.
     *
     * @return array<string, mixed>
     */
    protected function getActivitiesData(User $user): array
    {
        $activities = Activity::with(['contact', 'activityType'])
            ->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id);
            })
            ->pending()
            ->get();

        return [
            'pending_count' => $activities->count(),
            'overdue_count' => $activities->filter(fn ($a) => $a->isOverdue())->count(),
            'items' => $activities->map(fn ($a) => [
                'id' => $a->id,
                'subject' => $a->subject,
                'type' => $a->activityType?->name,
                'due_date' => $a->due_date?->format('Y-m-d'),
                'is_overdue' => $a->isOverdue(),
                'contact_name' => $a->contact?->company_name ?? $a->contact?->name,
            ])->take(10)->values()->toArray(),
        ];
    }

    /**
     * Get quotes created by user that need attention.
     *
     * @return array<string, mixed>
     */
    protected function getQuotesData(User $user): array
    {
        $quotes = Quote::with(['contact', 'quoteStatus'])
            ->where('created_by', $user->id)
            ->whereHas('quoteStatus', fn ($q) => $q->whereIn('code', ['draft', 'sent']))
            ->get();

        $drafts = $quotes->filter(fn ($q) => $q->quoteStatus?->code === 'draft');
        $sent = $quotes->filter(fn ($q) => $q->quoteStatus?->code === 'sent');

        return [
            'draft_count' => $drafts->count(),
            'draft_value' => round($drafts->sum('total'), 2),
            'sent_not_converted_count' => $sent->count(),
            'sent_not_converted_value' => round($sent->sum('total'), 2),
            'items' => $quotes->map(fn ($q) => [
                'id' => $q->id,
                'quote_number' => $q->quote_number,
                'title' => $q->title,
                'customer' => $q->contact?->company_name ?? $q->customer_name,
                'total' => $q->total,
                'status' => $q->quoteStatus?->code,
                'valid_until' => $q->valid_until?->format('Y-m-d'),
                'days_since_sent' => $q->sent_at ? now()->diffInDays($q->sent_at) : null,
            ])->take(10)->values()->toArray(),
        ];
    }

    /**
     * Get work orders assigned to user.
     *
     * @return array<string, mixed>
     */
    protected function getWorkOrdersData(User $user): array
    {
        $workOrders = WorkOrder::with(['contact', 'workOrderStatus', 'workOrderPriority'])
            ->where('assigned_to', $user->id)
            ->whereHas('workOrderStatus', fn ($q) => $q->whereNotIn('code', ['COMPLETED', 'CANCELLED', 'INVOICED']))
            ->get();

        $overdue = $workOrders->filter(fn ($wo) => $wo->due_date && $wo->due_date->isPast());

        return [
            'pending_count' => $workOrders->count(),
            'overdue_count' => $overdue->count(),
            'items' => $workOrders->map(fn ($wo) => [
                'id' => $wo->id,
                'work_order_number' => $wo->work_order_number,
                'title' => $wo->title,
                'customer' => $wo->contact?->company_name,
                'due_date' => $wo->due_date?->format('Y-m-d'),
                'is_overdue' => $wo->due_date && $wo->due_date->isPast(),
                'priority' => $wo->workOrderPriority?->name,
                'status' => $wo->workOrderStatus?->name,
            ])->take(10)->values()->toArray(),
        ];
    }

    /**
     * Get projects where user is manager.
     *
     * @return array<string, mixed>
     */
    protected function getProjectsData(User $user): array
    {
        $projects = Project::with(['contact', 'projectStatus'])
            ->where('manager_id', $user->id)
            ->active()
            ->whereHas('projectStatus', fn ($q) => $q->whereIn('code', ['PLANNING', 'IN_PROGRESS']))
            ->get();

        $overdue = $projects->filter(fn ($p) => $p->end_date && $p->end_date->isPast());

        return [
            'active_count' => $projects->count(),
            'overdue_count' => $overdue->count(),
            'items' => $projects->map(fn ($p) => [
                'id' => $p->id,
                'project_number' => $p->project_number,
                'name' => $p->name,
                'customer' => $p->contact?->company_name,
                'status' => $p->projectStatus?->name,
                'end_date' => $p->end_date?->format('Y-m-d'),
                'is_overdue' => $p->end_date && $p->end_date->isPast(),
            ])->take(10)->values()->toArray(),
        ];
    }

    /**
     * Get invoices created by user that need attention.
     *
     * @return array<string, mixed>
     */
    protected function getInvoicesData(User $user): array
    {
        $invoices = Invoice::with(['contact', 'invoiceStatus'])
            ->where('created_by', $user->id)
            ->invoices()
            ->unpaid()
            ->get();

        $overdue = $invoices->filter(fn ($i) => $i->due_date && $i->due_date->isPast());

        return [
            'unpaid_count' => $invoices->count(),
            'unpaid_value' => round($invoices->sum('balance'), 2),
            'overdue_count' => $overdue->count(),
            'overdue_value' => round($overdue->sum('balance'), 2),
            'items' => $invoices->sortByDesc(fn ($i) => $i->due_date && $i->due_date->isPast())
                ->map(fn ($i) => [
                    'id' => $i->id,
                    'invoice_number' => $i->invoice_number,
                    'customer' => $i->contact?->company_name ?? $i->customer_name,
                    'total' => $i->total,
                    'balance' => $i->balance,
                    'due_date' => $i->due_date?->format('Y-m-d'),
                    'is_overdue' => $i->due_date && $i->due_date->isPast(),
                    'days_overdue' => $i->due_date && $i->due_date->isPast() ? now()->diffInDays($i->due_date) : 0,
                ])->take(10)->values()->toArray(),
        ];
    }

    /**
     * Generate AI suggestions based on user data.
     *
     * @return array<string, mixed>
     */
    public function generateSuggestions(User $user): array
    {
        $userData = $this->gatherUserData($user);

        if (isset($userData['error'])) {
            return ['success' => false, 'error' => $userData['error']];
        }

        try {
            $response = $this->callAI($userData);
            $suggestions = $this->parseResponse($response);

            return [
                'success' => true,
                'suggestions' => $suggestions,
                'summary' => $userData,
                'generated_at' => now()->toIso8601String(),
            ];
        } catch (\Exception $e) {
            Log::error('Feil ved generering av aktivitetsforslag', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'summary' => $userData,
            ];
        }
    }

    /**
     * Call AI for suggestions.
     */
    protected function callAI(array $userData): string
    {
        $provider = config('voucher.ai.provider', 'openai');
        $model = config('voucher.ai.model', 'gpt-4o');

        $response = prism()
            ->text()
            ->using($provider, $model)
            ->withSystemPrompt($this->getSystemPrompt())
            ->withPrompt($this->buildPrompt($userData))
            ->asText();

        return $response->text;
    }

    /**
     * Get system prompt for AI suggestions.
     */
    protected function getSystemPrompt(): string
    {
        return <<<'PROMPT'
Du er en erfaren norsk forretningsrådgiver som hjelper brukere med å prioritere arbeidsoppgaver.
Analyser brukerens ventende oppgaver og gi konkrete, handlingsrettede forslag til oppfølging.

Returner BARE gyldig JSON (ingen markdown, ingen kodeblokker) med følgende struktur:

{
  "summary": "Kort oppsummering av brukerens arbeidssituasjon (1-2 setninger)",
  "priority_score": 0-100,
  "suggestions": [
    {
      "title": "Kort tittel for forslaget",
      "description": "Utfyllende beskrivelse og hvorfor dette er viktig",
      "priority": "high|medium|low",
      "entity_type": "quote|invoice|work_order|project|activity",
      "entity_id": 123,
      "action_type": "follow_up|complete|review|send|call"
    }
  ],
  "quick_wins": [
    "Enkle oppgaver som kan fullføres raskt"
  ],
  "focus_areas": [
    {"area": "Område", "reason": "Hvorfor fokusere her"}
  ]
}

Viktige regler:
- Maksimalt 8 forslag, sortert etter prioritet (høyeste først)
- Fokuser på forfalne og tidskritiske oppgaver først
- Tilbud som har vært sendt lenge uten svar er viktige å følge opp
- Ubetalte fakturaer over forfall er kritiske
- Bruk norsk språk
- Vær konkret og spesifikk med referanser til dataene
- Priority score: 80-100=Mye å gjøre, 50-79=Normal, 0-49=Rolig
- Returner KUN JSON, ingen forklaringer eller markdown
PROMPT;
    }

    /**
     * Build the prompt with user data.
     */
    protected function buildPrompt(array $userData): string
    {
        $json = json_encode($userData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
Analyser følgende data om brukerens ventende arbeidsoppgaver og gi prioriterte forslag til oppfølging.

Brukerdata:
{$json}

Gi konkrete forslag basert på disse dataene, med fokus på hva som haster mest.
PROMPT;
    }

    /**
     * Parse AI response to array.
     *
     * @return array<string, mixed>
     */
    protected function parseResponse(string $response): array
    {
        $response = preg_replace('/^```json?\s*/m', '', $response);
        $response = preg_replace('/```\s*$/m', '', $response);
        $response = trim($response);

        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('Kunne ikke parse AI-respons som JSON', [
                'response' => $response,
                'error' => json_last_error_msg(),
            ]);

            throw new \RuntimeException('Kunne ikke tolke AI-respons: '.json_last_error_msg());
        }

        return $data;
    }
}
