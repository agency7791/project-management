<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Project;
use App\Models\Client;
use App\Models\TimeEntry;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get filter parameters
        $projectFilter = $request->get('project');
        $clientFilter = $request->get('client');
        $userFilter = $request->get('user');
        $dateFilter = $request->get('date', 'this_month');
        
        // Calculate date range based on filter
        $dateRange = $this->getDateRange($dateFilter);
        
        // Base queries
        $projectsQuery = Project::with('client');
        $timeEntriesQuery = TimeEntry::with(['user', 'project']);
        
        // Apply filters based on user role
        if (!$user->isAdmin()) {
            if ($user->isManager()) {
                // Managers can see projects they're assigned to
                $projectIds = $user->teams()->pluck('project_id');
                $projectsQuery->whereIn('id', $projectIds);
                $timeEntriesQuery->whereIn('project_id', $projectIds);
            } else {
                // Staff can only see their own time entries and assigned projects
                $projectIds = $user->teams()->pluck('project_id');
                $projectsQuery->whereIn('id', $projectIds);
                $timeEntriesQuery->where('user_id', $user->id);
            }
        }
        
        // Apply additional filters
        if ($projectFilter) {
            $timeEntriesQuery->where('project_id', $projectFilter);
        }
        
        if ($clientFilter) {
            $projectsQuery->where('client_id', $clientFilter);
            $timeEntriesQuery->whereHas('project', function($q) use ($clientFilter) {
                $q->where('client_id', $clientFilter);
            });
        }
        
        if ($userFilter && $user->canManageProjects()) {
            $timeEntriesQuery->where('user_id', $userFilter);
        }
        
        // Apply date filter to time entries
        $timeEntriesQuery->whereBetween('date', $dateRange);
        
        // Get dashboard data
        $stats = [
            'total_projects' => $projectsQuery->count(),
            'active_projects' => $projectsQuery->where('status', 'active')->count(),
            'completed_projects' => $projectsQuery->where('status', 'completed')->count(),
            'total_clients' => $user->isAdmin() ? Client::count() : $projectsQuery->distinct('client_id')->count(),
        ];
        
        // Time tracking stats
        $timeEntries = $timeEntriesQuery->get();
        $stats['total_hours'] = round($timeEntries->sum('duration_minutes') / 60, 2);
        $stats['billable_hours'] = round($timeEntries->where('is_billable', true)->sum('duration_minutes') / 60, 2);
        $stats['total_revenue'] = $timeEntries->where('is_billable', true)->sum(function($entry) {
            $rate = $entry->hourly_rate ?? $entry->project->hourly_rate ?? $entry->user->hourly_rate ?? 0;
            return ($entry->duration_minutes / 60) * $rate;
        });
        
        // Recent projects
        $recentProjects = $projectsQuery->latest()->limit(5)->get();
        
        // Recent time entries
        $recentTimeEntries = $timeEntriesQuery->latest()->limit(10)->get();
        
        // Time entries by day (for chart)
        $dailyHours = $timeEntriesQuery->selectRaw('DATE(date) as date, SUM(duration_minutes) as total_minutes')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function($item) {
                return [
                    'date' => $item->date,
                    'hours' => round($item->total_minutes / 60, 2)
                ];
            });
        
        // Project status distribution
        $projectStatusData = $projectsQuery->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');
        
        // Filter options for dropdowns
        $filterOptions = [
            'projects' => $user->canManageProjects() ? Project::select('id', 'name')->get() : 
                         Project::whereIn('id', $user->teams()->pluck('project_id'))->select('id', 'name')->get(),
            'clients' => $user->isAdmin() ? Client::select('id', 'name')->get() : 
                        Client::whereIn('id', $projectsQuery->pluck('client_id'))->select('id', 'name')->get(),
            'users' => $user->canManageProjects() ? User::where('is_active', true)->select('id', 'name')->get() : collect(),
        ];
        
        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentProjects' => $recentProjects,
            'recentTimeEntries' => $recentTimeEntries,
            'dailyHours' => $dailyHours,
            'projectStatusData' => $projectStatusData,
            'filterOptions' => $filterOptions,
            'filters' => [
                'project' => $projectFilter,
                'client' => $clientFilter,
                'user' => $userFilter,
                'date' => $dateFilter,
            ]
        ]);
    }
    
    private function getDateRange($filter)
    {
        $now = Carbon::now();
        
        switch ($filter) {
            case 'today':
                return [$now->startOfDay(), $now->endOfDay()];
            case 'yesterday':
                return [$now->subDay()->startOfDay(), $now->endOfDay()];
            case 'this_week':
                return [$now->startOfWeek(), $now->endOfWeek()];
            case 'last_week':
                return [$now->subWeek()->startOfWeek(), $now->endOfWeek()];
            case 'this_month':
                return [$now->startOfMonth(), $now->endOfMonth()];
            case 'last_month':
                return [$now->subMonth()->startOfMonth(), $now->endOfMonth()];
            case 'this_year':
                return [$now->startOfYear(), $now->endOfYear()];
            default:
                return [$now->startOfMonth(), $now->endOfMonth()];
        }
    }
}
