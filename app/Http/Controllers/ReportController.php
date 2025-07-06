<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\TimeEntry;
use App\Models\Project;
use App\Models\Client;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:export-reports');
    }

    /**
     * Display reports dashboard
     */
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    /**
     * Timesheet report
     */
    public function timesheet(Request $request)
    {
        $filters = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'user_id' => 'nullable|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'client_id' => 'nullable|exists:clients,id',
            'billable_only' => 'boolean',
        ]);

        // Default date range (current month)
        $startDate = $filters['start_date'] ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? Carbon::now()->endOfMonth()->toDateString();

        $query = TimeEntry::with(['user', 'project.client'])
            ->whereBetween('date', [$startDate, $endDate]);

        // Apply filters
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['client_id'])) {
            $query->whereHas('project', function($q) use ($filters) {
                $q->where('client_id', $filters['client_id']);
            });
        }

        if (!empty($filters['billable_only'])) {
            $query->where('is_billable', true);
        }

        $timeEntries = $query->orderBy('date', 'desc')
            ->orderBy('start_time', 'desc')
            ->paginate(50)
            ->withQueryString();

        // Calculate summary statistics
        $summaryQuery = clone $query;
        $summaryData = $summaryQuery->selectRaw('
            SUM(duration_minutes) as total_minutes,
            SUM(CASE WHEN is_billable = 1 THEN duration_minutes ELSE 0 END) as billable_minutes,
            COUNT(*) as total_entries,
            COUNT(DISTINCT user_id) as unique_users,
            COUNT(DISTINCT project_id) as unique_projects
        ')->first();

        $summary = [
            'total_hours' => round($summaryData->total_minutes / 60, 2),
            'billable_hours' => round($summaryData->billable_minutes / 60, 2),
            'total_entries' => $summaryData->total_entries,
            'unique_users' => $summaryData->unique_users,
            'unique_projects' => $summaryData->unique_projects,
        ];

        // Calculate total revenue
        $revenueQuery = clone $query;
        $revenueEntries = $revenueQuery->where('is_billable', true)->get();
        $summary['total_revenue'] = $revenueEntries->sum(function($entry) {
            $rate = $entry->hourly_rate ?? $entry->project->hourly_rate ?? $entry->user->hourly_rate ?? 0;
            return ($entry->duration_minutes / 60) * $rate;
        });

        // Get filter options
        $users = User::where('is_active', true)->select('id', 'name')->get();
        $projects = Project::select('id', 'name')->get();
        $clients = Client::where('is_active', true)->select('id', 'name')->get();

        return Inertia::render('Reports/Timesheet', [
            'timeEntries' => $timeEntries,
            'summary' => $summary,
            'filters' => array_merge($filters, [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]),
            'filterOptions' => [
                'users' => $users,
                'projects' => $projects,
                'clients' => $clients,
            ]
        ]);
    }

    /**
     * Export timesheet report
     */
    public function exportTimesheet(Request $request)
    {
        $filters = $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'user_id' => 'nullable|exists:users,id',
            'project_id' => 'nullable|exists:projects,id',
            'client_id' => 'nullable|exists:clients,id',
            'billable_only' => 'boolean',
            'format' => 'required|in:csv,pdf',
        ]);

        // Default date range
        $startDate = $filters['start_date'] ?? Carbon::now()->startOfMonth()->toDateString();
        $endDate = $filters['end_date'] ?? Carbon::now()->endOfMonth()->toDateString();

        $query = TimeEntry::with(['user', 'project.client'])
            ->whereBetween('date', [$startDate, $endDate]);

        // Apply filters (same as timesheet method)
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }

        if (!empty($filters['client_id'])) {
            $query->whereHas('project', function($q) use ($filters) {
                $q->where('client_id', $filters['client_id']);
            });
        }

        if (!empty($filters['billable_only'])) {
            $query->where('is_billable', true);
        }

        $timeEntries = $query->orderBy('date', 'desc')->get();

        $filename = 'timesheet_' . $startDate . '_to_' . $endDate;

        if ($filters['format'] === 'csv') {
            return $this->exportTimesheetCSV($timeEntries, $filename);
        } else {
            return $this->exportTimesheetPDF($timeEntries, $filename, $filters);
        }
    }

    /**
     * Project summary report
     */
    public function projectSummary(Request $request)
    {
        $filters = $request->validate([
            'status' => 'nullable|in:planning,active,on_hold,completed,cancelled',
            'client_id' => 'nullable|exists:clients,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = Project::with(['client', 'team.members', 'timeEntries']);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->whereBetween('start_date', [$filters['start_date'], $filters['end_date']]);
        }

        $projects = $query->get()->map(function($project) {
            $timeEntries = $project->timeEntries;
            $totalHours = $timeEntries->sum('duration_minutes') / 60;
            $billableHours = $timeEntries->where('is_billable', true)->sum('duration_minutes') / 60;
            
            $totalRevenue = $timeEntries->where('is_billable', true)->sum(function($entry) use ($project) {
                $rate = $entry->hourly_rate ?? $project->hourly_rate ?? $entry->user->hourly_rate ?? 0;
                return ($entry->duration_minutes / 60) * $rate;
            });

            return [
                'id' => $project->id,
                'name' => $project->name,
                'client' => $project->client->name,
                'status' => $project->status,
                'priority' => $project->priority,
                'budget' => $project->budget,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'team_size' => $project->team ? $project->team->members->count() : 0,
                'total_hours' => round($totalHours, 2),
                'billable_hours' => round($billableHours, 2),
                'total_revenue' => round($totalRevenue, 2),
                'budget_utilization' => $project->budget > 0 ? round(($totalRevenue / $project->budget) * 100, 2) : 0,
            ];
        });

        $clients = Client::where('is_active', true)->select('id', 'name')->get();

        return Inertia::render('Reports/ProjectSummary', [
            'projects' => $projects,
            'filters' => $filters,
            'filterOptions' => [
                'clients' => $clients,
            ]
        ]);
    }

    /**
     * Export timesheet as CSV
     */
    private function exportTimesheetCSV($timeEntries, $filename)
    {
        $headers = [
            'Date',
            'User',
            'Project',
            'Client',
            'Task',
            'Description',
            'Start Time',
            'End Time',
            'Duration (Hours)',
            'Billable',
            'Hourly Rate',
            'Total Cost'
        ];

        $data = $timeEntries->map(function($entry) {
            $rate = $entry->hourly_rate ?? $entry->project->hourly_rate ?? $entry->user->hourly_rate ?? 0;
            $cost = $entry->is_billable ? ($entry->duration_minutes / 60) * $rate : 0;

            return [
                $entry->date,
                $entry->user->name,
                $entry->project->name,
                $entry->project->client->name,
                $entry->task_name,
                $entry->description,
                $entry->start_time,
                $entry->end_time,
                round($entry->duration_minutes / 60, 2),
                $entry->is_billable ? 'Yes' : 'No',
                $rate,
                round($cost, 2),
            ];
        });

        $csvContent = implode(',', $headers) . "\n";
        foreach ($data as $row) {
            $csvContent .= implode(',', array_map(function($field) {
                return '"' . str_replace('"', '""', $field) . '"';
            }, $row)) . "\n";
        }

        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.csv"');
    }

    /**
     * Export timesheet as PDF
     */
    private function exportTimesheetPDF($timeEntries, $filename, $filters)
    {
        $summary = [
            'total_hours' => round($timeEntries->sum('duration_minutes') / 60, 2),
            'billable_hours' => round($timeEntries->where('is_billable', true)->sum('duration_minutes') / 60, 2),
            'total_entries' => $timeEntries->count(),
            'date_range' => $filters['start_date'] . ' to ' . $filters['end_date'],
        ];

        $pdf = Pdf::loadView('reports.timesheet-pdf', [
            'timeEntries' => $timeEntries,
            'summary' => $summary,
            'filters' => $filters,
        ]);

        return $pdf->download($filename . '.pdf');
    }
}
