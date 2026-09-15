<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AddressController extends Controller
{
    /**
     * Display the authenticated user's saved addresses.
     */
    public function index(): View
    {
        $addresses = Address::where('user_id', auth()->id())->latest()->get();

        return view('addresses.index', compact('addresses'));
    }

    /**
     * Show the form for creating a new address.
     */
    public function create(): View
    {
        return view('addresses.create');
    }

    /**
     * Store a newly created address for the authenticated user.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());

        if (! empty($validated['is_default'])) {
            Address::where('user_id', auth()->id())->update(['is_default' => false]);
        }

        auth()->user()->addresses()->create($validated);

        return redirect()->route('addresses.index')->with('success', 'Address added successfully.');
    }

    /**
     * Addresses have no standalone detail view -- the edit form already
     * displays every field, so `show` sends the user straight there instead
     * of duplicating it as a separate read-only page.
     */
    public function show(Address $address): RedirectResponse
    {
        $this->authorizeOwnership($address);

        return redirect()->route('addresses.edit', $address);
    }

    /**
     * Show the form for editing the specified address.
     */
    public function edit(Address $address): View
    {
        $this->authorizeOwnership($address);

        return view('addresses.edit', compact('address'));
    }

    /**
     * Update the specified address.
     */
    public function update(Request $request, Address $address): RedirectResponse
    {
        $this->authorizeOwnership($address);

        $validated = $request->validate($this->rules(partial: true));

        if (! empty($validated['is_default'])) {
            Address::where('user_id', auth()->id())
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($validated);

        return redirect()->route('addresses.index')->with('success', 'Address updated successfully.');
    }

    /**
     * Remove the specified address.
     */
    public function destroy(Address $address): RedirectResponse
    {
        $this->authorizeOwnership($address);

        $address->delete();

        return redirect()->route('addresses.index')->with('success', 'Address removed.');
    }

    /**
     * Ensure the authenticated user owns the given address. This mirrors the
     * manual ownership check already used by the API counterpart
     * (Api\AddressController) and elsewhere in this codebase (e.g.
     * WishlistController::destroy) rather than introducing a dedicated
     * Policy for a single-model, single-relationship ownership rule.
     */
    private function authorizeOwnership(Address $address): void
    {
        abort_if($address->user_id !== auth()->id(), 403);
    }

    /**
     * Validation rules shared between store() and update().
     *
     * Kept in sync with Api\AddressController's rules so the web and API
     * address books enforce identical constraints. `state`, `postal_code`
     * and `phone` are required (not nullable, unlike a first pass at this
     * might assume) because the `addresses` migration defines all three as
     * NOT NULL with no default -- validating them as optional would let a
     * request pass validation and then fail with a raw QueryException at
     * the database layer instead of a clean 422. Api\AddressController had
     * this exact mismatch; fixed there too as part of this same pass.
     */
    private function rules(bool $partial = false): array
    {
        $required = $partial ? ['sometimes', 'required'] : ['required'];

        return [
            'type' => [...$required, 'in:billing,shipping,both'],
            'first_name' => [...$required, 'string', 'max:255'],
            'last_name' => [...$required, 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'address_line1' => [...$required, 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => [...$required, 'string', 'max:255'],
            'state' => [...$required, 'string', 'max:255'],
            'postal_code' => [...$required, 'string', 'max:20'],
            'country' => [...$required, 'string', 'max:100'],
            'phone' => [...$required, 'string', 'max:30'],
            'is_default' => ['boolean'],
        ];
    }
}
