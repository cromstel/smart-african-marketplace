<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeEmail;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorCategory;
use App\Providers\AppServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $vendorCategories = VendorCategory::active()->orderBy('sort_order')->get();

        return view('auth.register', compact('vendorCategories'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $rules = [
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        if ($request->filled('register_as_vendor')) {
            $rules['business_name'] = ['required', 'string', 'max:255'];
            $rules['business_email'] = ['required', 'email', 'max:255'];
            $rules['business_phone'] = ['required', 'string', 'max:20'];
            $rules['business_address'] = ['required', 'string', 'max:500'];
            $rules['vendor_category_id'] = ['required', 'exists:vendor_categories,id'];
        }

        $request->validate($rules);

        DB::beginTransaction();

        try {
            $user = User::create([
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'name' => $request->firstname.' '.$request->lastname,
                'email' => $request->email,
                'password' => $request->password,
                'role' => $request->filled('register_as_vendor') ? 'vendor' : 'customer',
            ]);

            if ($request->filled('register_as_vendor')) {
                Vendor::create([
                    'user_id' => $user->id,
                    'vendor_category_id' => $request->vendor_category_id,
                    'business_name' => $request->business_name,
                    'slug' => Str::slug($request->business_name).'-'.Str::random(6),
                    'business_email' => $request->business_email,
                    'business_phone' => $request->business_phone,
                    'business_address' => $request->business_address,
                    'status' => 'pending',
                    'is_active' => false,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        // Post-registration side effects (email verification notice, welcome
        // email) are best-effort: the account and (optional) vendor record
        // are already committed above, so a mail/notification failure here
        // (e.g. a misconfigured or temporarily unreachable mail transport)
        // must never prevent the user from being logged in and redirected.
        // Previously these calls sat inside the same try/catch as the DB
        // writes, so an exception here was caught, ran a no-op DB::rollBack()
        // (the transaction was already committed) and then rethrew --
        // surfacing as a 500 response and silently skipping Auth::login().
        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch Registered event / send email verification notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            Mail::to($user->email)->send(new WelcomeEmail($user));
        } catch (\Throwable $e) {
            Log::error('Failed to send welcome email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        Auth::login($user);

        if ($request->filled('register_as_vendor')) {
            return redirect()->route('vendor.dashboard')->with('success', 'Your vendor account has been created and is pending approval.');
        }

        return redirect(AppServiceProvider::HOME);
    }
}
