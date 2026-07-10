<?php

namespace Database\Seeders;

use Carbon\Carbon;
use App\Models\Notice;
use Illuminate\Support\Str;
use App\Models\NoticeCategory;
use Illuminate\Database\Seeder;

class NoticesTableSeeder extends Seeder
{
    public function run()
    {
        $faker = \Faker\Factory::create();

        $statuses = ['published', 'draft', 'scheduled'];
        $visibilities = ['customers', 'staffs', 'both'];
        $categories = NoticeCategory::active()->pluck('id')->toArray();
        // Generate 100 notices
        for ($i = 1; $i <= 100; $i++) {
            $status = $faker->randomElement($statuses);
            $publishAt = null;
            
            if ($status === 'scheduled') {
                $publishAt = $faker->dateTimeBetween('+1 day', '+1 month');
            } elseif ($status === 'published') {
                $publishAt = $faker->dateTimeBetween('-1 month', 'now');
            }

            $title = $this->generateNoticeTitle($faker);
            $content = $this->generateNoticeContent($faker);

            Notice::create([
                'notice_category_id' => $faker->randomElement($categories),
                'title' => $title,
                'content' => $content,
                'status' => $status,
                'publish_at' => $publishAt,
                'visibility' => $faker->randomElement($visibilities),
                'created_at' => $faker->dateTimeBetween('-2 months', 'now'),
                'updated_at' => $faker->dateTimeBetween('-2 months', 'now'),
            ]);
        }
    }

    private function generateNoticeTitle($faker)
    {
        $formats = [
            'Important: {noun} {action}',
            '{adjective} {noun} Announcement',
            '{department} {noun} Update',
            'Notice: {action} {noun}',
            'Urgent: {noun} {action} Required',
            '{timeframe} {noun} Maintenance',
            '{department} System {noun}',
            '{adjective} Notice Regarding {noun}'
        ];

        $replacements = [
            '{noun}' => $faker->randomElement([
                'System', 'Policy', 'Security', 'Server', 'Network', 
                'Database', 'Application', 'Service', 'Platform', 'Website'
            ]),
            '{action}' => $faker->randomElement([
                'Update', 'Maintenance', 'Change', 'Upgrade', 'Migration',
                'Enhancement', 'Modification', 'Implementation', 'Schedule'
            ]),
            '{adjective}' => $faker->randomElement([
                'Important', 'Critical', 'Urgent', 'General', 'Special',
                'Quarterly', 'Annual', 'Monthly', 'Weekly'
            ]),
            '{department}' => $faker->randomElement([
                'IT', 'HR', 'Finance', 'Operations', 'Customer Service',
                'Marketing', 'Sales', 'Development', 'Administration'
            ]),
            '{timeframe}' => $faker->randomElement([
                'Scheduled', 'Planned', 'Emergency', 'Ongoing', 'Immediate',
                'Upcoming', 'Regular', 'Periodic'
            ])
        ];

        $format = $faker->randomElement($formats);
        
        return Str::title(str_replace(
            array_keys($replacements),
            array_values($replacements),
            $format
        ));
    }

    private function generateNoticeContent($faker)
    {
        $paragraphs = [];
        
        // First paragraph - main announcement
        $paragraphs[] = $faker->randomElement([
            "We would like to inform you about important changes regarding our {system}.", 
            "Please be advised that we will be performing {maintenance} on our {system}.",
            "This notice serves to inform you about upcoming {changes} to our {system}.",
            "Attention all {users}: there will be significant {updates} to our {system}.",
            "We are pleased to announce {improvements} to our {system}."
        ]);

        // Second paragraph - details
        $paragraphs[] = $faker->randomElement([
            "The {action} is scheduled to take place on {date}. During this time, {impact}.",
            "This {change} will {benefit} and is expected to last approximately {duration}.",
            "The {update} includes {features} and will require {requirements} from all {users}.",
            "Please note that {restrictions} will be in effect during the {period}.",
            "This {modification} is part of our ongoing efforts to {goal} for our {users}."
        ]);

        // Third paragraph - instructions
        $paragraphs[] = $faker->randomElement([
            "All {users} are required to {action} before {deadline}. Please contact {department} with any questions.",
            "We recommend that all {users} {preparation} prior to the {event}. For assistance, please {contact}.",
            "No action is required from {users} at this time. We will provide further updates via {channel}.",
            "Please ensure you have completed {tasks} by {deadline}. Failure to comply may result in {consequence}.",
            "For more information about these {changes}, please visit {resource} or contact {support}."
        ]);

        // Replace placeholders
        $replacements = [
            '{system}' => $faker->randomElement(['customer portal', 'employee dashboard', 'main platform', 'service application']),
            '{maintenance}' => $faker->randomElement(['scheduled maintenance', 'system upgrades', 'critical updates', 'performance improvements']),
            '{changes}' => $faker->randomElement(['modifications', 'enhancements', 'updates', 'changes']),
            '{users}' => $faker->randomElement(['customers', 'employees', 'team members', 'users']),
            '{updates}' => $faker->randomElement(['changes', 'modifications', 'upgrades', 'enhancements']),
            '{improvements}' => $faker->randomElement(['new features', 'performance improvements', 'security enhancements', 'usability upgrades']),
            '{action}' => $faker->randomElement(['review the changes', 'update your settings', 'acknowledge this notice', 'complete the required steps']),
            '{date}' => $faker->randomElement(['Monday, '.$faker->dateTimeBetween('+1 week', '+1 month')->format('F j'), 'the scheduled date of '.$faker->dateTimeBetween('+1 week', '+1 month')->format('M j, Y'), 'the upcoming '.$faker->dayOfMonth($faker->numberBetween(1, 28)).' of next month']),
            '{impact}' => $faker->randomElement(['the system may be unavailable', 'you may experience intermittent connectivity', 'some features will be temporarily disabled', 'all services will remain operational']),
            '{change}' => $faker->randomElement(['update', 'modification', 'enhancement', 'improvement']),
            '{benefit}' => $faker->randomElement(['improve system performance', 'enhance security measures', 'provide new functionality', 'resolve existing issues']),
            '{duration}' => $faker->randomElement(['2 hours', '30 minutes', '4 hours', 'the entire day']),
            '{update}' => $faker->randomElement(['release', 'version', 'patch', 'upgrade']),
            '{features}' => $faker->randomElement(['new security protocols', 'updated interfaces', 'additional functionality', 'performance optimizations']),
            '{requirements}' => $faker->randomElement(['password resets', 'profile updates', 'system reboots', 'application restarts']),
            '{restrictions}' => $faker->randomElement(['access limitations', 'usage constraints', 'temporary blocks', 'reduced functionality']),
            '{period}' => $faker->randomElement(['maintenance window', 'update process', 'transition period', 'implementation phase']),
            '{modification}' => $faker->randomElement(['change', 'update', 'enhancement', 'improvement']),
            '{goal}' => $faker->randomElement(['improve service quality', 'enhance user experience', 'increase system reliability', 'provide better support']),
            '{preparation}' => $faker->randomElement(['save your work', 'backup your data', 'review the documentation', 'test your connections']),
            '{event}' => $faker->randomElement(['implementation', 'rollout', 'transition', 'upgrade']),
            '{contact}' => $faker->randomElement(['reach out to your manager', 'open a support ticket', 'email the help desk', 'call IT support']),
            '{tasks}' => $faker->randomElement(['the required training', 'your profile updates', 'the acknowledgment process', 'the necessary preparations']),
            '{deadline}' => $faker->randomElement(['Friday', 'the end of the week', 'the 15th', 'next Monday']),
            '{consequence}' => $faker->randomElement(['service interruptions', 'reduced access', 'delayed processing', 'additional requirements']),
            '{channel}' => $faker->randomElement(['email', 'the company portal', 'team meetings', 'announcement boards']),
            '{resource}' => $faker->randomElement(['our documentation site', 'the help center', 'the employee portal', 'the customer dashboard']),
            '{support}' => $faker->randomElement(['our help desk', 'your account manager', 'technical support', 'customer service']),
            '{department}' => $faker->randomElement(['IT department', 'HR team', 'customer support', 'your supervisor'])
        ];

        foreach ($paragraphs as &$paragraph) {
            $paragraph = str_replace(
                array_keys($replacements),
                array_values($replacements),
                $paragraph
            );
        }

        // Add a closing paragraph
        $paragraphs[] = "\n\nThank you for your attention to this matter.";

        return implode("\n\n", $paragraphs);
    }
}