<?php

use App\Livewire\CustomerLedger;
use App\Livewire\SupplierLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $context = createTestCompanyContext();
    $this->user = $context['user'];
    $this->company = $context['company'];
});

describe('CustomerLedger sort column whitelist', function () {
    test('accepts valid sort columns', function (string $column) {
        $this->actingAs($this->user);

        Livewire::test(CustomerLedger::class)
            ->call('sort', $column)
            ->assertSet('sortBy', $column);
    })->with([
        'invoice_number',
        'invoice_date',
        'due_date',
        'total',
        'balance',
    ]);

    test('rejects invalid sort column', function () {
        $this->actingAs($this->user);

        Livewire::test(CustomerLedger::class)
            ->call('sort', 'malicious_column; DROP TABLE invoices')
            ->assertSet('sortBy', 'due_date');
    });

    test('rejects arbitrary column name', function () {
        $this->actingAs($this->user);

        Livewire::test(CustomerLedger::class)
            ->call('sort', 'password')
            ->assertSet('sortBy', 'due_date');
    });
});

describe('SupplierLedger sort column whitelist', function () {
    test('accepts valid sort columns', function (string $column) {
        $this->actingAs($this->user);

        Livewire::test(SupplierLedger::class)
            ->call('sort', $column)
            ->assertSet('sortBy', $column);
    })->with([
        'internal_number',
        'invoice_date',
        'due_date',
        'total',
        'balance',
    ]);

    test('rejects invalid sort column', function () {
        $this->actingAs($this->user);

        Livewire::test(SupplierLedger::class)
            ->call('sort', 'malicious_column')
            ->assertSet('sortBy', 'due_date');
    });

    test('rejects arbitrary column name', function () {
        $this->actingAs($this->user);

        Livewire::test(SupplierLedger::class)
            ->call('sort', 'password')
            ->assertSet('sortBy', 'due_date');
    });
});
