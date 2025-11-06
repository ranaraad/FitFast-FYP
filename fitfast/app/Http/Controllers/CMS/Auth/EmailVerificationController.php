<?php

namespace App\Http\Controllers\CMS\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    /**
     * Show the email verification notice.
     */
    public function notice(Request $request)
    {
        return $request->user()->hasVerifiedEmail()
            ? $this->redirectBasedOnRole($request->user())
            : view('cms.pages.auth.verify-email');
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectBasedOnRole($request->user());
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new \Illuminate\Auth\Events\Verified($request->user()));
        }

        return $this->redirectBasedOnRole($request->user())->with('success', 'Email verified successfully!');
    }

    /**
     * Resend the email verification notification.
     */
    public function send(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->redirectBasedOnRole($request->user());
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Verification link sent!');
    }

    /**
     * Redirect user based on their role.
     */
    private function redirectBasedOnRole($user)
    {
        if ($user->role->name === 'Admin') {
            return redirect()->route('cms.dashboard');
        } elseif ($user->role->name === 'Store Admin') {
            return redirect()->route('store-admin.dashboard');
        }

        // Fallback
        return redirect()->route('cms.dashboard');
    }
}
