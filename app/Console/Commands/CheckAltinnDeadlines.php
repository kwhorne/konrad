<?php

namespace App\Console\Commands;

use App\Models\AnnualAccount;
use App\Models\ShareholderReport;
use App\Models\TaxReturn;
use App\Models\User;
use App\Notifications\AltinnDeadlineReminder;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckAltinnDeadlines extends Command
{
    protected $signature = 'altinn:check-deadlines {--notify : Send notifications to users}';

    protected $description = 'Sjekk Altinn-frister og send påminnelser';

    // Dager før frist for å sende påminnelser
    private const REMINDER_DAYS = [30, 14, 7, 1];

    public function handle(): int
    {
        $this->info('Sjekker Altinn-frister...');

        $year = now()->year;
        $deadlines = $this->getDeadlines($year);

        $upcomingCount = 0;
        $overdueCount = 0;

        foreach ($deadlines as $deadline) {
            $daysUntil = now()->startOfDay()->diffInDays($deadline['deadline']->startOfDay(), false);

            if ($deadline['status'] !== 'accepted') {
                if ($daysUntil < 0) {
                    $this->error("FORFALT: {$deadline['name']} ({$deadline['code']}) - {$deadline['deadline']->format('d.m.Y')} - ".abs($daysUntil).' dager siden');
                    $overdueCount++;
                } elseif ($daysUntil <= 30) {
                    $urgency = $this->getUrgencyText($daysUntil);
                    $this->warn("{$urgency}: {$deadline['name']} ({$deadline['code']}) - {$deadline['deadline']->format('d.m.Y')} - {$daysUntil} dager igjen");
                    $upcomingCount++;

                    // Send notification if requested and day matches
                    if ($this->option('notify') && in_array($daysUntil, self::REMINDER_DAYS)) {
                        $this->sendReminders($deadline, $daysUntil);
                    }
                } else {
                    $this->line("OK: {$deadline['name']} ({$deadline['code']}) - {$deadline['deadline']->format('d.m.Y')} - {$daysUntil} dager igjen");
                }
            } else {
                $this->info("LEVERT: {$deadline['name']} ({$deadline['code']})");
            }
        }

        $this->newLine();
        $this->info("Oppsummering: {$upcomingCount} kommende frister, {$overdueCount} forfalte");

        return self::SUCCESS;
    }

    private function getDeadlines(int $year): array
    {
        $deadlines = [];

        // Aksjonærregisteroppgaven (RF-1086) - 31. januar
        $shareholderReport = ShareholderReport::where('fiscal_year', $year - 1)->first();
        $deadlines[] = [
            'type' => 'aksjonaerregister',
            'name' => 'Aksjonærregisteroppgaven',
            'code' => 'RF-1086',
            'fiscal_year' => $year - 1,
            'deadline' => Carbon::create($year, 1, 31),
            'recipient' => 'Skatteetaten',
            'status' => $shareholderReport?->status ?? 'not_started',
            'entity' => $shareholderReport,
        ];

        // Skattemelding (RF-1028) - 31. mai
        $taxReturn = TaxReturn::where('fiscal_year', $year - 1)->first();
        $deadlines[] = [
            'type' => 'skattemelding',
            'name' => 'Skattemelding',
            'code' => 'RF-1028',
            'fiscal_year' => $year - 1,
            'deadline' => Carbon::create($year, 5, 31),
            'recipient' => 'Skatteetaten',
            'status' => $taxReturn?->status ?? 'not_started',
            'entity' => $taxReturn,
        ];

        // Årsregnskap - 31. juli
        $annualAccount = AnnualAccount::where('fiscal_year', $year - 1)->first();
        $deadlines[] = [
            'type' => 'arsregnskap',
            'name' => 'Årsregnskap',
            'code' => 'XBRL',
            'fiscal_year' => $year - 1,
            'deadline' => Carbon::create($year, 7, 31),
            'recipient' => 'Regnskapsregisteret',
            'status' => $annualAccount?->status ?? 'not_started',
            'entity' => $annualAccount,
        ];

        return $deadlines;
    }

    private function getUrgencyText(int $daysUntil): string
    {
        if ($daysUntil <= 1) {
            return 'KRITISK';
        }
        if ($daysUntil <= 7) {
            return 'HASTER';
        }
        if ($daysUntil <= 14) {
            return 'VIKTIG';
        }

        return 'SNART';
    }

    private function sendReminders(array $deadline, int $daysUntil): void
    {
        // Get users who should receive reminders (admins or users with notification preference)
        $users = User::where('is_admin', true)->get();

        foreach ($users as $user) {
            $user->notify(new AltinnDeadlineReminder($deadline, $daysUntil));
            $this->info("  → Sendte påminnelse til {$user->email}");
        }
    }
}
