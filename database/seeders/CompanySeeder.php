<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            [
                'name' => 'PT Maju Bersama Indonesia',
                'email' => 'info@majubersama.co.id',
                'website' => 'https://majubersama.co.id',
            ],
            [
                'name' => 'CV Sinar Harapan Jaya',
                'email' => 'contact@sinarharapan.com',
                'website' => 'https://sinarharapan.com',
            ],
            [
                'name' => 'PT Teknologi Nusantara',
                'email' => 'hello@teknusa.id',
                'website' => 'https://teknusa.id',
            ],
            [
                'name' => 'Syarikat Gemilang Sdn Bhd',
                'email' => 'admin@gemilang.com.my',
                'website' => 'https://gemilang.com.my',
            ],
            [
                'name' => 'PT Karya Digital Mandiri',
                'email' => 'support@karyadigital.id',
                'website' => 'https://karyadigital.id',
            ],
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }
    }
}
