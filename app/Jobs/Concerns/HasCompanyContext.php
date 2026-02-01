<?php

namespace App\Jobs\Concerns;

use App\Models\Company;
use InvalidArgumentException;

/**
 * Trait for queue jobs that need company context.
 *
 * Usage:
 * 1. Use this trait in your job class
 * 2. Pass company or company_id to the job constructor
 * 3. Call $this->setCompanyContext() at the start of handle()
 *
 * Example:
 *
 * class ProcessInvoice implements ShouldQueue
 * {
 *     use HasCompanyContext;
 *
 *     public function __construct(Invoice $invoice)
 *     {
 *         $this->setCompanyForJob($invoice->company_id);
 *     }
 *
 *     public function handle()
 *     {
 *         $this->setCompanyContext();
 *         // Now app('current.company') is available
 *     }
 * }
 */
trait HasCompanyContext
{
    /**
     * The company ID for this job.
     */
    public int $companyId;

    /**
     * Set the company for this job.
     */
    protected function setCompanyForJob(Company|int $company): void
    {
        $this->companyId = $company instanceof Company ? $company->id : $company;
    }

    /**
     * Set the company context for the current execution.
     * Call this at the beginning of handle().
     *
     * @throws InvalidArgumentException If company not found
     */
    protected function setCompanyContext(): Company
    {
        if (! isset($this->companyId)) {
            throw new InvalidArgumentException(
                'Company ID not set. Call setCompanyForJob() in the constructor.'
            );
        }

        $company = Company::find($this->companyId);

        if (! $company) {
            throw new InvalidArgumentException(
                "Company with ID {$this->companyId} not found."
            );
        }

        app()->instance('current.company', $company);

        return $company;
    }

    /**
     * Clear the company context after job execution.
     * Optional - call this in a finally block if needed.
     */
    protected function clearCompanyContext(): void
    {
        app()->instance('current.company', null);
    }

    /**
     * Execute a callback within a specific company context.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    protected function withinCompanyContext(callable $callback): mixed
    {
        $previousCompany = app('current.company');

        try {
            $this->setCompanyContext();

            return $callback();
        } finally {
            app()->instance('current.company', $previousCompany);
        }
    }
}
