<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the customer-facing web Address Book (AddressController), which
 * was previously a set of empty stub methods with no backing views at all
 * -- every route resolved (Route::resource('addresses', ...) inside the
 * authenticated group), but every method beyond index() did nothing, and
 * even index() would have thrown a ViewNotFoundException since
 * resources/views/addresses/ didn't exist. This is reachable from a live
 * money path: checkout/index.blade.php renders route('addresses.create')
 * as the "Add Address" link shown to any customer with no saved address.
 *
 * Found and fixed while resolving the "input validation audit across Form
 * Request classes" item -- this codebase doesn't use Form Request classes
 * for this feature, but the same audit surfaced the address book itself
 * being unimplemented, which is the more serious defect.
 */
class AddressBookTest extends TestCase
{
    use RefreshDatabase;

    private function addressPayload(array $overrides = []): array
    {
        return array_merge([
            'type' => 'shipping',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
            'address_line1' => '12 Analytical Engine Way',
            'city' => 'Lagos',
            'state' => 'Lagos State',
            'postal_code' => '100001',
            'country' => 'Nigeria',
            'phone' => '+2348000000000',
            'is_default' => false,
        ], $overrides);
    }

    public function test_a_user_can_view_their_own_address_book(): void
    {
        $user = User::factory()->create();
        Address::factory()->count(2)->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('addresses.index'))
            ->assertOk()
            ->assertSee('Address Book');
    }

    public function test_an_empty_address_book_shows_an_actionable_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('addresses.index'))
            ->assertOk()
            // The empty-state copy is static HTML in the view (not a
            // {{ $var }} interpolation), so it renders with a literal
            // apostrophe rather than the &#039; entity assertSee() expects
            // by default -- pass escape=false to match what's actually
            // in the response instead of Blade's auto-escaped output.
            ->assertSee("You haven't saved any addresses yet.", false);
    }

    public function test_a_user_can_create_an_address(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('addresses.store'), $this->addressPayload())
            ->assertRedirect(route('addresses.index'));

        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);
    }

    public function test_creating_an_address_requires_the_fields_the_database_requires(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('addresses.store'), [])
            ->assertSessionHasErrors([
                'type', 'first_name', 'last_name', 'address_line1',
                'city', 'state', 'postal_code', 'country', 'phone',
            ]);
    }

    public function test_marking_a_new_address_default_unsets_the_previous_default(): void
    {
        $user = User::factory()->create();
        $first = Address::factory()->create(['user_id' => $user->id, 'is_default' => true]);

        $this->actingAs($user)->post(route('addresses.store'), $this->addressPayload(['is_default' => true]));

        $this->assertFalse($first->fresh()->is_default);
        $this->assertDatabaseHas('addresses', [
            'user_id' => $user->id,
            'is_default' => true,
            'first_name' => 'Ada',
        ]);
    }

    public function test_a_user_can_update_their_own_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->put(route('addresses.update', $address), $this->addressPayload(['city' => 'Accra']))
            ->assertRedirect(route('addresses.index'));

        $this->assertSame('Accra', $address->fresh()->city);
    }

    public function test_a_user_can_delete_their_own_address(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->delete(route('addresses.destroy', $address))
            ->assertRedirect(route('addresses.index'));

        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_visiting_show_redirects_to_the_edit_form(): void
    {
        $user = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('addresses.show', $address))
            ->assertRedirect(route('addresses.edit', $address));
    }

    public function test_a_user_cannot_view_another_users_address(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder)
            ->get(route('addresses.edit', $address))
            ->assertForbidden();
    }

    public function test_a_user_cannot_update_another_users_address(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $owner->id, 'city' => 'Original City']);

        $this->actingAs($intruder)
            ->put(route('addresses.update', $address), $this->addressPayload())
            ->assertForbidden();

        $this->assertSame('Original City', $address->fresh()->city);
    }

    public function test_a_user_cannot_delete_another_users_address(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $address = Address::factory()->create(['user_id' => $owner->id]);

        $this->actingAs($intruder)
            ->delete(route('addresses.destroy', $address))
            ->assertForbidden();

        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('addresses.index'))
            ->assertRedirect(route('login'));
    }
}
