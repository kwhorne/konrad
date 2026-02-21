<?php

namespace App\Livewire\Admin;

use Flux\Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SystemManager extends Component
{
    use AuthorizesRequests;

    public bool $showLogs = false;

    public string $logContent = '';

    public function clearCache(): void
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');

        Flux::toast(text: 'Cache tømt', variant: 'success');
    }

    public function loadLogs(): void
    {
        $logPath = storage_path('logs/laravel.log');

        if (! file_exists($logPath)) {
            $this->logContent = 'Ingen loggfil funnet.';
            $this->showLogs = true;

            return;
        }

        $file = new \SplFileObject($logPath, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();

        $startLine = max(0, $totalLines - 50);
        $lines = [];
        $file->seek($startLine);

        while (! $file->eof()) {
            $lines[] = $file->current();
            $file->next();
        }

        $this->logContent = implode('', $lines);
        $this->showLogs = true;
    }

    public function hideLogs(): void
    {
        $this->showLogs = false;
        $this->logContent = '';
    }

    public function render(): \Illuminate\View\View
    {
        $pendingJobs = 0;
        $failedJobs = 0;

        try {
            $pendingJobs = DB::table('jobs')->count();
        } catch (\Throwable) {
            // Table may not exist
        }

        try {
            $failedJobs = DB::table('failed_jobs')->count();
        } catch (\Throwable) {
            // Table may not exist
        }

        return view('livewire.admin.system-manager', compact('pendingJobs', 'failedJobs'));
    }
}
