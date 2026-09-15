<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Concerns\ApiResponse;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $addresses = Address::where('user_id', $request->user()->id)->latest()->get();

        return $this->successResponse(AddressResource::collection($addresses), 'Addresses retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        // state/postal_code/phone are required (not nullable): the
        // `addresses` migration defines all three as NOT NULL with no
        // default, so validating them as optional let a request pass
        // validation and then blow up with a raw QueryException at the
        // database layer instead of a clean 422 (found while resolving the
        // input-validation audit; the web AddressController shares this
        // same rule set).
        $validated = $request->validate([
            'type' => ['required', 'in:billing,shipping,both'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'address_line1' => ['required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:30'],
            'is_default' => ['boolean'],
        ]);

        if (! empty($validated['is_default'])) {
            Address::where('user_id', $request->user()->id)->update(['is_default' => false]);
        }

        $address = $request->user()->addresses()->create($validated);

        return $this->successResponse(new AddressResource($address), 'Address created successfully', 201);
    }

    public function show(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse(new AddressResource($address), 'Address retrieved successfully');
    }

    public function update(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $validated = $request->validate([
            'type' => ['sometimes', 'required', 'in:billing,shipping,both'],
            'first_name' => ['sometimes', 'required', 'string', 'max:255'],
            'last_name' => ['sometimes', 'required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'address_line1' => ['sometimes', 'required', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'required', 'string', 'max:255'],
            'state' => ['sometimes', 'required', 'string', 'max:255'],
            'postal_code' => ['sometimes', 'required', 'string', 'max:20'],
            'country' => ['sometimes', 'required', 'string', 'max:100'],
            'phone' => ['sometimes', 'required', 'string', 'max:30'],
            'is_default' => ['boolean'],
        ]);

        if (! empty($validated['is_default'])) {
            Address::where('user_id', $request->user()->id)
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($validated);

        return $this->successResponse(new AddressResource($address), 'Address updated successfully');
    }

    public function destroy(Request $request, Address $address): JsonResponse
    {
        if ($address->user_id !== $request->user()->id) {
            return $this->errorResponse('Unauthorized', 403);
        }

        $address->delete();

        return $this->successResponse(null, 'Address deleted successfully');
    }
}
