<?php

namespace Database\Factories;

use App\Models\EmployeePayrollSettings;
use App\Models\PayrollRun;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PayrollEntry>
 */
class PayrollEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bruttolonn = fake()->numberBetween(30000, 80000);
        $forskuddstrekk = round($bruttolonn * 0.30, 0);
        $feriepengerGrunnlag = $bruttolonn;
        $feriepengerAvsetning = round($feriepengerGrunnlag * 0.102, 2);
        $agaBasis = $bruttolonn + $feriepengerAvsetning;
        $aga = round($agaBasis * 0.141, 2);
        $otp = round($bruttolonn * 0.02, 2);

        return [
            'payroll_run_id' => PayrollRun::factory(),
            'user_id' => User::factory(),
            'timer_ordinaer' => 162.5,
            'timer_overtid_50' => 0,
            'timer_overtid_100' => 0,
            'grunnlonn' => $bruttolonn,
            'overtid_belop' => 0,
            'bonus' => 0,
            'tillegg' => 0,
            'bruttolonn' => $bruttolonn,
            'forskuddstrekk' => $forskuddstrekk,
            'fagforening' => 0,
            'andre_trekk' => 0,
            'nettolonn' => $bruttolonn - $forskuddstrekk,
            'feriepenger_grunnlag' => $feriepengerGrunnlag,
            'feriepenger_avsetning' => $feriepengerAvsetning,
            'arbeidsgiveravgift' => $aga,
            'otp_belop' => $otp,
            'skatt_type_brukt' => EmployeePayrollSettings::SKATT_TYPE_TABELLTREKK,
            'skatteprosent_brukt' => null,
        ];
    }

    public function withSalary(float $bruttolonn): static
    {
        $forskuddstrekk = round($bruttolonn * 0.30, 0);
        $feriepengerGrunnlag = $bruttolonn;
        $feriepengerAvsetning = round($feriepengerGrunnlag * 0.102, 2);
        $agaBasis = $bruttolonn + $feriepengerAvsetning;
        $aga = round($agaBasis * 0.141, 2);
        $otp = round($bruttolonn * 0.02, 2);

        return $this->state(fn (array $attributes) => [
            'grunnlonn' => $bruttolonn,
            'bruttolonn' => $bruttolonn,
            'forskuddstrekk' => $forskuddstrekk,
            'nettolonn' => $bruttolonn - $forskuddstrekk,
            'feriepenger_grunnlag' => $feriepengerGrunnlag,
            'feriepenger_avsetning' => $feriepengerAvsetning,
            'arbeidsgiveravgift' => $aga,
            'otp_belop' => $otp,
        ]);
    }

    public function withOvertime(float $overtid50Hours = 0, float $overtid100Hours = 0, float $hourlyRate = 300): static
    {
        $overtidBelop = ($overtid50Hours * $hourlyRate * 1.5) + ($overtid100Hours * $hourlyRate * 2.0);

        return $this->state(fn (array $attributes) => [
            'timer_overtid_50' => $overtid50Hours,
            'timer_overtid_100' => $overtid100Hours,
            'overtid_belop' => $overtidBelop,
            'bruttolonn' => $attributes['grunnlonn'] + $overtidBelop,
            'nettolonn' => ($attributes['grunnlonn'] + $overtidBelop) - $attributes['forskuddstrekk'],
        ]);
    }

    public function withBonus(float $amount): static
    {
        return $this->state(fn (array $attributes) => [
            'bonus' => $amount,
            'bruttolonn' => $attributes['grunnlonn'] + $attributes['overtid_belop'] + $amount,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function forRun(PayrollRun $run): static
    {
        return $this->state(fn (array $attributes) => [
            'payroll_run_id' => $run->id,
            'company_id' => $run->company_id,
        ]);
    }
}
