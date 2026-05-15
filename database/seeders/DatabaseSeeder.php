<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use App\Models\Municipality;
use App\Models\Office;
use App\Models\OfficeTimeSlot;
use App\Models\OfficeType;
use App\Models\Role;
use App\Models\ServiceCategory;
use App\Models\ServiceType;
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
                'office_type'     => 'municipality',
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
                'office_type'     => 'civil_registry',
                'address'         => 'Tall Square, Tripoli',
                'phone'           => '+961 6 431 000',
                'email'           => 'tripoli@bettergov.lb',
                'latitude'        => 34.4367,
                'longitude'       => 35.8497,
                'working_hours'   => 'Mon–Fri 8:00 AM – 2:00 PM',
            ]
        );

        $civilRegistryOffice = Office::firstOrCreate(
            ['name' => 'Civil Registry Office'],
            [
                'municipality_id' => $beirut->id,
                'office_type_id'  => $cityHall->id,
                'office_type'     => 'civil_registry',
                'address'         => 'Beirut, Lebanon',
                'phone'           => '+961 1 111 111',
                'email'           => 'civil.registry@bettergov.lb',
                'working_hours'   => 'Mon–Fri 8:00 AM – 2:00 PM',
            ]
        );

        $mukhtarOffice = Office::firstOrCreate(
            ['name' => 'Mukhtar Office'],
            [
                'municipality_id' => $beirut->id,
                'office_type_id'  => $cityHall->id,
                'office_type'     => 'mukhtar',
                'address'         => 'Beirut, Lebanon',
                'phone'           => '+961 1 222 222',
                'email'           => 'mukhtar@bettergov.lb',
                'working_hours'   => 'Mon–Fri 8:00 AM – 2:00 PM',
            ]
        );

        $publicHealthOffice = Office::firstOrCreate(
            ['name' => 'Public Health Office'],
            [
                'municipality_id' => $beirut->id,
                'office_type_id'  => $svcCenter->id,
                'office_type'     => 'public_health',
                'address'         => 'Beirut, Lebanon',
                'phone'           => '+961 1 333 333',
                'email'           => 'public.health@bettergov.lb',
                'working_hours'   => 'Mon–Fri 8:00 AM – 2:00 PM',
            ]
        );

        $generalSecurityOffice = Office::firstOrCreate(
            ['name' => 'General Security Office'],
            [
                'municipality_id' => $tripoli->id,
                'office_type_id'  => $svcCenter->id,
                'office_type'     => 'general_security',
                'address'         => 'Tripoli, Lebanon',
                'phone'           => '+961 6 444 444',
                'email'           => 'general.security@bettergov.lb',
                'working_hours'   => 'Mon–Fri 8:00 AM – 2:00 PM',
            ]
        );

        // ── Document types ─────────────────────────────────────────────────────
        $documentTypeNames = [
            'Birth notification signed by the delivering doctor or midwife',
            'Mukhtar-certified birth certificate',
            "Both parents' Lebanese ID cards",
            'Family Civil Status Extract (إخراج قيد عائلي) — not older than 1 year',
            'Marriage certificate registered in Lebanon',
            'Religious marriage certificate (from church, mosque, or court)',
            "Both spouses' Lebanese ID cards",
            'Individual and Family Civil Status Extracts for each spouse',
            'Non-Lebanese spouse: translated & legalized birth certificate + passport copy',
            'Original death certificate',
            "Deceased's Lebanese ID card and/or passport",
            'Family or Individual Civil Status Extract of the deceased',
            'Marriage certificate if applicable',
            'Burial permit (issued by Civil Registry upon registration)',
            'Religious or court divorce decree',
            'Lebanese Marriage Certificate',
            'Family Civil Status Extract',
            'Passport copies of both spouses',
            'Lebanese ID card or passport',
            'Statement of the registrant\'s village and registration number (القضاء والرقم)',
            'Proof of residence (utility bill, lease contract, or landlord declaration)',
            'Recent Individual Civil Status Extract',
            'Physical presence of the applicant',
            'Passport application form signed in front of the Mukhtar',
            'Lebanese ID card or Civil Status Extract (not older than 3 months)',
            'Recent passport photo (4.5×3.5 cm, white background, Mukhtar-signed)',
            'Old passport (if applicable)',
            'Title deed (صك ملكية) or proof of ownership',
            'Architectural and structural drawings signed by licensed engineers',
            "Engineers' syndicate approvals",
            'Site plan (aerial view to scale)',
            'Property tax clearance',
            'Application form signed by property owner',
            'Structural engineer\'s report confirming demolition safety',
            'Title deed',
            'Site plan',
            'Municipal application form',
            'ID card or passport',
            'Lease contract or title deed for the premises',
            'Commercial register extract (سجل تجاري) from the Ministry of Economy',
            'Health clearance (food businesses)',
            'Fire safety compliance certificate',
            'Application form',
            'Event details (date, location, type)',
            "Organizer's ID",
            'Venue ownership/lease proof',
            'Security plan (for large events)',
            'Technical drawings of the excavation area',
            'Company trade license',
            "Engineer\'s signature",
            'Municipality application + fees',
            'Stool test results',
            'Medical examination report',
            'ID card',
            'Lebanese Order of Pharmacists/Physicians membership',
            'Premises lease contract',
            'Facility layout plans',
            'ID card + academic credentials',
            'Passport application form (signed by Mukhtar)',
            'Civil Status Extract (not older than 3 months)',
            'Lebanese National ID card',
            'Recent passport photo (Mukhtar-certified)',
            'Old passport (if renewing)',
            'Payment of fees',
            'Valid passport',
            'Lease contract or title deed',
            'Bank statement (for long-term residency)',
            'Employer attestation or salary certificate',
            'Health insurance proof',
            'Passport photos',
            // generic fallback names used elsewhere in the app
            'National ID',
            'Passport',
            'Proof of Residence',
            'Birth Certificate',
            'Tax Declaration',
        ];

        foreach (array_values(array_unique($documentTypeNames)) as $documentTypeName) {
            DocumentType::firstOrCreate(['name' => $documentTypeName]);
        }

        $natId    = DocumentType::firstOrCreate(['name' => 'National ID']);
        $passport = DocumentType::firstOrCreate(['name' => 'Passport']);
        $proof    = DocumentType::firstOrCreate(['name' => 'Proof of Residence']);
        $birth    = DocumentType::firstOrCreate(['name' => 'Birth Certificate']);
        $tax      = DocumentType::firstOrCreate(['name' => 'Tax Declaration']);

        // ── Service categories ─────────────────────────────────────────────────
        $catPermits  = ServiceCategory::firstOrCreate(['name' => 'Permits & Licenses']);
        $catRecords  = ServiceCategory::firstOrCreate(['name' => 'Records & Certificates']);
        $catRegist   = ServiceCategory::firstOrCreate(['name' => 'Registrations']);
        // Template categories (used when offices adopt service templates)
        ServiceCategory::firstOrCreate(['name' => 'Civil Registry']);
        ServiceCategory::firstOrCreate(['name' => 'Mukhtar Services']);
        ServiceCategory::firstOrCreate(['name' => 'Municipal Permits']);
        ServiceCategory::firstOrCreate(['name' => 'Public Health']);
        ServiceCategory::firstOrCreate(['name' => 'General Security']);

        // ── Service types ──────────────────────────────────────────────────────
        $stypeApp    = ServiceType::firstOrCreate(['name' => 'Application']);
        $stypeRenew  = ServiceType::firstOrCreate(['name' => 'Renewal']);
        $stypeReq    = ServiceType::firstOrCreate(['name' => 'Request']);

        // ── Services ───────────────────────────────────────────────────────────
        // Services intentionally omitted.

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
            ['email' => 'civil.registry@bettergov.lb'],
            [
                'name'      => 'Civil Registry Manager',
                'password'  => 'Office@1234',
                'role_id'   => $officeRole->id,
                'office_id' => $civilRegistryOffice->id,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'mukhtar@bettergov.lb'],
            [
                'name'      => 'Mukhtar Manager',
                'password'  => 'Office@1234',
                'role_id'   => $officeRole->id,
                'office_id' => $mukhtarOffice->id,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'public.health@bettergov.lb'],
            [
                'name'      => 'Public Health Manager',
                'password'  => 'Office@1234',
                'role_id'   => $officeRole->id,
                'office_id' => $publicHealthOffice->id,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'general.security@bettergov.lb'],
            [
                'name'      => 'General Security Manager',
                'password'  => 'Office@1234',
                'role_id'   => $officeRole->id,
                'office_id' => $generalSecurityOffice->id,
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

        $this->command->info('');
        $this->command->info('✅  Database seeded successfully!');
        $this->command->info('');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin',                'admin@bettergov.lb',          'Admin@1234'],
                ['Office (Beirut)',      'beirut@bettergov.lb',         'Office@1234'],
                ['Office (Tripoli)',     'tripoli@bettergov.lb',        'Office@1234'],
                ['Office (Civil Registry)','civil.registry@bettergov.lb','Office@1234'],
                ['Office (Mukhtar)',     'mukhtar@bettergov.lb',        'Office@1234'],
                ['Office (Public Health)','public.health@bettergov.lb', 'Office@1234'],
                ['Office (General Security)','general.security@bettergov.lb','Office@1234'],
                ['Citizen',              'citizen@bettergov.lb',        'Citizen@1234'],
            ]
        );
        $this->call(ServiceTemplateSeeder::class);

        $this->command->info('');
        $this->command->info('Run: php artisan migrate:fresh --seed');
    }
}
