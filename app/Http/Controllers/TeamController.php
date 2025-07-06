<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Team;
use App\Models\User;
use App\Models\Project;
use App\Models\TeamMember;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->get('search');
        $project = $request->get('project');
        
        $query = Team::with(['project', 'members.user']);
        
        // Apply role-based filtering
        if (!$user->can('manage-projects')) {
            $teamIds = $user->teamMemberships()->pluck('team_id');
            $query->whereIn('id', $teamIds);
        }
        
        // Apply filters
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        if ($project) {
            $query->where('project_id', $project);
        }
        
        $teams = $query->latest()->paginate(12)->withQueryString();
        
        // Get filter options
        $projects = $user->can('manage-projects') 
            ? Project::select('id', 'name')->get()
            : Project::whereIn('id', $user->teams()->pluck('project_id'))->select('id', 'name')->get();
        
        return Inertia::render('Teams/Index', [
            'teams' => $teams,
            'projects' => $projects,
            'filters' => [
                'search' => $search,
                'project' => $project,
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Team::class);
        
        $projects = Project::select('id', 'name')->get();
        $users = User::where('is_active', true)->select('id', 'name', 'role')->get();
        
        return Inertia::render('Teams/Create', [
            'projects' => $projects,
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Team::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'project_id' => 'required|exists:projects,id',
            'members' => 'required|array|min:1',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.role' => 'required|in:lead,member',
        ]);

        $team = Team::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'project_id' => $validated['project_id'],
        ]);

        // Add team members
        foreach ($validated['members'] as $member) {
            TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $member['user_id'],
                'role' => $member['role'],
                'joined_at' => now(),
            ]);
        }

        return redirect()->route('teams.index')
            ->with('success', 'Team created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team)
    {
        $this->authorize('view', $team);
        
        $team->load([
            'project',
            'members.user',
            'project.timeEntries' => function($query) use ($team) {
                $query->whereIn('user_id', $team->members->pluck('user_id'))
                      ->with('user')
                      ->latest()
                      ->limit(20);
            }
        ]);
        
        // Calculate team statistics
        $memberIds = $team->members->pluck('user_id');
        $stats = [
            'total_members' => $team->members->count(),
            'leads' => $team->members->where('role', 'lead')->count(),
            'total_hours' => $team->project->timeEntries()
                ->whereIn('user_id', $memberIds)
                ->sum('duration_minutes') / 60,
            'this_week_hours' => $team->project->timeEntries()
                ->whereIn('user_id', $memberIds)
                ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
                ->sum('duration_minutes') / 60,
        ];
        
        return Inertia::render('Teams/Show', [
            'team' => $team,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Team $team)
    {
        $this->authorize('update', $team);
        
        $team->load('members.user');
        $projects = Project::select('id', 'name')->get();
        $users = User::where('is_active', true)->select('id', 'name', 'role')->get();
        
        return Inertia::render('Teams/Edit', [
            'team' => $team,
            'projects' => $projects,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Team $team)
    {
        $this->authorize('update', $team);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'project_id' => 'required|exists:projects,id',
            'members' => 'required|array|min:1',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.role' => 'required|in:lead,member',
        ]);

        $team->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'project_id' => $validated['project_id'],
        ]);

        // Remove existing members and add new ones
        $team->members()->delete();
        
        foreach ($validated['members'] as $member) {
            TeamMember::create([
                'team_id' => $team->id,
                'user_id' => $member['user_id'],
                'role' => $member['role'],
                'joined_at' => now(),
            ]);
        }

        return redirect()->route('teams.index')
            ->with('success', 'Team updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);
        
        $team->delete();

        return redirect()->route('teams.index')
            ->with('success', 'Team deleted successfully.');
    }

    /**
     * Add member to team
     */
    public function addMember(Request $request, Team $team)
    {
        $this->authorize('update', $team);
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:lead,member',
        ]);

        // Check if user is already a member
        if ($team->members()->where('user_id', $validated['user_id'])->exists()) {
            return back()->with('error', 'User is already a team member.');
        }

        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $validated['user_id'],
            'role' => $validated['role'],
            'joined_at' => now(),
        ]);

        return back()->with('success', 'Member added successfully.');
    }

    /**
     * Remove member from team
     */
    public function removeMember(Team $team, User $user)
    {
        $this->authorize('update', $team);
        
        $member = $team->members()->where('user_id', $user->id)->first();
        
        if (!$member) {
            return back()->with('error', 'User is not a team member.');
        }

        $member->delete();

        return back()->with('success', 'Member removed successfully.');
    }
}
