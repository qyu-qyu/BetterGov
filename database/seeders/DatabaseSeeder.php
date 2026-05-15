<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\Municipality;
use App\Models\Office;
use App\Models\OfficeTimeSlot;
use App\Models\OfficeType;
use App\Models\Role;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
use App\Models\Request as ServiceRequest;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Roles ──────────────────────────────────────────────────────────────
        $adminRole   = Role::firstOrCreate(['name' => 'admin']);
        $officeRole  = Role::firstOrCreate(['name' => 'office']);
        $citizenRole = Role::firstOrCreate(['name' => 'citizen']);

        // ── Municipalities ─────────────────────────────────────────────────────
        $beirut  = Municipality::firstOrCreate(['name' => 'Beirut'],  ['city' => 'Greater Beirut']);
        $tripoli = Municipality::firstOrCreate(['name' => 'Tripoli'], ['city' => 'North Lebanon']);

        // ── Office types ───────────────────────────────────────────────────────
        $cityHall  = OfficeType::firstOrCreate(['name' => 'City Hall']);
        $svcCenter = OfficeType::firstOrCreate(['name' => 'Service Center']);

        // ── Offices ────────────────────────────────────────────────────────────
        $office1 = Office::firstOrCreate(
            ['name' => 'Beirut Municipality'],
            [
                'municipality_id' => $beirut->id,
                'office_type_id'  => $cityHall->id,
                'address'         => 'Riad El Solh Square, Beirut',
                'phone'           => '+961 1 981 400',
                'email'           => 'beirut@bettergov.lb',
                'latitude'        => 33.8938,
                'longitude'       => 35.5018,
                'working_hours'   => 'Mon–Fri 8:00 AM – 3:00 PM',
            ]
        );

        $office2 = Office::firstOrCreate(
            ['name' => 'Tripoli Service Center'],
            [
                'municipality_id' => $tripoli->id,
                'office_type_id'  => $svcCenter->id,
                'address'         => 'Tall Square, Tripoli',
                'phone'           => '+961 6 431 000',
                'email'           => 'tripoli@bettergov.lb',
                'latitude'        => 34.4367,
                'longitude'       => 35.8497,
                'working_hours'   => 'Mon–Fri 8:00 AM – 2:00 PM',
            ]
        );

        // ── Document types ─────────────────────────────────────────────────────
        $natId    = DocumentType::firstOrCreate(['name' => 'National ID']);
        $passport = DocumentType::firstOrCreate(['name' => 'Passport']);
        $proof    = DocumentType::firstOrCreate(['name' => 'Proof of Residence']);
        $birth    = DocumentType::firstOrCreate(['name' => 'Birth Certificate']);
        $tax      = DocumentType::firstOrCreate(['name' => 'Tax Declaration']);

        // ── Service categories ─────────────────────────────────────────────────
        $catPermits  = ServiceCategory::firstOrCreate(['name' => 'Permits & Licenses']);
        $catRecords  = ServiceCategory::firstOrCreate(['name' => 'Records & Certificates']);
        $catRegist   = ServiceCategory::firstOrCreate(['name' => 'Registrations']);

        // ── Service types ──────────────────────────────────────────────────────
        $stypeApp    = ServiceType::firstOrCreate(['name' => 'Application']);
        $stypeRenew  = ServiceType::firstOrCreate(['name' => 'Renewal']);
        $stypeReq    = ServiceType::firstOrCreate(['name' => 'Request']);

        // ── Services ───────────────────────────────────────────────────────────
        $svc1 = Service::firstOrCreate(
            ['name' => 'Business License', 'office_id' => $office1->id],
            [
                'service_category_id' => $catPermits->id,
                'service_type_id'     => $stypeApp->id,
                'fee'                 => 150.00,
                'estimated_time'      => '5–7 business days',
                'description'         => 'Apply for a new commercial business license.',
            ]
        );
        $svc1->documentTypes()->syncWithoutDetaching([$natId->id, $proof->id, $tax->id]);

        $svc2 = Service::firstOrCreate(
            ['name' => 'Birth Certificate Copy', 'office_id' => $office1->id],
            [
                'service_category_id' => $catRecords->id,
                'service_type_id'     => $stypeReq->id,
                'fee'                 => 15.00,
                'estimated_time'      => '1–2 business days',
                'description'         => 'Request an official copy of your birth certificate.',
            ]
        );
        $svc2->documentTypes()->syncWithoutDetaching([$natId->id]);

        $svc3 = Service::firstOrCreate(
            ['name' => 'Vehicle Registration', 'office_id' => $office2->id],
            [
                'service_category_id' => $catRegist->id,
                'service_type_id'     => $stypeApp->id,
                'fee'                 => 80.00,
                'estimated_time'      => '3–5 business days',
                'description'         => 'Register a new or transferred vehicle.',
            ]
        );
        $svc3->documentTypes()->syncWithoutDetaching([$natId->id, $proof->id]);

        $svc4 = Service::firstOrCreate(
            ['name' => 'Marriage Certificate', 'office_id' => $office2->id],
            [
                'service_category_id' => $catRecords->id,
                'service_type_id'     => $stypeReq->id,
                'fee'                 => 25.00,
                'estimated_time'      => '2–3 business days',
                'description'         => 'Request an official marriage certificate.',
            ]
        );
        $svc4->documentTypes()->syncWithoutDetaching([$natId->id, $birth->id]);

        // ── Time slots (Mon–Fri for both offices) ──────────────────────────────
        $weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        foreach ($weekdays as $day) {
            OfficeTimeSlot::firstOrCreate(
                ['office_id' => $office1->id, 'day_of_week' => $day, 'start_time' => '09:00'],
                ['end_time' => '12:00', 'max_capacity' => 10, 'is_active' => true]
            );
            OfficeTimeSlot::firstOrCreate(
                ['office_id' => $office1->id, 'day_of_week' => $day, 'start_time' => '13:00'],
                ['end_time' => '16:00', 'max_capacity' => 10, 'is_active' => true]
            );
            OfficeTimeSlot::firstOrCreate(
                ['office_id' => $office2->id, 'day_of_week' => $day, 'start_time' => '08:00'],
                ['end_time' => '14:00', 'max_capacity' => 8, 'is_active' => true]
            );
        }

        // ── Users ──────────────────────────────────────────────────────────────
        // Passwords are plain text — the User model's 'hashed' cast bcrypts automatically
        User::firstOrCreate(
            ['email' => 'admin@bettergov.lb'],
            [
                'name'     => 'Super Admin',
                'password' => 'Admin@1234',
                'role_id'  => $adminRole->id,
                'is_active'=> true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'beirut@bettergov.lb'],
            [
                'name'      => 'Beirut Office Manager',
                'password'  => 'Office@1234',
                'role_id'   => $officeRole->id,
                'office_id' => $office1->id,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'tripoli@bettergov.lb'],
            [
                'name'      => 'Tripoli Office Manager',
                'password'  => 'Office@1234',
                'role_id'   => $officeRole->id,
                'office_id' => $office2->id,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'citizen@bettergov.lb'],
            [
                'name'     => 'Ahmad Khalil',
                'password' => 'Citizen@1234',
                'role_id'  => $citizenRole->id,
                'is_active'=> true,
            ]
        );

        // ── Sample requests (citizen) ──────────────────────────────────────────
        $citizen = User::firstWhere('email', 'citizen@bettergov.lb');

        ServiceRequest::firstOrCreate(
            ['user_id' => $citizen->id, 'service_id' => $svc1->id],
            ['office_id' => $office1->id, 'status' => 'pending',   'notes' => 'I need this for my new shop.']
        );
        ServiceRequest::firstOrCreate(
            ['user_id' => $citizen->id, 'service_id' => $svc2->id],
            ['office_id' => $office1->id, 'status' => 'completed',  'notes' => 'Needed for school enrollment.']
        );
        ServiceRequest::firstOrCreate(
            ['user_id' => $citizen->id, 'service_id' => $svc3->id],
            ['office_id' => $office2->id, 'status' => 'processing', 'notes' => 'Transferring vehicle from previous owner.']
        );

        $this->command->info('');
        $this->command->info('✅  Database seeded successfully!');
        $this->command->info('');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin',           'admin@bettergov.lb',   'Admin@1234'],
                ['Office (Beirut)', 'beirut@bettergov.lb',  'Office@1234'],
                ['Office (Tripoli)','tripoli@bettergov.lb', 'Office@1234'],
                ['Citizen',         'citizen@bettergov.lb', 'Citizen@1234'],
            ]
        );
        $this->command->info('');
        $this->command->info('Run: php artisan migrate:fresh --seed');
    }
}
