<?php

namespace App\Services\Payroll;

use App\Models\AMeldingReport;
use App\Models\Company;
use App\Models\PayrollRun;

class AMeldingService
{
    /**
     * Generate an A-melding report for a payroll run.
     */
    public function generateReport(PayrollRun $run): AMeldingReport
    {
        $report = AMeldingReport::create([
            'company_id' => $run->company_id,
            'payroll_run_id' => $run->id,
            'year' => $run->year,
            'month' => $run->month,
            'melding_type' => AMeldingReport::TYPE_ORDINAER,
            'status' => AMeldingReport::STATUS_DRAFT,
            'created_by' => auth()->id(),
        ]);

        // Build the melding data
        $meldingData = $this->buildMeldingData($run);
        $report->melding_data = $meldingData;

        // Generate XML
        $report->xml_content = $this->generateXml($report);
        $report->status = AMeldingReport::STATUS_GENERATED;
        $report->save();

        return $report;
    }

    /**
     * Build the A-melding data structure.
     */
    public function buildMeldingData(PayrollRun $run): array
    {
        $company = Company::find($run->company_id);
        $entries = $run->entries()->with('user')->get();

        $inntektsmottakere = [];

        foreach ($entries as $entry) {
            $inntektsmottakere[] = [
                'norskIdentifikator' => $entry->user->fnr ?? null, // Would need to add fnr to user
                'navn' => $entry->user->name,
                'inntekt' => [
                    [
                        'beloep' => $entry->bruttolonn,
                        'inntektType' => 'loennsinntekt',
                        'fordel' => 'kontantytelse',
                        'beskrivelse' => 'fastloenn',
                    ],
                ],
                'fradrag' => [
                    [
                        'beloep' => $entry->forskuddstrekk,
                        'beskrivelse' => 'forskuddstrekk',
                    ],
                ],
                'arbeidsforhold' => [
                    'arbeidsforholdId' => $entry->user->aa_arbeidsforhold_id ?? null,
                    'typeArbeidsforhold' => 'ordinaertArbeidsforhold',
                    'arbeidstidsordning' => 'ikkeSkift',
                    'stillingsprosent' => 100,
                ],
            ];
        }

        return [
            'leveranse' => [
                'kildesystem' => 'Konrad Office',
                'opprettetDato' => now()->toDateString(),
                'periode' => [
                    'aar' => $run->year,
                    'maaned' => str_pad($run->month, 2, '0', STR_PAD_LEFT),
                ],
            ],
            'opplysningspliktig' => [
                'organisasjonsnummer' => $company->orgnr ?? null,
                'virksomhet' => [
                    'organisasjonsnummer' => $company->orgnr ?? null,
                ],
            ],
            'inntektsmottakere' => $inntektsmottakere,
            'arbeidsgiveravgift' => [
                'beregnetAvgift' => $run->total_arbeidsgiveravgift,
                'sone' => $run->aga_sone,
                'prosentsats' => $run->aga_sats,
            ],
        ];
    }

    /**
     * Generate XML for the A-melding.
     */
    public function generateXml(AMeldingReport $report): string
    {
        $data = $report->melding_data;

        // Create XML structure according to Skatteetaten's schema
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><melding xmlns="http://seres.no/xsd/NAV/A-melding_M" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"></melding>');

        // Add leveranse info
        $leveranse = $xml->addChild('leveranse');
        $leveranse->addChild('kildesystem', $data['leveranse']['kildesystem']);
        $leveranse->addChild('opprettetDato', $data['leveranse']['opprettetDato']);

        $periode = $leveranse->addChild('periode');
        $periode->addChild('aar', $data['leveranse']['periode']['aar']);
        $periode->addChild('maaned', $data['leveranse']['periode']['maaned']);

        // Add opplysningspliktig
        $opplysningspliktig = $xml->addChild('opplysningspliktig');
        $opplysningspliktig->addChild('organisasjonsnummer', $data['opplysningspliktig']['organisasjonsnummer']);

        // Add virksomhet
        $virksomhet = $opplysningspliktig->addChild('virksomhet');
        $virksomhet->addChild('organisasjonsnummer', $data['opplysningspliktig']['virksomhet']['organisasjonsnummer']);

        // Add inntektsmottakere
        foreach ($data['inntektsmottakere'] as $mottaker) {
            $im = $virksomhet->addChild('inntektsmottaker');

            if ($mottaker['norskIdentifikator']) {
                $im->addChild('norskIdentifikator', $mottaker['norskIdentifikator']);
            }

            // Add inntekt
            foreach ($mottaker['inntekt'] as $inntekt) {
                $i = $im->addChild('inntekt');
                $i->addChild('beloep', $inntekt['beloep']);
                $i->addChild('inntektType', $inntekt['inntektType']);
                $i->addChild('fordel', $inntekt['fordel']);
                $i->addChild('beskrivelse', $inntekt['beskrivelse']);
            }

            // Add fradrag
            foreach ($mottaker['fradrag'] as $fradrag) {
                $f = $im->addChild('fradrag');
                $f->addChild('beloep', $fradrag['beloep']);
                $f->addChild('beskrivelse', $fradrag['beskrivelse']);
            }

            // Add arbeidsforhold
            $af = $im->addChild('arbeidsforhold');
            if ($mottaker['arbeidsforhold']['arbeidsforholdId']) {
                $af->addChild('arbeidsforholdId', $mottaker['arbeidsforhold']['arbeidsforholdId']);
            }
            $af->addChild('typeArbeidsforhold', $mottaker['arbeidsforhold']['typeArbeidsforhold']);
        }

        // Add arbeidsgiveravgift
        $aga = $virksomhet->addChild('arbeidsgiveravgift');
        $aga->addChild('beregnetAvgift', $data['arbeidsgiveravgift']['beregnetAvgift']);
        $aga->addChild('sone', $data['arbeidsgiveravgift']['sone']);
        $aga->addChild('prosentsats', $data['arbeidsgiveravgift']['prosentsats']);

        return $xml->asXML();
    }

    /**
     * Validate the A-melding before submission.
     */
    public function validateMelding(AMeldingReport $report): array
    {
        $errors = [];
        $data = $report->melding_data;

        // Check required fields
        if (empty($data['opplysningspliktig']['organisasjonsnummer'])) {
            $errors[] = 'Organisasjonsnummer mangler';
        }

        foreach ($data['inntektsmottakere'] as $index => $mottaker) {
            if (empty($mottaker['norskIdentifikator'])) {
                $errors[] = "Inntektsmottaker #{$index}: Fodselsnummer mangler";
            }

            foreach ($mottaker['inntekt'] as $inntekt) {
                if ($inntekt['beloep'] < 0) {
                    $errors[] = "Inntektsmottaker #{$index}: Negativt inntektsbelop";
                }
            }
        }

        return $errors;
    }

    /**
     * Get the submission deadline for a period.
     */
    public function getDeadline(int $year, int $month): \Carbon\Carbon
    {
        // A-melding deadline is the 5th of the following month
        return \Carbon\Carbon::create($year, $month, 1)->addMonth()->setDay(5);
    }

    /**
     * Check if a period is past deadline.
     */
    public function isPastDeadline(int $year, int $month): bool
    {
        return now()->isAfter($this->getDeadline($year, $month));
    }
}
