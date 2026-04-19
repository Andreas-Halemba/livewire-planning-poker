<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Fixed credentials for local development only. Run via `php artisan db:seed` when APP_ENV=local.
 *
 * @see DatabaseSeeder
 */
class DevUsersSeeder extends Seeder
{
    /**
     * @return list<array{name: string, email: string, password: string}>
     */
    public static function definitions(): array
    {
        return [
            ['name' => 'Local Dev', 'email' => 'dev@planning-poker.test', 'password' => 'password'],
            ['name' => 'Tester One', 'email' => 'tester1@planning-poker.test', 'password' => 'password'],
            ['name' => 'Tester Two', 'email' => 'tester2@planning-poker.test', 'password' => 'password'],
        ];
    }

    public function run(): void
    {
        foreach (self::definitions() as $row) {
            $plain = $row['password'];
            unset($row['password']);

            User::updateOrCreate(
                ['email' => $row['email']],
                [
                    ...$row,
                    'password' => bcrypt($plain),
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
