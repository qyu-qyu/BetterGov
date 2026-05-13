<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Municipality;
use App\Models\OfficeType;
use App\Models\Office;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\DocumentType;
use App\Models\Service;
use App\Models\RequestStatus;
use App\Models\RequestPriority;
use App\Models\OfficeTimeSlot;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $adminRole   = Role::firstOrCreate(['name' => 'admin']);
        $officeRole  = Role::firstOrCreate(['name' => 'office']);
        $citizenRole = Role::firstOrCreate(['name' => 'citizen']);

        // Create municipalities
        $mun1 = Municipality::firstOrCreate(['name' => 'New York'], ['city' => 'New York']);
        $mun2 = Municipality::firstOrCreate(['name' => 'Los Angeles'], ['city' => 'Los Angeles']);

        // Create office types
        $type1 = OfficeType::firstOrCreate(['name' => 'City Hall']);
        $type2 = OfficeType::firstOrCreate(['name' => 'Service Center']);

        // Create offices
        $office1 = Office::firstOrCreate(
            ['name' => 'New York City Hall'],
            ['municipality_id' => $mun1->id, 'office_type_id' => $type1->id, 'address' => '123 Main St', 'phone' => '212-555-0001', 'email' => 'nychal@example.com']
        );
        $office2 = Office::firstOrCreate(
            ['name' => 'LA Service Center'],
            ['municipality_id' => $mun2->id, 'office_type_id' => $type2->id, 'address' => '456 Oak Ave', 'phone' => '213-555-0002', 'email' => 'laservice@example.com']
        );

        // Create service categories
        $cat1 = ServiceCategory::firstOrCreate(['name' => 'Permits & Licenses']);
        $cat2 = ServiceCategory::firstOrCreate(['name' => 'Records & Certificates']);
        $cat3 = ServiceCategory::firstOrCreate(['name' => 'Registrations']);

        // Create service types
        $stype1 = ServiceType::firstOrCreate(['name' => 'Application']);
        $stype2 = ServiceType::firstOrCreate(['name' => 'Renewal']);
        $stype3 = ServiceType::firstOrCreate(['name' => 'Request']);

        // Create document types
        $doc1 = DocumentType::firstOrCreate(['name' => 'Government ID']);
        $doc2 = DocumentType::firstOrCreate(['name' => 'Proof of Address']);
        $doc3 = DocumentType::firstOrCreate(['name' => 'Birth Certificate']);
        $doc4 = DocumentType::firstOrCreate(['name' => 'Tax Form']);

        // Create services
        $svc1 = Service::firstOrCreate(
            ['name' => 'Business License Application', 'office_id' => $office1->id],
            ['service_category_id' => $cat1->id, 'service_type_id' => $stype1->id, 'fee' => 150.00, 'estimated_time' => '5 days']
        );
        $svc2 = Service::firstOrCreate(
            ['name' => 'Birth Certificate Request', 'office_id' => $office1->id],
            ['service_category_id' => $cat2->id, 'service_type_id' => $stype3->id, 'fee' => 25.00, 'estimated_time' => '3 days']
        );
        $svc3 = Service::firstOrCreate(
            ['name' => 'Vehicle Registration', 'office_id' => $office2->id],
            ['service_category_id' => $cat3->id, 'service_type_id' => $stype1->id, 'fee' => 80.00, 'estimated_time' => '10 days']
        );

        // Attach required documents to services
        $svc1->documentTypes()->syncWithoutDetaching([$doc1->id, $doc2->id, $doc4->id]);
        $svc2->documentTypes()->syncWithoutDetaching([$doc1->id, $doc3->id]);
        $svc3->documentTypes()->syncWithoutDetaching([$doc1->id, $doc2->id]);

        // Create request statuses
        RequestStatus::firstOrCreate(['name' => 'pending']);
        RequestStatus::firstOrCreate(['name' => 'processing']);
        RequestStatus::firstOrCreate(['name' => 'approved']);
        RequestStatus::firstOrCreate(['name' => 'rejected']);
        RequestStatus::firstOrCreate(['name' => 'completed']);

        // Create request priorities
        RequestPriority::firstOrCreate(['name' => 'low']);
        RequestPriority::firstOrCreate(['name' => 'medium']);
        RequestPriority::firstOrCreate(['name' => 'high']);

        // Create test users
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Admin User', 'password' => bcrypt('password'), 'role_id' => $adminRole->id]
        );

        $officeUser = User::firstOrCreate(
            ['email' => 'office@example.com'],
            ['name' => 'Office User', 'password' => bcrypt('password'), 'role_id' => $officeRole->id]
        );

        $citizen = User::firstOrCreate(
            ['email' => 'citizen@example.com'],
            ['name' => 'Test Citizen', 'password' => bcrypt('password'), 'role_id' => $citizenRole->id]
        );

        // Create office time slots
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        foreach ($days as $day) {
            OfficeTimeSlot::firstOrCreate(
                ['office_id' => $office1->id, 'day_of_week' => $day],
                ['start_time' => '09:00', 'end_time' => '17:00', 'max_capacity' => 10, 'is_active' => true]
            );
            OfficeTimeSlot::firstOrCreate(
                ['office_id' => $office2->id, 'day_of_week' => $day],
                ['start_time' => '08:00', 'end_time' => '16:00', 'max_capacity' => 8, 'is_active' => true]
            );
        }

        echo "\nTest data seeded successfully!\n";
        echo "Test Citizen credentials: citizen@example.com / password\n";
        echo "Use this token after login to test the app.\n";
    }
}
