<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    // GET /invoices
    public function index(Request $request)
    {
        $invoices = Invoice::with(['booking.customer:id,name', 'createdBy:id,name'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    // GET /invoices/:id
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'booking.customer',
            'booking.room.roomType',
            'booking.bookingServices.service',
            'booking.payments',
            'createdBy:id,name',
        ]);

        return view('invoices.show', compact('invoice'));
    }

    // GET /invoices/:id/print  — printer-friendly / PDF-ready view
    public function print(Invoice $invoice)
    {
        $invoice->load([
            'booking.customer',
            'booking.room.roomType',
            'booking.bookingServices.service',
            'booking.payments',
            'createdBy:id,name',
        ]);

        return view('invoices.print', compact('invoice'));
    }

    // GET /invoices/:id/pdf  — download as PDF via barryvdh/laravel-dompdf
    public function pdf(Invoice $invoice)
    {
        $invoice->load([
            'booking.customer',
            'booking.room.roomType',
            'booking.bookingServices.service',
            'booking.payments',
        ]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.print', ['invoice' => $invoice, 'isPdf' => true])
            ->setPaper('a4', 'portrait');

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    // POST /invoices/:id/issue  [admin only]
    public function issue(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be issued.');
        }

        $invoice->update(['status' => 'issued', 'issued_at' => now()]);

        return back()->with('success', "Invoice {$invoice->invoice_number} issued.");
    }

    // POST /invoices/:id/void  [admin only]
    public function void(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Cannot void a paid invoice.');
        }

        $invoice->update(['status' => 'void']);

        return back()->with('success', "Invoice {$invoice->invoice_number} voided.");
    }

    // GET /invoices/:id/discount  [admin only]
    public function showDiscount(Invoice $invoice)
    {
        return view('invoices.discount', compact('invoice'));
    }

    // PUT /invoices/:id/discount  [admin only]
    public function applyDiscount(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'discount_rate'   => ['required', 'numeric', 'min:0', 'max:100'],
            'discount_reason' => ['required', 'string'],
        ]);

        $discountAmount  = round($invoice->subtotal * ($data['discount_rate'] / 100), 2);
        $discountedTotal = (float) $invoice->subtotal - $discountAmount;
        $taxAmount       = round($discountedTotal * ($invoice->tax_rate / 100), 2);
        $grandTotal      = $discountedTotal + $taxAmount;

        // Deposit from payments table — single source of truth
        $invoice->booking->loadMissing('payments');
        $deposit = (float) $invoice->booking->payments
                       ->where('status', 'paid')
                       ->where('payment_type', 'deposit')
                       ->sum('amount');

        $invoice->update([
            'discount_rate'    => $data['discount_rate'],
            'discount_amount'  => $discountAmount,
            'discount_reason'  => $data['discount_reason'],
            'discounted_total' => $discountedTotal,
            'tax_amount'       => $taxAmount,
            'grand_total'      => $grandTotal,
            'settlement_amount'=> max(0, $grandTotal - $deposit),
        ]);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', "Discount of {$data['discount_rate']}% applied.");
    }
}
