<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * The app actually has one role system, not the three it can look like at a
 * glance: the `role` column (fast single-role path) and the hand-rolled
 * `roles`/`role_user` tables (multi-role fallback) are both read by
 * User::hasRole(). spatie/laravel-permission is a composer dependency with
 * its config published, but App\Models\User never uses its HasRoles trait
 * and nothing in the app calls its methods -- it was fully unused, and its
 * dead tables (permissions, model_has_permissions, model_has_roles,
 * role_has_permissions) should be dropped once that migration can be
 * applied (see the Phase 1 notes -- an automated safety check on this
 * machine currently blocks writing a migration file with DROP TABLE-style
 * content).
 *
 * These tests pin the one system that's actually live, so a future change
 * can't silently reintroduce role-check drift.
 */
class UserRoleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_role_column_drives_the_is_helpers(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create(); // factory default role: customer

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isCustomer());

        $this->assertTrue($customer->isCustomer());
        $this->assertFalse($customer->isAdmin());
    }

    public function test_is_vendor_checks_both_vendor_and_salon_owner_roles(): void
    {
        $vendor = User::factory()->create(['role' => 'vendor']);
        $salonOwner = User::factory()->create(['role' => 'salon_owner']);
        $customer = User::factory()->create();

        $this->assertTrue($vendor->isVendor());
        $this->assertTrue($salonOwner->isVendor());
        $this->assertFalse($customer->isVendor());
    }

    public function test_has_role_falls_back_to_the_relational_roles_table(): void
    {
        // A role granted through the roles/role_user pivot, independent of
        // the `role` column, must still be picked up by hasRole().
        $user = User::factory()->create(); // role column: customer
        Role::firstOrCreate(['slug' => 'support_agent'], ['name' => 'Support Agent']);

        $this->assertFalse($user->hasRole('support_agent'));

        $user->assignRoles(['support_agent']);
        $user->refresh();

        $this->assertTrue($user->fresh()->hasRole('support_agent'));
        $this->assertTrue($user->fresh()->hasAnyRole(['nonexistent', 'support_agent']));
    }

    public function test_the_live_role_tables_are_unaffected(): void
    {
        $this->assertTrue(Schema::hasTable('roles'));
        $this->assertTrue(Schema::hasTable('role_user'));
        $this->assertGreaterThanOrEqual(4, Role::whereIn('slug', ['admin', 'vendor', 'salon_owner', 'customer'])->count());
    }
}
