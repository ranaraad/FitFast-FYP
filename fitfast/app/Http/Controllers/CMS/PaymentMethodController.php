<?php

namespace App\Http\Controllers\CMS;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentMethodController extends Controller
{
        public function index()
    {
        $paymentMethods = PaymentMethod::with(['user'])
            ->whereHas('user')
            ->latest()
            ->get();

        return view('cms.pages.payment-methods.index', compact('paymentMethods'));
    }

    public function show(PaymentMethod $paymentMethod)
    {
        // Only show masked information, never full details
        $paymentMethod->load(['user', 'payments.order']);
        return view('cms.pages.payment-methods.show', compact('paymentMethod'));
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        // Only allow deletion if no payments are associated
        if ($paymentMethod->payments()->exists()) {
            return redirect()->back()
                ->with('error', 'Cannot delete payment method associated with orders.');
        }

        $paymentMethod->delete();
        return redirect()->route('cms.payment-methods.index')
            ->with('success', 'Payment method deleted.');
    }
}
