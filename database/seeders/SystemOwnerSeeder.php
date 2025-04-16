<?php

namespace Database\Seeders;

use App\Models\SystemOwner;
use Illuminate\Database\Seeder;

class SystemOwnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systemOwners = SystemOwner::updateOrCreate([
            'email' => 'shokrymansor123@gmail.com',
        ], [
            'name' => 'shokry',
            'phone' => '01014001055',
            'password' => bcrypt('123456789'),
        ]);
    }
}
