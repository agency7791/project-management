<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Project;
use App\Models\Client;
use App\Models\Team;
use App\Models\User;
use App\Models\TeamMember;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->get('search');
        $status = $request->get('status');
        $client = $request->get('client');
        $priority = $request->get('priority');
        
        $query = Project::with(['client', 'team.members.user']);
        
        // Apply role-based filtering
        if (!$user->can('manage-projects')) {
            $projectIds = $user->teams()->pluck('project_id');
            $query->whereIn('id', $projectIds);
        }
        
        // Apply filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        if ($client) {
            $query->where('client_id', $client);
        }
        
        if ($priority) {
            $query->where('priority', $priority);
        }
        
        $projects = $query->latest()->paginate(12)->withQueryString();
        
        // Get filter options
        $clients = $user->can('manage-projects') 
            ? Client::select('id', 'name')->get()
            : Client::whereIn('id', $query->pluck('client_id'))->select('id', 'name')->get();
        
        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'clients' => $clients,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'client' => $client,
                'priority' => $priority,
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Project::class);
        
        $clients = Client::where('is_active', true)->select('id', 'name')->get();
        
        return Inertia::render('Projects/Create', [
            'clients' => $clients,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Project::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        $project = Project::create($validated);
        
        // Create default team for the project
        $team = Team::create([
            'name' => $project->name . ' Team',
            'description' => 'Default team for ' . $project->name,
            'project_id' => $project->id,
        ]);

        return redirect()->route('projects.index')
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);
        
        $project->load([
            'client',
            'team.members.user',
            'timeEntries' => function($query) {
                $query->with('user')->latest()->limit(10);
            },
            'chatRooms' => function($query) {
                $query->with('latestMessage.user');
            },
            'files' => function($query) {
                $query->with('uploader')->latest();
            }
        ]);
        
        // Calculate project statistics
        $stats = [
            'total_hours' => $project->timeEntries()->sum('duration_minutes') / 60,
            'billable_hours' => $project->timeEntries()->where('is_billable', true)->sum('duration_minutes') / 60,
            'team_members' => $project->team ? $project->team->members()->count() : 0,
            'files_count' => $project->files()->count(),
        ];
        
        return Inertia::render('Projects/Show', [
            'project' => $project,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $this->authorize('update', $project);
        
        $clients = Client::where('is_active', true)->select('id', 'name')->get();
        
        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'clients' => $clients,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'client_id' => 'required|exists:clients,id',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
            'priority' => 'required|in:low,medium,high,urgent',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'budget' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        $project->update($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);
        
        // Check if project has time entries
        if ($project->timeEntries()->exists()) {
            return back()->with('error', 'Cannot delete project with time entries.');
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    /**
     * Assign team to project
     */
    public function assignTeam(Request $request, Project $project)
    {
        $this->authorize('update', $project);
        
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'roles' => 'required|array',
            'roles.*' => 'in:lead,member',
        ]);

        $team = $project->team;
        if (!$team) {
            $team = Team::create([
                'name' => $project->name . ' Team',
                'description' => 'Team for ' . $project->name,
                'project_id' => $project->id,
            ]);
        }

        // Remove existing members
        $team->members()->delete();

        // Add new members
        foreach ($validated['user_ids'] as $index => $userId) {
            TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $userId,
                'role' => $validated['roles'][$index] ?? 'member',
                'joined_at' => now(),
            ]);
        }

        return back()->with('success', 'Team assigned successfully.');
    }
}
