<?php

namespace App\Http\Controllers;

use App\Models\VatReport;

class VatReportController extends Controller
{
    public function index()
    {
        return view('vat-reports.index');
    }

    public function show(VatReport $vatReport)
    {
        $vatReport->load(['lines.vatCode', 'attachments', 'creator', 'submitter']);

        return view('vat-reports.show', compact('vatReport'));
    }
}
