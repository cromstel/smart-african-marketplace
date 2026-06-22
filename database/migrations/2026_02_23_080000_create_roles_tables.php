<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['role_id', 'user_id']);
        });

        $now = now();
        $roles = [
            ['name' => 'Admin', 'slug' => 'admin', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Vendor', 'slug' => 'vendor', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Salon Owner', 'slug' => 'salon_owner', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Customer', 'slug' => 'customer', 'created_at' => $now, 'updated_at' => $now],
        ];
        DB::table('roles')->insert($roles);

        $roleMap = DB::table('roles')->pluck('id', 'slug');
        $users = DB::table('users')->select('id', 'role')->get();
        foreach ($users as $user) {
            if (! isset($roleMap[$user->role])) {
                continue;
            }
            DB::table('role_user')->insert([
                'role_id' => $roleMap[$user->role],
                'user_id' => $user->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};
