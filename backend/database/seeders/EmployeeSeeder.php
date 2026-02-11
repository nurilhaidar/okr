<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstNames = [
            'James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda',
            'William', 'Elizabeth', 'David', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Jessica',
            'Thomas', 'Sarah', 'Charles', 'Karen', 'Christopher', 'Nancy', 'Daniel', 'Lisa',
            'Matthew', 'Betty', 'Anthony', 'Margaret', 'Mark', 'Sandra', 'Donald', 'Ashley',
            'Steven', 'Dorothy', 'Paul', 'Emily', 'Andrew', 'Donna', 'Joshua', 'Michelle',
            'Kenneth', 'Carol', 'Kevin', 'Amanda', 'Brian', 'Melissa', 'George', 'Deborah',
            'Edward', 'Stephanie'
        ];

        $lastNames = [
            'Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis',
            'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson',
            'Thomas', 'Taylor', 'Moore', 'Jackson', 'Martin', 'Lee', 'Perez', 'Thompson',
            'White', 'Harris', 'Sanchez', 'Clark', 'Ramirez', 'Lewis', 'Robinson', 'Walker',
            'Young', 'Allen', 'King', 'Wright', 'Scott', 'Torres', 'Nguyen', 'Hill',
            'Flores', 'Green', 'Adams', 'Nelson', 'Baker', 'Hall', 'Rivera', 'Campbell',
            'Mitchell', 'Carter', 'Roberts'
        ];

        $positions = [
            'Software Engineer',
            'Senior Software Engineer',
            'Lead Software Engineer',
            'Project Manager',
            'Product Manager',
            'UX Designer',
            'UI Designer',
            'Data Analyst',
            'Data Scientist',
            'DevOps Engineer',
            'QA Engineer',
            'Business Analyst',
            'HR Manager',
            'HR Specialist',
            'Marketing Manager',
            'Sales Representative',
            'Account Manager',
            'Finance Manager',
            'Accountant',
            'Operations Manager',
            'Team Lead',
            'Scrum Master',
            'Technical Writer',
            'System Administrator',
            'Network Engineer',
            'Security Engineer',
            'Cloud Architect',
            'Mobile Developer',
            'Frontend Developer',
            'Backend Developer',
            'Full Stack Developer',
            'Machine Learning Engineer',
            'Research Scientist',
            'Content Writer',
            'Graphic Designer',
            'Customer Success Manager',
            'Support Engineer',
            'Training Specialist',
            'Compliance Officer',
            'Legal Counsel',
            'Procurement Manager',
            'Logistics Coordinator',
            'Supply Chain Manager',
            'Product Owner',
            'Program Manager',
            'Portfolio Manager',
            'Change Manager',
            'Agile Coach',
            'Quality Assurance Manager',
            'Release Manager',
            'Site Reliability Engineer'
        ];

        $employees = [];

        // Admin user (first employee)
        $employees[] = [
            'name' => 'Admin User',
            'email' => 'admin@okr.com',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'position' => 'Chief Technology Officer',
            'role_id' => 1,
            'is_active' => true,
        ];

        // Generate 49 more employees
        for ($i = 1; $i < 50; $i++) {
            $firstName = $firstNames[$i % count($firstNames)];
            $lastName = $lastNames[$i % count($lastNames)];
            $position = $positions[$i % count($positions)];

            $employees[] = [
                'name' => "$firstName $lastName",
                'email' => strtolower($firstName) . '.' . strtolower($lastName) . ($i > 25 ? $i : '') . '@okr.com',
                'username' => strtolower($firstName[0] . $lastName) . ($i > 25 ? $i : ''),
                'password' => Hash::make('password'),
                'position' => $position,
                'role_id' => rand(2, 5), // Random role between Manager and Director
                'is_active' => true,
            ];
        }

        foreach ($employees as $employee) {
            DB::table('employee')->insert(array_merge($employee, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
