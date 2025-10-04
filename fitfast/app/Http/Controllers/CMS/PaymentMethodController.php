<?php

namespace App\Http\Controllers\CMS;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::with('user')
            ->latest()
            ->paginate(10);

        return view('cms.payment-methods.index', compact('paymentMethods'));
    }

    public function create()
    {
        $users = User::all();
        return view('cms.payment-methods.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:credit_card,debit_card,paypal,bank_transfer,cash_on_delivery',
            'details' => 'required|array',
            'details.card_number' => 'required_if:type,credit_card,debit_card|string|size:16',
            'details.expiry_month' => 'required_if:type,credit_card,debit_card|integer|min:1|max:12',
            'details.expiry_year' => 'required_if:type,credit_card,debit_card|integer|min:' . date('Y') . '|max:' . (date('Y') + 10),
            'details.cvv' => 'required_if:type,credit_card,debit_card|string|size:3',
            'details.card_holder' => 'required_if:type,credit_card,debit_card|string|max:255',
            'details.paypal_email' => 'required_if:type,paypal|email',
            'details.bank_account' => 'required_if:type,bank_transfer|string',
            'is_default' => 'boolean',
        ]);

        // If this is set as default, remove default from other payment methods
        if ($request->has('is_default') && $request->is_default) {
            PaymentMethod::where('user_id', $validated['user_id'])
                ->update(['is_default' => false]);
        }

        PaymentMethod::create($validated);

        return redirect()->route('cms.payment-methods.index')
            ->with('success', 'Payment method created successfully.');
    }

    public function show(PaymentMethod $paymentMethod)
    {
        $paymentMethod->load(['user', 'payments.order']);
        return view('cms.payment-methods.show', compact('paymentMethod'));
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        $paymentMethod->load('user');
        $users = User::all();
        return view('cms.payment-methods.edit', compact('paymentMethod', 'users'));
    }

    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:credit_card,debit_card,paypal,bank_transfer,cash_on_delivery',
            'details' => 'required|array',
            'details.card_number' => 'required_if:type,credit_card,debit_card|string|size:16',
            'details.expiry_month' => 'required_if:type,credit_card,debit_card|integer|min:1|max:12',
            'details.expiry_year' => 'required_if:type,credit_card,debit_card|integer|min:' . date('Y') . '|max:' . (date('Y') + 10),
            'details.cvv' => 'required_if:type,credit_card,debit_card|string|size:3',
            'details.card_holder' => 'required_if:type,credit_card,debit_card|string|max:255',
            'details.paypal_email' => 'required_if:type,paypal|email',
            'details.bank_account' => 'required_if:type,bank_transfer|string',
            'is_default' => 'boolean',
        ]);

        // If this is set as default, remove default from other payment methods
        if ($request->has('is_default') && $request->is_default) {
            PaymentMethod::where('user_id', $validated['user_id'])
                ->where('id', '!=', $paymentMethod->id)
                ->update(['is_default' => false]);
        }

        $paymentMethod->update($validated);

        return redirect()->route('cms.payment-methods.index')
            ->with('success', 'Payment method updated successfully.');
    }

    public function destroy(PaymentMethod $paymentMethod)
    {
        // Check if this is the default payment method
        $wasDefault = $paymentMethod->is_default;
        $userId = $paymentMethod->user_id;

        $paymentMethod->delete();

        // If it was the default, set a new default if available
        if ($wasDefault) {
            $newDefault = PaymentMethod::where('user_id', $userId)->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return redirect()->route('cms.payment-methods.index')
            ->with('success', 'Payment method deleted successfully.');
    }

    /**
     * Set payment method as default
     */
    public function setAsDefault(PaymentMethod $paymentMethod)
    {
        $paymentMethod->setAsDefault();

        return redirect()->back()
            ->with('success', 'Payment method set as default.');
    }

    /**
     * Get payment methods by user
     */
    public function byUser(User $user)
    {
        $paymentMethods = $user->paymentMethods()
            ->latest()
            ->paginate(10);

        return view('cms.payment-methods.index', compact('paymentMethods', 'user'));
    }

    /**
     * Get payment methods by type
     */
    public function byType($type)
    {
        $validTypes = ['credit_card', 'debit_card', 'paypal', 'bank_transfer', 'cash_on_delivery'];

        if (!in_array($type, $validTypes)) {
            return redirect()->route('cms.payment-methods.index')
                ->with('error', 'Invalid payment method type.');
        }

        $paymentMethods = PaymentMethod::with('user')
            ->where('type', $type)
            ->latest()
            ->paginate(10);

        return view('cms.payment-methods.index', compact('paymentMethods', 'type'));
    }
}
