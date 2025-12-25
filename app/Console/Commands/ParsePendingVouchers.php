<?php

namespace App\Console\Commands;

use App\Models\IncomingVoucher;
use App\Services\VoucherParserService;
use Illuminate\Console\Command;

class ParsePendingVouchers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vouchers:parse
                            {--limit=10 : Maksimalt antall bilag som skal tolkes}
                            {--sync : Kjør synkront i stedet for å bruke køer}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tolk ventende bilag med AI';

    /**
     * Execute the console command.
     */
    public function handle(VoucherParserService $service): int
    {
        $limit = (int) $this->option('limit');
        $sync = $this->option('sync');

        $vouchers = IncomingVoucher::query()
            ->where('status', IncomingVoucher::STATUS_PENDING)
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        if ($vouchers->isEmpty()) {
            $this->info('Ingen ventende bilag å tolke.');

            return Command::SUCCESS;
        }

        $this->info("Fant {$vouchers->count()} bilag å tolke.");

        $bar = $this->output->createProgressBar($vouchers->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($vouchers as $voucher) {
            try {
                if ($sync) {
                    $service->parse($voucher);
                    $this->newLine();
                    $this->info("  ✓ Tolket {$voucher->reference_number}");
                } else {
                    dispatch(new \App\Jobs\ParseVoucherJob($voucher));
                    $this->newLine();
                    $this->info("  → Lagt i kø: {$voucher->reference_number}");
                }
                $success++;
            } catch (\Exception $e) {
                $failed++;
                $this->newLine();
                $this->error("  ✗ Feil ved {$voucher->reference_number}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($sync) {
            $this->info("Ferdig! {$success} tolket, {$failed} feilet.");
        } else {
            $this->info("Ferdig! {$success} lagt i kø, {$failed} feilet.");
        }

        return $failed > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
