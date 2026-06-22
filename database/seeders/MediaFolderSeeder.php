<?php

namespace Database\Seeders;

use App\Models\MediaFolder;
use App\Models\User;
use Illuminate\Database\Seeder;

class MediaFolderSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@africanmarket.com')->first()
            ?? User::where('role', 'admin')->first();

        if (! $admin) {
            return;
        }

        MediaFolder::firstOrCreate(
            ['slug' => 'seeded'],
            [
                'name' => 'Seeded',
                'slug' => 'seeded',
                'owner_type' => get_class($admin),
                'owner_id' => $admin->id,
                'created_by_id' => $admin->id,
            ]
        );
    }
}
