<?php

namespace App\Jobs;

use App\Models\IncomingVoucher;
use App\Services\VoucherParserService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ParseVoucherJob implements ShouldQueue
{
    use Queueable;

    /**
     * Antall ganger jobben kan prøves på nytt.
     */
    public int $tries = 3;

    /**
     * Timeout i sekunder.
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public IncomingVoucher $voucher
    ) {}

    /**
     * Execute the job.
     */
    public function handle(VoucherParserService $service): void
    {
        Log::info('Starter parsing av bilag', [
            'voucher_id' => $this->voucher->id,
            'reference_number' => $this->voucher->reference_number,
        ]);

        try {
            $service->parse($this->voucher);

            Log::info('Bilag parset vellykket', [
                'voucher_id' => $this->voucher->id,
                'reference_number' => $this->voucher->reference_number,
            ]);
        } catch (\Exception $e) {
            Log::error('Feil ved parsing av bilag', [
                'voucher_id' => $this->voucher->id,
                'reference_number' => $this->voucher->reference_number,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Håndter en mislykket jobb.
     */
    public function failed(?\Throwable $exception): void
    {
        Log::error('Parsing av bilag feilet permanent', [
            'voucher_id' => $this->voucher->id,
            'reference_number' => $this->voucher->reference_number,
            'error' => $exception?->getMessage(),
        ]);

        $this->voucher->update([
            'status' => IncomingVoucher::STATUS_PENDING,
            'parsed_data' => [
                'error' => $exception?->getMessage() ?? 'Ukjent feil',
                'failed_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
