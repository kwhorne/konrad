<?php

use App\Models\Account;
use App\Models\Company;
use App\Models\Contact;
use App\Models\IncomingVoucher;
use App\Models\User;
use App\Services\IncomingVoucherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function setupVoucherContext(): array
{
    $user = User::factory()->create(['onboarding_completed' => true]);
    $company = Company::factory()->withOwner($user)->create();
    $user->update(['current_company_id' => $company->id]);
    app()->instance('current.company', $company);

    return ['user' => $user->fresh(), 'company' => $company];
}

beforeEach(function () {
    ['user' => $this->user, 'company' => $this->company] = setupVoucherContext();
    $this->actingAs($this->user);
    $this->service = app(IncomingVoucherService::class);
    Storage::fake('local');
    Queue::fake();
});

it('uploads files and creates incoming vouchers', function () {
    $files = [
        UploadedFile::fake()->create('invoice1.pdf', 100, 'application/pdf'),
        UploadedFile::fake()->create('invoice2.pdf', 150, 'application/pdf'),
    ];

    $vouchers = $this->service->uploadFiles($files);

    expect($vouchers)->toHaveCount(2);
    expect($vouchers[0])->toBeInstanceOf(IncomingVoucher::class);
    expect($vouchers[0]->original_filename)->toBe('invoice1.pdf');
    expect($vouchers[0]->status)->toBe(IncomingVoucher::STATUS_PENDING);
    expect($vouchers[0]->source)->toBe(IncomingVoucher::SOURCE_UPLOAD);
    expect($vouchers[0]->created_by)->toBe($this->user->id);
});

it('updates suggestions on incoming voucher', function () {
    $voucher = IncomingVoucher::factory()->parsed()->create();
    $newSupplier = Contact::factory()->supplier()->create();
    $newAccount = Account::factory()->expense()->create();

    $data = [
        'supplier_id' => $newSupplier->id,
        'invoice_number' => 'NEW-123',
        'invoice_date' => '2026-01-15',
        'due_date' => '2026-02-15',
        'total' => 5000,
        'vat_total' => 1000,
        'account_id' => $newAccount->id,
    ];

    $updated = $this->service->updateSuggestions($voucher, $data);

    expect($updated->suggested_supplier_id)->toBe($newSupplier->id);
    expect($updated->suggested_invoice_number)->toBe('NEW-123');
    expect((float) $updated->suggested_total)->toBe(5000.0);
    expect($updated->suggested_account_id)->toBe($newAccount->id);
});

it('attests a parsed voucher', function () {
    $voucher = IncomingVoucher::factory()->parsed()->create();

    $data = [
        'supplier_id' => $voucher->suggested_supplier_id,
        'invoice_number' => 'ATT-123',
        'invoice_date' => '2026-01-15',
        'total' => 3000,
        'account_id' => $voucher->suggested_account_id,
    ];

    $result = $this->service->attest($voucher, $data);

    expect($result)->toBeTrue();
    expect($voucher->fresh()->status)->toBe(IncomingVoucher::STATUS_ATTESTED);
    expect($voucher->fresh()->attested_by)->toBe($this->user->id);
});

it('cannot attest non-parsed voucher', function () {
    $voucher = IncomingVoucher::factory()->pending()->create();

    $result = $this->service->attest($voucher, []);

    expect($result)->toBeFalse();
    expect($voucher->fresh()->status)->toBe(IncomingVoucher::STATUS_PENDING);
});

it('rejects a voucher with reason', function () {
    $voucher = IncomingVoucher::factory()->parsed()->create();

    $result = $this->service->reject($voucher, 'Invalid invoice');

    expect($result)->toBeTrue();
    expect($voucher->fresh()->status)->toBe(IncomingVoucher::STATUS_REJECTED);
    expect($voucher->fresh()->rejection_reason)->toBe('Invalid invoice');
    expect($voucher->fresh()->rejected_by)->toBe($this->user->id);
});

it('cannot reject approved voucher', function () {
    $voucher = IncomingVoucher::factory()->approved()->create();

    $result = $this->service->reject($voucher, 'Too late');

    expect($result)->toBeFalse();
});

it('re-parses a voucher', function () {
    $voucher = IncomingVoucher::factory()->parsed()->create();

    $result = $this->service->reParse($voucher);

    expect($result)->toBeTrue();
    expect($voucher->fresh()->status)->toBe(IncomingVoucher::STATUS_PENDING);
});

it('cannot re-parse while parsing', function () {
    $voucher = IncomingVoucher::factory()->parsing()->create();

    $result = $this->service->reParse($voucher);

    expect($result)->toBeFalse();
});

it('deletes a pending voucher', function () {
    $voucher = IncomingVoucher::factory()->pending()->create();
    $id = $voucher->id;

    $result = $this->service->delete($voucher);

    expect($result)->toBeTrue();
    expect(IncomingVoucher::find($id))->toBeNull();
});

it('cannot delete approved voucher', function () {
    $voucher = IncomingVoucher::factory()->approved()->create();

    $result = $this->service->delete($voucher);

    expect($result)->toBeFalse();
    expect(IncomingVoucher::find($voucher->id))->not->toBeNull();
});

it('cannot delete posted voucher', function () {
    $voucher = IncomingVoucher::factory()->posted()->create();

    $result = $this->service->delete($voucher);

    expect($result)->toBeFalse();
});

it('gets status counts', function () {
    IncomingVoucher::factory()->pending()->count(3)->create();
    IncomingVoucher::factory()->parsed()->count(2)->create();
    IncomingVoucher::factory()->attested()->count(1)->create();
    IncomingVoucher::factory()->rejected()->count(1)->create();

    $counts = $this->service->getStatusCounts();

    expect($counts['pending'])->toBe(3);
    expect($counts['parsed'])->toBe(2);
    expect($counts['attested'])->toBe(1);
    expect($counts['rejected'])->toBe(1);
    expect($counts['approved'])->toBe(0);
});

it('checks if voucher can be attested', function () {
    $parsed = IncomingVoucher::factory()->parsed()->create();
    $pending = IncomingVoucher::factory()->pending()->create();
    $attested = IncomingVoucher::factory()->attested()->create();

    expect($this->service->canAttest($parsed))->toBeTrue();
    expect($this->service->canAttest($pending))->toBeFalse();
    expect($this->service->canAttest($attested))->toBeFalse();
});

it('checks if voucher can be approved', function () {
    $attested = IncomingVoucher::factory()->attested()->create();
    $parsed = IncomingVoucher::factory()->parsed()->create();

    expect($this->service->canApprove($attested))->toBeTrue();
    expect($this->service->canApprove($parsed))->toBeFalse();
});

it('checks if voucher can be rejected', function () {
    $parsed = IncomingVoucher::factory()->parsed()->create();
    $attested = IncomingVoucher::factory()->attested()->create();
    $approved = IncomingVoucher::factory()->approved()->create();

    expect($this->service->canReject($parsed))->toBeTrue();
    expect($this->service->canReject($attested))->toBeTrue();
    expect($this->service->canReject($approved))->toBeFalse();
});

it('checks if voucher can be deleted', function () {
    $pending = IncomingVoucher::factory()->pending()->create();
    $parsed = IncomingVoucher::factory()->parsed()->create();
    $approved = IncomingVoucher::factory()->approved()->create();
    $posted = IncomingVoucher::factory()->posted()->create();

    expect($this->service->canDelete($pending))->toBeTrue();
    expect($this->service->canDelete($parsed))->toBeTrue();
    expect($this->service->canDelete($approved))->toBeFalse();
    expect($this->service->canDelete($posted))->toBeFalse();
});

it('checks if voucher can be re-parsed', function () {
    $pending = IncomingVoucher::factory()->pending()->create();
    $parsing = IncomingVoucher::factory()->parsing()->create();
    $parsed = IncomingVoucher::factory()->parsed()->create();

    expect($this->service->canReParse($pending))->toBeTrue();
    expect($this->service->canReParse($parsing))->toBeFalse();
    expect($this->service->canReParse($parsed))->toBeTrue();
});
