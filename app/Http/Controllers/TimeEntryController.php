<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\TimeEntry;
use App\Models\Project;
use Carbon\Carbon;

class TimeEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->get('search');
        $project = $request->get('project');
        $date = $request->get('date');
        $view = $request->get('view', 'daily'); // daily, weekly, monthly
        
        $query = TimeEntry::with(['user', 'project']);
        
        // Apply role-based filtering
        if (!$user->can('view-all-time-entries')) {
            $query->where('user_id', $user->id);
        }
        
        // Apply filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('task_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        if ($project) {
            $query->where('project_id', $project);
        }
        
        if ($date) {
            $query->whereDate('date', $date);
        } else {
            // Default to current week
            $query->whereBetween('date', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ]);
        }
        
        $timeEntries = $query->latest('date')
            ->latest('start_time')
            ->paginate(20)
            ->withQueryString();
        
        // Get projects for filter dropdown
        $projects = $user->can('view-all-time-entries') 
            ? Project::select('id', 'name')->get()
            : Project::whereIn('id', $user->teams()->pluck('project_id'))->select('id', 'name')->get();
        
        // Calculate totals
        $totalHours = $query->sum('duration_minutes') / 60;
        $billableHours = $query->where('is_billable', true)->sum('duration_minutes') / 60;
        
        return Inertia::render('TimeEntries/Index', [
            'timeEntries' => $timeEntries,
            'projects' => $projects,
            'filters' => [
                'search' => $search,
                'project' => $project,
                'date' => $date,
                'view' => $view,
            ],
            'stats' => [
                'total_hours' => round($totalHours, 2),
                'billable_hours' => round($billableHours, 2),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        
        $projects = $user->can('view-all-time-entries') 
            ? Project::select('id', 'name')->get()
            : Project::whereIn('id', $user->teams()->pluck('project_id'))->select('id', 'name')->get();
        
        return Inertia::render('TimeEntries/Create', [
            'projects' => $projects,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_billable' => 'boolean',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        $validated['user_id'] = auth()->id();
        
        // Calculate duration
        $start = Carbon::createFromFormat('H:i', $validated['start_time']);
        $end = Carbon::createFromFormat('H:i', $validated['end_time']);
        $validated['duration_minutes'] = $end->diffInMinutes($start);

        TimeEntry::create($validated);

        return redirect()->route('time-entries.index')
            ->with('success', 'Time entry created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TimeEntry $timeEntry)
    {
        $this->authorize('view', $timeEntry);
        
        $timeEntry->load(['user', 'project']);
        
        return Inertia::render('TimeEntries/Show', [
            'timeEntry' => $timeEntry,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);
        
        $user = auth()->user();
        
        $projects = $user->can('view-all-time-entries') 
            ? Project::select('id', 'name')->get()
            : Project::whereIn('id', $user->teams()->pluck('project_id'))->select('id', 'name')->get();
        
        return Inertia::render('TimeEntries/Edit', [
            'timeEntry' => $timeEntry,
            'projects' => $projects,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);
        
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_billable' => 'boolean',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        // Calculate duration
        $start = Carbon::createFromFormat('H:i', $validated['start_time']);
        $end = Carbon::createFromFormat('H:i', $validated['end_time']);
        $validated['duration_minutes'] = $end->diffInMinutes($start);

        $timeEntry->update($validated);

        return redirect()->route('time-entries.index')
            ->with('success', 'Time entry updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeEntry $timeEntry)
    {
        $this->authorize('delete', $timeEntry);
        
        $timeEntry->delete();

        return redirect()->route('time-entries.index')
            ->with('success', 'Time entry deleted successfully.');
    }

    /**
     * Start a timer for a new time entry
     */
    public function startTimer(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'task_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['date'] = now()->toDateString();
        $validated['start_time'] = now()->format('H:i:s');
        $validated['is_billable'] = true;

        $timeEntry = TimeEntry::create($validated);

        return response()->json([
            'success' => true,
            'time_entry' => $timeEntry,
            'message' => 'Timer started successfully.'
        ]);
    }

    /**
     * Stop the running timer
     */
    public function stopTimer(Request $request)
    {
        $timeEntry = TimeEntry::where('user_id', auth()->id())
            ->whereNull('end_time')
            ->latest()
            ->first();

        if (!$timeEntry) {
            return response()->json([
                'success' => false,
                'message' => 'No running timer found.'
            ], 404);
        }

        $timeEntry->update([
            'end_time' => now()->format('H:i:s'),
            'duration_minutes' => now()->diffInMinutes(
                Carbon::createFromFormat('H:i:s', $timeEntry->start_time)
            )
        ]);

        return response()->json([
            'success' => true,
            'time_entry' => $timeEntry->fresh(),
            'message' => 'Timer stopped successfully.'
        ]);
    }
}
