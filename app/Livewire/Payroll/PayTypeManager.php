<?php

namespace App\Livewire\Payroll;

use App\Models\PayType;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;

class PayTypeManager extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public bool $isEditing = false;

    public ?int $editingId = null;

    // Form fields
    public string $code = '';

    public string $name = '';

    public string $category = 'fastlonn';

    public bool $isTaxable = true;

    public bool $isAgaBasis = true;

    public bool $isVacationBasis = true;

    public bool $isOtpBasis = true;

    public ?float $defaultRate = null;

    public ?float $overtidFaktor = null;

    public ?string $aMeldingCode = null;

    public bool $isActive = true;

    public int $sortOrder = 0;

    protected function rules(): array
    {
        return [
            'code' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'category' => 'required|in:fastlonn,timelonn,overtid,bonus,tillegg,trekk,naturalytelse,utgiftsgodtgjorelse',
            'isTaxable' => 'boolean',
            'isAgaBasis' => 'boolean',
            'isVacationBasis' => 'boolean',
            'isOtpBasis' => 'boolean',
            'defaultRate' => 'nullable|numeric|min:0',
            'overtidFaktor' => 'nullable|numeric|min:1|max:3',
            'aMeldingCode' => 'nullable|string|max:50',
            'isActive' => 'boolean',
            'sortOrder' => 'integer|min:0',
        ];
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->reset([
            'editingId', 'code', 'name', 'category', 'isTaxable', 'isAgaBasis',
            'isVacationBasis', 'isOtpBasis', 'defaultRate', 'overtidFaktor',
            'aMeldingCode', 'isActive', 'sortOrder',
        ]);
        $this->category = 'fastlonn';
        $this->isTaxable = true;
        $this->isAgaBasis = true;
        $this->isVacationBasis = true;
        $this->isOtpBasis = true;
        $this->isActive = true;
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $payType = PayType::findOrFail($id);

        $this->editingId = $id;
        $this->code = $payType->code;
        $this->name = $payType->name;
        $this->category = $payType->category;
        $this->isTaxable = $payType->is_taxable;
        $this->isAgaBasis = $payType->is_aga_basis;
        $this->isVacationBasis = $payType->is_vacation_basis;
        $this->isOtpBasis = $payType->is_otp_basis;
        $this->defaultRate = $payType->default_rate ? (float) $payType->default_rate : null;
        $this->overtidFaktor = $payType->overtid_faktor ? (float) $payType->overtid_faktor : null;
        $this->aMeldingCode = $payType->a_melding_code;
        $this->isActive = $payType->is_active;
        $this->sortOrder = $payType->sort_order;

        $this->isEditing = true;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->authorize('create', PayType::class);
        $this->validate();

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'category' => $this->category,
            'is_taxable' => $this->isTaxable,
            'is_aga_basis' => $this->isAgaBasis,
            'is_vacation_basis' => $this->isVacationBasis,
            'is_otp_basis' => $this->isOtpBasis,
            'default_rate' => $this->defaultRate,
            'overtid_faktor' => $this->overtidFaktor,
            'a_melding_code' => $this->aMeldingCode,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];

        if ($this->isEditing) {
            $payType = PayType::findOrFail($this->editingId);
            $payType->update($data);
            session()->flash('success', 'Lønnsart oppdatert.');
        } else {
            PayType::create($data);
            session()->flash('success', 'Lønnsart opprettet.');
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $payType = PayType::findOrFail($id);
        $this->authorize('delete', $payType);
        $payType->delete();
        session()->flash('success', 'Lønnsart slettet.');
    }

    public function seedDefaultPayTypes(): void
    {
        $this->authorize('create', PayType::class);

        $company = app('current.company');

        $defaults = [
            ['code' => '100', 'name' => 'Fastlonn', 'category' => 'fastlonn', 'sort_order' => 1],
            ['code' => '110', 'name' => 'Timelonn', 'category' => 'timelonn', 'sort_order' => 2],
            ['code' => '200', 'name' => 'Overtid 50%', 'category' => 'overtid', 'overtid_faktor' => 1.5, 'sort_order' => 3],
            ['code' => '210', 'name' => 'Overtid 100%', 'category' => 'overtid', 'overtid_faktor' => 2.0, 'sort_order' => 4],
            ['code' => '300', 'name' => 'Bonus', 'category' => 'bonus', 'sort_order' => 5],
            ['code' => '400', 'name' => 'Kveldstillegg', 'category' => 'tillegg', 'sort_order' => 6],
            ['code' => '410', 'name' => 'Helgetillegg', 'category' => 'tillegg', 'sort_order' => 7],
            ['code' => '500', 'name' => 'Fagforeningstrekk', 'category' => 'trekk', 'is_taxable' => false, 'is_aga_basis' => false, 'is_vacation_basis' => false, 'is_otp_basis' => false, 'sort_order' => 8],
            ['code' => '600', 'name' => 'Firmabil', 'category' => 'naturalytelse', 'sort_order' => 9],
            ['code' => '700', 'name' => 'Diett', 'category' => 'utgiftsgodtgjorelse', 'is_taxable' => false, 'is_aga_basis' => false, 'is_vacation_basis' => false, 'is_otp_basis' => false, 'sort_order' => 10],
            ['code' => '710', 'name' => 'Kilometergodtgjorelse', 'category' => 'utgiftsgodtgjorelse', 'is_taxable' => false, 'is_aga_basis' => false, 'is_vacation_basis' => false, 'is_otp_basis' => false, 'sort_order' => 11],
        ];

        foreach ($defaults as $default) {
            PayType::firstOrCreate(
                ['company_id' => $company->id, 'code' => $default['code']],
                array_merge([
                    'is_taxable' => true,
                    'is_aga_basis' => true,
                    'is_vacation_basis' => true,
                    'is_otp_basis' => true,
                    'is_active' => true,
                ], $default)
            );
        }

        session()->flash('success', 'Standard lønnsarter opprettet.');
    }

    public function render()
    {
        $company = app('current.company');

        $payTypes = PayType::where('company_id', $company->id)
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('code', 'like', '%'.$this->search.'%');
            })
            ->ordered()
            ->paginate(20);

        $categories = [
            'fastlonn' => 'Fastlonn',
            'timelonn' => 'Timelonn',
            'overtid' => 'Overtid',
            'bonus' => 'Bonus/provisjon',
            'tillegg' => 'Tillegg',
            'trekk' => 'Trekk',
            'naturalytelse' => 'Naturalytelse',
            'utgiftsgodtgjorelse' => 'Utgiftsgodtgjorelse',
        ];

        return view('livewire.payroll.pay-type-manager', [
            'payTypes' => $payTypes,
            'categories' => $categories,
        ]);
    }
}
