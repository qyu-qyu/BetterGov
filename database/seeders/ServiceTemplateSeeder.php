<?php

namespace Database\Seeders;

use App\Models\ServiceTemplate;
use Illuminate\Database\Seeder;

class ServiceTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [

            // ── Civil Registry ─────────────────────────────────────────────────
            [
                'category'           => 'Civil Registry',
                'name_en'            => 'Birth Registration',
                'name_ar'            => 'تسجيل الولادة',
                'description'        => 'Official registration of a newborn with the civil registry.',
                'required_documents' => [
                    'Birth notification signed by the delivering doctor or midwife',
                    'Mukhtar-certified birth certificate',
                    "Both parents' Lebanese ID cards",
                    'Family Civil Status Extract (إخراج قيد عائلي) — not older than 1 year',
                    'Marriage certificate registered in Lebanon',
                ],
                'estimated_days' => 3,
            ],
            [
                'category'           => 'Civil Registry',
                'name_en'            => 'Marriage Registration',
                'name_ar'            => 'تسجيل الزواج',
                'description'        => 'Official registration of a marriage in the civil registry.',
                'required_documents' => [
                    'Religious marriage certificate (from church, mosque, or court)',
                    "Both spouses' Lebanese ID cards",
                    'Individual and Family Civil Status Extracts for each spouse',
                    'Non-Lebanese spouse: translated & legalized birth certificate + passport copy',
                ],
                'estimated_days' => 5,
            ],
            [
                'category'           => 'Civil Registry',
                'name_en'            => 'Death Registration',
                'name_ar'            => 'تسجيل الوفاة',
                'description'        => 'Official registration of a death with the civil registry.',
                'required_documents' => [
                    'Original death certificate',
                    "Deceased's Lebanese ID card and/or passport",
                    'Family or Individual Civil Status Extract of the deceased',
                    'Marriage certificate if applicable',
                    'Burial permit (issued by Civil Registry upon registration)',
                ],
                'estimated_days' => 2,
            ],
            [
                'category'           => 'Civil Registry',
                'name_en'            => 'Divorce Registration',
                'name_ar'            => 'تسجيل الطلاق',
                'description'        => 'Official registration of a divorce decree in the civil registry.',
                'required_documents' => [
                    'Religious or court divorce decree',
                    "Both spouses' Lebanese ID cards",
                    'Lebanese Marriage Certificate',
                    'Family Civil Status Extract',
                    'Passport copies of both spouses',
                ],
                'estimated_days' => 5,
            ],
            [
                'category'           => 'Civil Registry',
                'name_en'            => 'Civil Status Extract (Individual/Family)',
                'name_ar'            => 'إخراج قيد',
                'description'        => 'Certified extract of personal or family civil registration records.',
                'required_documents' => [
                    'Lebanese ID card or passport',
                    'Statement of the registrant\'s village and registration number (القضاء والرقم)',
                ],
                'estimated_days' => 1,
            ],

            // ── Mukhtar Services ───────────────────────────────────────────────
            [
                'category'           => 'Mukhtar Services',
                'name_en'            => 'Residence Attestation',
                'name_ar'            => 'شهادة سكن',
                'description'        => 'Official attestation of place of residence issued by the Mukhtar.',
                'required_documents' => [
                    'Lebanese ID card or passport',
                    'Proof of residence (utility bill, lease contract, or landlord declaration)',
                ],
                'estimated_days' => 1,
            ],
            [
                'category'           => 'Mukhtar Services',
                'name_en'            => 'Good Conduct Certificate',
                'name_ar'            => 'شهادة حسن سيرة وسلوك',
                'description'        => 'Certificate attesting good conduct and behavior issued by the Mukhtar.',
                'required_documents' => [
                    'Lebanese ID card',
                    'Recent Individual Civil Status Extract',
                ],
                'estimated_days' => 1,
            ],
            [
                'category'           => 'Mukhtar Services',
                'name_en'            => 'Life Certificate',
                'name_ar'            => 'شهادة قيد الحياة',
                'description'        => 'Certificate confirming that the applicant is alive, often required for pensions.',
                'required_documents' => [
                    'Lebanese ID card',
                    'Physical presence of the applicant',
                ],
                'estimated_days' => 1,
            ],
            [
                'category'           => 'Mukhtar Services',
                'name_en'            => 'Passport Endorsement Letter',
                'name_ar'            => 'توقيع طلب جواز السفر',
                'description'        => 'Mukhtar endorsement letter required for passport applications.',
                'required_documents' => [
                    'Passport application form signed in front of the Mukhtar',
                    'Lebanese ID card or Civil Status Extract (not older than 3 months)',
                    'Recent passport photo (4.5×3.5 cm, white background, Mukhtar-signed)',
                    'Old passport (if applicable)',
                ],
                'estimated_days' => 1,
            ],

            // ── Municipal Permits ──────────────────────────────────────────────
            [
                'category'           => 'Municipal Permits',
                'name_en'            => 'Building Permit',
                'name_ar'            => 'رخصة بناء',
                'description'        => 'Official permit to construct or expand a building within the municipality.',
                'required_documents' => [
                    'Title deed (صك ملكية) or proof of ownership',
                    'Architectural and structural drawings signed by licensed engineers',
                    "Engineers' syndicate approvals",
                    'Site plan (aerial view to scale)',
                    'Property tax clearance',
                    'Application form signed by property owner',
                ],
                'estimated_days' => 30,
            ],
            [
                'category'           => 'Municipal Permits',
                'name_en'            => 'Demolition Permit',
                'name_ar'            => 'رخصة هدم',
                'description'        => 'Permit required before demolishing a structure within the municipality.',
                'required_documents' => [
                    'Title deed',
                    'Structural engineer\'s report confirming demolition safety',
                    'Site plan',
                    'Municipal application form',
                ],
                'estimated_days' => 14,
            ],
            [
                'category'           => 'Municipal Permits',
                'name_en'            => 'Commercial Trade License',
                'name_ar'            => 'إجازة مزاولة مهنة',
                'description'        => 'License to operate a commercial trade or business within the municipality.',
                'required_documents' => [
                    'ID card or passport',
                    'Lease contract or title deed for the premises',
                    'Commercial register extract (سجل تجاري) from the Ministry of Economy',
                    'Health clearance (food businesses)',
                    'Fire safety compliance certificate',
                    'Municipal application form',
                ],
                'estimated_days' => 10,
            ],
            [
                'category'           => 'Municipal Permits',
                'name_en'            => 'Event Noise / Environmental Permit',
                'name_ar'            => null,
                'description'        => 'Permit for events that may generate noise or environmental impact.',
                'required_documents' => [
                    'Event details (date, location, type)',
                    "Organizer's ID",
                    'Venue ownership/lease proof',
                    'Security plan (for large events)',
                ],
                'estimated_days' => 5,
            ],
            [
                'category'           => 'Municipal Permits',
                'name_en'            => 'Street Excavation Permit',
                'name_ar'            => null,
                'description'        => 'Permit for excavation work on public roads or sidewalks.',
                'required_documents' => [
                    'Technical drawings of the excavation area',
                    'Company trade license',
                    "Engineer\'s signature",
                    'Municipality application + fees',
                ],
                'estimated_days' => 7,
            ],

            // ── Public Health ──────────────────────────────────────────────────
            [
                'category'           => 'Public Health',
                'name_en'            => 'Food Handler Health Certificate',
                'name_ar'            => 'شهادة صحية',
                'description'        => 'Health certificate required for individuals handling food commercially.',
                'required_documents' => [
                    'Stool test results',
                    'Medical examination report',
                    'ID card',
                ],
                'estimated_days' => 3,
            ],
            [
                'category'           => 'Public Health',
                'name_en'            => 'Pharmacy / Clinic License',
                'name_ar'            => null,
                'description'        => 'License to open and operate a pharmacy or medical clinic.',
                'required_documents' => [
                    'Lebanese Order of Pharmacists/Physicians membership',
                    'Premises lease contract',
                    'Facility layout plans',
                    'ID card + academic credentials',
                ],
                'estimated_days' => 21,
            ],

            // ── General Security ───────────────────────────────────────────────
            [
                'category'           => 'General Security',
                'name_en'            => 'Biometric Passport Issuance',
                'name_ar'            => 'جواز سفر بيومتري',
                'description'        => 'Issuance or renewal of a Lebanese biometric passport.',
                'required_documents' => [
                    'Passport application form (signed by Mukhtar)',
                    'Civil Status Extract (not older than 3 months)',
                    'Lebanese National ID card',
                    'Recent passport photo (Mukhtar-certified)',
                    'Old passport (if renewing)',
                    'Payment of fees',
                ],
                'estimated_days' => 14,
            ],
            [
                'category'           => 'General Security',
                'name_en'            => 'Residency Permit for Foreigners',
                'name_ar'            => 'إقامة',
                'description'        => 'Residency permit for non-Lebanese individuals residing in Lebanon.',
                'required_documents' => [
                    'Valid passport',
                    'Lease contract or title deed',
                    'Bank statement (for long-term residency)',
                    'Employer attestation or salary certificate',
                    'Health insurance proof',
                    'Passport photos',
                ],
                'estimated_days' => 14,
            ],
        ];

        foreach ($templates as $data) {
            ServiceTemplate::firstOrCreate(
                ['name_en' => $data['name_en'], 'category' => $data['category']],
                $data
            );
        }

        $this->command->info('✅  Service templates seeded (' . count($templates) . ' templates)');
    }
}
