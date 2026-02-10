<?php

namespace Database\Seeders;

use App\Models\User;
// use App\Models\Tender; // Not needed if we use separate seeders
// use App\Models\Bid; // Not needed
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);

        // Create Main Contractor User
        User::create([
            'name' => 'Contractor User',
            'email' => 'contractor@example.com',
            'password' => Hash::make('password123'),
            'role' => 'contractor',
            'company_name' => 'ABC Construction SRL',
            'vat_number' => 'IT12345678901',
            'fiscal_code' => 'ABCXYZ80A01H501Z',
            'address' => 'Via Roma 123',
            'city' => 'Milano',
            'province' => 'MI',
            'phone' => '+39 02 1234567',
            'legal_representative' => 'Mario Rossi',
        ]);
        
        // Create Main Owner User
        User::create([
            'name' => 'Owner User',
            'email' => 'owner@example.com',
            'password' => Hash::make('password123'),
            'role' => 'owner',
        ]);

        // Run other seeders
        $this->call([
            ContractorSeeder::class,
            TenderSeeder::class,
            BidSeeder::class,
        ]);
    }
}
