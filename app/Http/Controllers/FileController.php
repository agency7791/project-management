<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{
    /**
     * Download a file
     */
    public function download(ProjectFile $file)
    {
        $user = auth()->user();
        
        // Check if user has access to this file's project
        if (!$user->can('manage-projects')) {
            $projectIds = $user->teams()->pluck('project_id');
            if (!$projectIds->contains($file->project_id)) {
                abort(403, 'You do not have access to this file.');
            }
        }
        
        if (!Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'File not found.');
        }
        
        return Storage::disk('public')->download($file->file_path, $file->original_name);
    }

    /**
     * Upload file to project
     */
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'file' => 'required|file|max:10240', // 10MB max
            'description' => 'nullable|string|max:500',
        ]);

        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('project-files', $filename, 'public');
        
        $projectFile = ProjectFile::create([
            'project_id' => $validated['project_id'],
            'uploader_id' => auth()->id(),
            'original_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'description' => $validated['description'],
        ]);
        
        return response()->json([
            'success' => true,
            'file' => $projectFile->load('uploader'),
        ]);
    }

    /**
     * Delete a file
     */
    public function destroy(ProjectFile $file)
    {
        $user = auth()->user();
        
        // Check permissions
        if (!$user->can('manage-projects') && $file->uploader_id !== $user->id) {
            abort(403, 'You can only delete your own files.');
        }
        
        // Delete physical file
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }
        
        $file->delete();
        
        return response()->json(['success' => true]);
    }
}