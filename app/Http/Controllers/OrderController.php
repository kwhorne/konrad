<?php

namespace App\Http\Controllers;

use App\Mail\DocumentMail;
use App\Models\Order;
use App\Services\DocumentPdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function index()
    {
        return view('orders.index');
    }

    public function create()
    {
        // Return view directly to preserve query parameters (contact_id)
        return view('orders.index');
    }

    public function show()
    {
        return redirect()->route('orders.index');
    }

    public function edit()
    {
        return redirect()->route('orders.index');
    }

    public function pdf(Order $order, DocumentPdfService $pdfService): Response
    {
        $pdf = $pdfService->generateOrderPdf($order);
        $filename = 'Ordre-'.$order->order_number.'.pdf';

        return $pdf->download($filename);
    }

    public function preview(Order $order, DocumentPdfService $pdfService): Response
    {
        $pdf = $pdfService->generateOrderPdf($order);

        return $pdf->stream();
    }

    public function send(Order $order): RedirectResponse
    {
        $email = $order->contact?->email;

        if (! $email) {
            return redirect()->route('orders.index')
                ->with('error', 'Kontakten har ingen e-postadresse.');
        }

        Mail::to($email)->send(new DocumentMail($order));

        $order->update(['sent_at' => now()]);

        // Update status to 'confirmed' if currently draft
        $confirmedStatus = \App\Models\OrderStatus::where('code', 'confirmed')->first();
        if ($confirmedStatus && $order->orderStatus?->code === 'draft') {
            $order->update(['order_status_id' => $confirmedStatus->id]);
        }

        return redirect()->route('orders.index')
            ->with('success', 'Ordren ble sendt til '.$email);
    }
}
