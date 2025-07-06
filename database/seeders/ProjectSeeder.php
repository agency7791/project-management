<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Client;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\ChatRoom;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();
        $users = User::where('role', '!=', 'admin')->get();

        $projects = [
            [
                'name' => 'E-commerce Platform Redesign',
                'description' => 'Complete redesign and development of the client\'s e-commerce platform with modern UI/UX and enhanced functionality.',
                'client_id' => $clients->first()->id,
                'status' => 'active',
                'priority' => 'high',
                'start_date' => Carbon::now()->subDays(30),
                'end_date' => Carbon::now()->addDays(60),
                'budget' => 50000.00,
                'hourly_rate' => 75.00,
                'notes' => 'Focus on mobile responsiveness and performance optimization.',
            ],
            [
                'name' => 'Mobile App Development',
                'description' => 'Native mobile application for iOS and Android platforms with real-time features.',
                'client_id' => $clients->skip(1)->first()->id,
                'status' => 'planning',
                'priority' => 'medium',
                'start_date' => Carbon::now()->addDays(15),
                'end_date' => Carbon::now()->addDays(120),
                'budget' => 75000.00,
                'hourly_rate' => 80.00,
                'notes' => 'Requires integration with existing API and push notifications.',
            ],
            [
                'name' => 'Website Maintenance',
                'description' => 'Ongoing maintenance and updates for the client\'s corporate website.',
                'client_id' => $clients->skip(2)->first()->id,
                'status' => 'active',
                'priority' => 'low',
                'start_date' => Carbon::now()->subDays(60),
                'end_date' => Carbon::now()->addDays(300),
                'budget' => 15000.00,
                'hourly_rate' => 60.00,
                'notes' => 'Monthly updates and security patches required.',
            ],
            [
                'name' => 'CRM System Integration',
                'description' => 'Integration of third-party CRM system with existing business processes.',
                'client_id' => $clients->skip(3)->first()->id,
                'status' => 'completed',
                'priority' => 'high',
                'start_date' => Carbon::now()->subDays(90),
                'end_date' => Carbon::now()->subDays(10),
                'budget' => 35000.00,
                'hourly_rate' => 85.00,
                'notes' => 'Successfully completed with full data migration.',
            ],
            [
                'name' => 'Digital Marketing Platform',
                'description' => 'Custom digital marketing platform with analytics and campaign management.',
                'client_id' => $clients->skip(4)->first()->id,
                'status' => 'on_hold',
                'priority' => 'medium',
                'start_date' => Carbon::now()->addDays(30),
                'end_date' => Carbon::now()->addDays(150),
                'budget' => 40000.00,
                'hourly_rate' => 70.00,
                'notes' => 'Waiting for client approval on final requirements.',
            ],
        ];

        foreach ($projects as $projectData) {
            $project = Project::create($projectData);

            // Create a team for each project
            $team = Team::create([
                'name' => $project->name . ' Team',
                'description' => 'Development team for ' . $project->name,
                'project_id' => $project->id,
            ]);

            // Assign random team members
            $teamUsers = $users->random(rand(2, 4));
            foreach ($teamUsers as $index => $user) {
                TeamMember::create([
                    'team_id' => $team->id,
                    'user_id' => $user->id,
                    'role' => $index === 0 ? 'lead' : 'member',
                    'joined_at' => Carbon::now()->subDays(rand(1, 30)),
                ]);
            }

            // Create a chat room for each project
            ChatRoom::create([
                'name' => $project->name . ' Discussion',
                'description' => 'Main discussion room for ' . $project->name,
                'project_id' => $project->id,
                'is_private' => false,
            ]);
        }
    }
}
