<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    /**
     * Display chat rooms
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->get('search');
        
        $query = ChatRoom::with(['project', 'latestMessage.user']);
        
        // Apply role-based filtering
        if (!$user->can('manage-projects')) {
            $projectIds = $user->teams()->pluck('project_id');
            $query->whereIn('project_id', $projectIds);
        }
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('project', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $chatRooms = $query->latest('updated_at')->paginate(20)->withQueryString();
        
        return Inertia::render('Chat/Index', [
            'chatRooms' => $chatRooms,
            'filters' => [
                'search' => $search,
            ]
        ]);
    }

    /**
     * Display specific chat room
     */
    public function show(ChatRoom $room, Request $request)
    {
        $user = auth()->user();
        
        // Check if user has access to this chat room
        if (!$user->can('manage-projects')) {
            $projectIds = $user->teams()->pluck('project_id');
            if (!$projectIds->contains($room->project_id)) {
                abort(403, 'You do not have access to this chat room.');
            }
        }
        
        $room->load('project');
        
        // Get messages with pagination
        $page = $request->get('page', 1);
        $messages = $room->messages()
            ->with('user')
            ->latest()
            ->paginate(50, ['*'], 'page', $page);
        
        // Reverse messages for chronological order
        $messages->setCollection($messages->getCollection()->reverse()->values());
        
        // Get team members for this project
        $teamMembers = $room->project->team 
            ? $room->project->team->members()->with('user')->get()
            : collect();
        
        return Inertia::render('Chat/Show', [
            'chatRoom' => $room,
            'messages' => $messages,
            'teamMembers' => $teamMembers,
        ]);
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request, ChatRoom $room)
    {
        $user = auth()->user();
        
        // Check access
        if (!$user->can('manage-projects')) {
            $projectIds = $user->teams()->pluck('project_id');
            if (!$projectIds->contains($room->project_id)) {
                abort(403, 'You do not have access to this chat room.');
            }
        }
        
        $validated = $request->validate([
            'message' => 'required_without:file|string|max:2000',
            'file' => 'nullable|file|max:10240', // 10MB max
        ]);

        $messageData = [
            'chat_room_id' => $room->id,
            'user_id' => $user->id,
            'message' => $validated['message'] ?? '',
        ];

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('chat-files', $filename, 'public');
            
            $messageData['file_path'] = $path;
            $messageData['file_name'] = $file->getClientOriginalName();
            $messageData['file_type'] = $file->getMimeType();
        }

        $message = ChatMessage::create($messageData);
        $message->load('user');
        
        // Update room's updated_at timestamp
        $room->touch();
        
        // TODO: Broadcast message via Laravel Echo/Pusher
        // broadcast(new MessageSent($message))->toOthers();
        
        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Upload file to chat
     */
    public function uploadFile(Request $request, ChatRoom $room)
    {
        $user = auth()->user();
        
        // Check access
        if (!$user->can('manage-projects')) {
            $projectIds = $user->teams()->pluck('project_id');
            if (!$projectIds->contains($room->project_id)) {
                abort(403, 'You do not have access to this chat room.');
            }
        }
        
        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'message' => 'nullable|string|max:500',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('chat-files', $filename, 'public');
        
        $message = ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => $user->id,
            'message' => $validated['message'] ?? 'Shared a file',
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->getMimeType(),
        ]);
        
        $message->load('user');
        
        // Update room's updated_at timestamp
        $room->touch();
        
        // TODO: Broadcast message via Laravel Echo/Pusher
        // broadcast(new MessageSent($message))->toOthers();
        
        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Create a new chat room
     */
    public function createRoom(Request $request)
    {
        $this->authorize('manage-projects');
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'project_id' => 'required|exists:projects,id',
            'is_private' => 'boolean',
        ]);

        $room = ChatRoom::create($validated);
        
        return response()->json([
            'success' => true,
            'room' => $room,
        ]);
    }

    /**
     * Get messages for a room (API endpoint for real-time updates)
     */
    public function getMessages(ChatRoom $room, Request $request)
    {
        $user = auth()->user();
        
        // Check access
        if (!$user->can('manage-projects')) {
            $projectIds = $user->teams()->pluck('project_id');
            if (!$projectIds->contains($room->project_id)) {
                abort(403);
            }
        }
        
        $lastMessageId = $request->get('last_message_id');
        
        $query = $room->messages()->with('user');
        
        if ($lastMessageId) {
            $query->where('id', '>', $lastMessageId);
        } else {
            $query->latest()->limit(50);
        }
        
        $messages = $query->get();
        
        if (!$lastMessageId) {
            $messages = $messages->reverse()->values();
        }
        
        return response()->json([
            'messages' => $messages,
        ]);
    }
}
