<?php

namespace Database\Seeders;

use App\Models\{Firma, User, Project};
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Erstelle Test-Firma
        $testFirma = Firma::create([
            'name' => 'Test Company GmbH',
            'description' => 'Eine Test-Firma für die Demonstration des Ticket-Systems',
            'email' => 'info@test-company.de',
            'phone' => '+49 30 12345678',
            'address' => 'Musterstraße 123, 10115 Berlin',
        ]);

        // Erstelle Test Customer
        $testCustomer = User::create([
            'name' => 'Max Mustermann',
            'email' => 'customer@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => UserRole::CUSTOMER,
            'firma_id' => $testFirma->id,
        ]);

        // Erstelle Test Developer
        $testDeveloper = User::create([
            'name' => 'Anna Developer',
            'email' => 'developer@test.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => UserRole::DEVELOPER,
            'firma_id' => null, // Developers gehören zu keiner spezifischen Firma
        ]);

        // Erstelle Test-Projekt
        $testProject = Project::create([
            'name' => 'Website Relaunch 2024',
            'description' => 'Kompletter Relaunch der Firmen-Website mit modernem Design und verbesserter UX',
            'firma_id' => $testFirma->id,
            'created_by' => $testCustomer->id,
        ]);

        // Füge Customer zum Projekt hinzu
        $testProject->users()->attach($testCustomer->id);

        // Erstelle zusätzliche Test-User für die Firma
        $additionalUsers = User::factory(3)->create([
            'role' => UserRole::CUSTOMER,
            'firma_id' => $testFirma->id,
        ]);

        // Füge einen zusätzlichen User zum Projekt hinzu
        $testProject->users()->attach($additionalUsers->first()->id);

        $this->command->info('Test-User erfolgreich erstellt:');
        $this->command->line('- Customer: customer@test.com (Passwort: password)');
        $this->command->line('- Developer: developer@test.com (Passwort: password)');
        $this->command->line('- Firma: ' . $testFirma->name);
        $this->command->line('- Projekt: ' . $testProject->name);
    }
}
