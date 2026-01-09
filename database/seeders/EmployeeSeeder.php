<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            // PT Maju Bersama Indonesia (1)
            ['first_name' => 'Budi', 'last_name' => 'Santoso', 'company_id' => 1, 'email' => 'budi.santoso@majubersama.co.id', 'phone' => '081234567890'],
            ['first_name' => 'Siti', 'last_name' => 'Rahayu', 'company_id' => 1, 'email' => 'siti.rahayu@majubersama.co.id', 'phone' => '081234567891'],
            ['first_name' => 'Agus', 'last_name' => 'Wijaya', 'company_id' => 1, 'email' => 'agus.wijaya@majubersama.co.id', 'phone' => '081234567892'],
            ['first_name' => 'Dewi', 'last_name' => 'Lestari', 'company_id' => 1, 'email' => 'dewi.lestari@majubersama.co.id', 'phone' => '081234567893'],

            // CV Sinar Harapan Jaya (2)
            ['first_name' => 'Andi', 'last_name' => 'Pratama', 'company_id' => 2, 'email' => 'andi.pratama@sinarharapan.com', 'phone' => '082345678901'],
            ['first_name' => 'Rina', 'last_name' => 'Wulandari', 'company_id' => 2, 'email' => 'rina.wulandari@sinarharapan.com', 'phone' => '082345678902'],
            ['first_name' => 'Hendra', 'last_name' => 'Gunawan', 'company_id' => 2, 'email' => 'hendra.gunawan@sinarharapan.com', 'phone' => '082345678903'],

            // PT Teknologi Nusantara (3)
            ['first_name' => 'Rizky', 'last_name' => 'Hidayat', 'company_id' => 3, 'email' => 'rizky.hidayat@teknusa.id', 'phone' => '083456789012'],
            ['first_name' => 'Maya', 'last_name' => 'Putri', 'company_id' => 3, 'email' => 'maya.putri@teknusa.id', 'phone' => '083456789013'],
            ['first_name' => 'Fajar', 'last_name' => 'Nugroho', 'company_id' => 3, 'email' => 'fajar.nugroho@teknusa.id', 'phone' => '083456789014'],
            ['first_name' => 'Indah', 'last_name' => 'Permata', 'company_id' => 3, 'email' => 'indah.permata@teknusa.id', 'phone' => '083456789015'],
            ['first_name' => 'Dimas', 'last_name' => 'Saputra', 'company_id' => 3, 'email' => 'dimas.saputra@teknusa.id', 'phone' => '083456789016'],

            // Syarikat Gemilang Sdn Bhd (4)
            ['first_name' => 'Ahmad', 'last_name' => 'bin Ibrahim', 'company_id' => 4, 'email' => 'ahmad.ibrahim@gemilang.com.my', 'phone' => '+60123456789'],
            ['first_name' => 'Nurul', 'last_name' => 'Aisyah', 'company_id' => 4, 'email' => 'nurul.aisyah@gemilang.com.my', 'phone' => '+60123456790'],
            ['first_name' => 'Mohd', 'last_name' => 'Hafiz', 'company_id' => 4, 'email' => 'mohd.hafiz@gemilang.com.my', 'phone' => '+60123456791'],
            ['first_name' => 'Siti', 'last_name' => 'Aminah', 'company_id' => 4, 'email' => 'siti.aminah@gemilang.com.my', 'phone' => '+60123456792'],

            // PT Karya Digital Mandiri (5)
            ['first_name' => 'Yoga', 'last_name' => 'Hermawan', 'company_id' => 5, 'email' => 'yoga.hermawan@karyadigital.id', 'phone' => '085678901234'],
            ['first_name' => 'Ratna', 'last_name' => 'Sari', 'company_id' => 5, 'email' => 'ratna.sari@karyadigital.id', 'phone' => '085678901235'],
            ['first_name' => 'Bayu', 'last_name' => 'Pradana', 'company_id' => 5, 'email' => 'bayu.pradana@karyadigital.id', 'phone' => '085678901236'],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}
