<?php

namespace Database\Seeders;
use Faker\Factory;
use Illuminate\Support\Str;
use App\Models\NoticeCategory;
use Illuminate\Database\Seeder;

class NoticeCategorySeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();

        // Common category prefixes and suffixes
        $prefixes = ['General', 'Important', 'Company', 'Department', 'Team', 'Project', 'System', 'HR', 'IT', 'Finance'];
        $suffixes = ['Announcements', 'Updates', 'Notices', 'Alerts', 'Bulletins', 'News', 'Information', 'Directives', 'Policies', 'Changes'];

        // Specific category types
        $departmentCategories = ['Sales', 'Marketing', 'Engineering', 'Support', 'Operations', 'Administration', 'Legal', 'Product', 'Quality', 'Research'];
        $systemCategories = ['Maintenance', 'Outage', 'Upgrade', 'Security', 'Performance', 'Migration', 'Integration', 'Backup', 'Deployment', 'Configuration'];
        $hrCategories = ['Benefits', 'Payroll', 'Training', 'Recruiting', 'Compliance', 'Events', 'Recognition', 'Handbook', 'Time Off', 'Onboarding'];
        $generalCategories = ['All Staff', 'Managers', 'Executives', 'Contractors', 'Partners', 'Clients', 'Vendors', 'Public', 'Internal', 'Confidential'];

        // Combine all possible category names
        $categoryNames = array_merge(
            // Combined prefix + suffix
            array_map(fn($p) => "$p {$faker->randomElement($suffixes)}", $prefixes),
            
            // Department specific
            array_map(fn($d) => "$d Department", $departmentCategories),
            array_map(fn($d) => "$d Updates", $departmentCategories),
            
            // System specific
            array_map(fn($s) => "System $s", $systemCategories),
            array_map(fn($s) => "Tech $s", $systemCategories),
            
            // HR specific
            array_map(fn($h) => "HR $h", $hrCategories),
            array_map(fn($h) => "Employee $h", $hrCategories),
            
            // General
            $generalCategories
        );

        // Ensure uniqueness
        $categoryNames = array_unique($categoryNames);

        // Generate 100 categories
        for ($i = 0; $i < 100; $i++) {
            // If we have predefined names, use them first
            $name = $categoryNames[$i % count($categoryNames)] ?? $faker->unique()->words($faker->numberBetween(1, 3), true);
            
            // For additional categories beyond our predefined ones
            if ($i >= count($categoryNames)) {
                $name = $this->generateCategoryName($faker, $prefixes, $suffixes);
            }

            NoticeCategory::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'status' => $faker->randomElement([0,1]),
            ]);
        }
    }

    protected function generateCategoryName($faker, $prefixes, $suffixes)
    {
        $formats = [
            '{prefix} {suffix}',
            '{department} {type}',
            '{scope} {subject}',
            '{audience} {content}',
        ];

        $format = $faker->randomElement($formats);

        return Str::title(str_replace(
            [
                '{prefix}', 
                '{suffix}',
                '{department}',
                '{type}',
                '{scope}',
                '{subject}',
                '{audience}',
                '{content}'
            ],
            [
                $faker->randomElement($prefixes),
                $faker->randomElement($suffixes),
                $faker->randomElement(['Sales', 'Marketing', 'IT', 'HR', 'Finance', 'Operations']),
                $faker->randomElement(['Updates', 'News', 'Alerts', 'Notices', 'Bulletins']),
                $faker->randomElement(['Company', 'Team', 'Project', 'Department', 'Regional']),
                $faker->randomElement(['Policies', 'Changes', 'Announcements', 'Directives', 'Information']),
                $faker->randomElement(['Employee', 'Manager', 'Staff', 'Contractor', 'Vendor']),
                $faker->randomElement(['Communications', 'Materials', 'Resources', 'Documents', 'Guidelines']),
            ],
            $format
        ));
    }
}
