<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('username', 'admin')->first();
        if (!$user) {
            $user = User::updateOrCreate([
                'username' => 'admin',
                'email' => 'admin@admin.com',
                'name' => 'admin, admin admin',
                'proper_full_name' => 'admin a. admin'
            ], [
                'password' => Hash::make('p@55w0rd'),
            ]);

            $user->assignRole(['Super Admin']);
        }
    }
}
