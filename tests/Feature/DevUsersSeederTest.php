<?php

use App\Models\User;
use Database\Seeders\DevUsersSeeder;
use Illuminate\Support\Facades\Hash;

test('dev users seeder upserts expected users and passwords', function () {
    $this->seed(DevUsersSeeder::class);

    foreach (DevUsersSeeder::definitions() as $row) {
        $user = User::where('email', $row['email'])->first();
        expect($user)->not->toBeNull()
            ->and($user->name)->toBe($row['name'])
            ->and(Hash::check($row['password'], $user->password))->toBeTrue()
            ->and($user->email_verified_at)->not->toBeNull();
    }
});
