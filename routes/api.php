<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Project;
use App\Models\User;
use App\Models\TimeEntry;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['auth:sanctum'])->group(function () {
    // Projects API
    Route::get('/projects', function () {
        return Project::select('id', 'name')->get();
    });

    // Users API
    Route::get('/users', function () {
        return User::where('is_active', true)->select('id', 'name', 'role')->get();
    });

    // Quick stats for reports
    Route::get('/reports/quick-stats', function () {
        $user = auth()->user();
        
        $projectQuery = Project::query();
        if (!$user->can('manage-projects')) {
            $projectIds = $user->teams()->pluck('project_id');
            $projectQuery->whereIn('id', $projectIds);
        }
        
        $timeEntryQuery = TimeEntry::query();
        if (!$user->can('manage-projects')) {
            $timeEntryQuery->where('user_id', $user->id);
        }
        
        return [
            'total_projects' => $projectQuery->count(),
            'active_projects' => $projectQuery->where('status', 'active')->count(),
            'total_hours' => round($timeEntryQuery->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->sum('duration_minutes') / 60, 1),
            'revenue' => $timeEntryQuery->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
                ->where('is_billable', true)
                ->get()
                ->sum(function($entry) {
                    $rate = $entry->hourly_rate ?? $entry->project->hourly_rate ?? $entry->user->hourly_rate ?? 0;
                    return ($entry->duration_minutes / 60) * $rate;
                }),
        ];
    });

    // Recent activity for reports
    Route::get('/reports/recent-activity', function () {
        $user = auth()->user();
        
        $activities = collect();
        
        // Recent time entries
        $timeEntryQuery = TimeEntry::with(['user', 'project']);
        if (!$user->can('manage-projects')) {
            $timeEntryQuery->where('user_id', $user->id);
        }
        
        $recentTimeEntries = $timeEntryQuery->latest()->limit(5)->get();
        foreach ($recentTimeEntries as $entry) {
            $activities->push([
                'id' => 'time_' . $entry->id,
                'type' => 'time_entry',
                'description' => $entry->user->name . ' logged ' . round($entry->duration_minutes / 60, 1) . ' hours on ' . $entry->project->name,
                'created_at' => $entry->created_at,
            ]);
        }
        
        // Recent projects (if user can manage projects)
        if ($user->can('manage-projects')) {
            $recentProjects = Project::with('client')->latest()->limit(3)->get();
            foreach ($recentProjects as $project) {
                $activities->push([
                    'id' => 'project_' . $project->id,
                    'type' => 'project',
                    'description' => 'New project "' . $project->name . '" was created for ' . $project->client->name,
                    'created_at' => $project->created_at,
                ]);
            }
        }
        
        return $activities->sortByDesc('created_at')->take(10)->values();
    });

    // Chat messages API
    Route::get('/chat/rooms/{room}/messages', function (\App\Models\ChatRoom $room) {
        $user = auth()->user();
        
        // Check access
        if (!$user->can('manage-projects')) {
            $projectIds = $user->teams()->pluck('project_id');
            if (!$projectIds->contains($room->project_id)) {
                abort(403);
            }
        }
        
        $messages = $room->messages()
            ->with('user')
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->values();
        
        return ['messages' => $messages];
    });
});