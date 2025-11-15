<?php

namespace App\Http\Controllers;

use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TeamMemberController extends Controller
{
    /**
     * Display admin dashboard - list all team members
     */
    public function index()
    {
        $teamMembers = TeamMember::orderBy('created_at', 'desc')->get();
        return view('admin.team-members.index', compact('teamMembers'));
    }

    /**
     * Show the form for creating a new team member
     */
    public function create()
    {
        return view('admin.team-members.create');
    }

    /**
     * Store a newly created team member
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'profile_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp,bmp,svg,ico,tiff,tif,heic,heif|max:30720',
            'telegram_link' => 'nullable|url|max:255',
            'facebook_link' => 'nullable|url|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        if ($request->hasFile('profile_image')) {
            try {
                $file = $request->file('profile_image');
                
                // Check PHP upload limits
                $uploadMaxFilesize = ini_get('upload_max_filesize');
                $postMaxSize = ini_get('post_max_size');
                $uploadMaxBytes = $this->convertToBytes($uploadMaxFilesize);
                $postMaxBytes = $this->convertToBytes($postMaxSize);
                
                // Check if file exists and is valid
                if (!$file || !$file->isValid()) {
                    $error = $file ? $file->getError() : 'File not found';
                    $errorMessage = 'The uploaded file is not valid. Error code: ' . $error;
                    
                    // Provide helpful message for PHP limit errors
                    if ($error == UPLOAD_ERR_INI_SIZE || $error == UPLOAD_ERR_FORM_SIZE) {
                        $errorMessage .= '. PHP upload limit is ' . $uploadMaxFilesize . '. Please increase upload_max_filesize and post_max_size in php.ini.';
                    }
                    
                    Log::error('Invalid file upload', ['error' => $error, 'php_limits' => [
                        'upload_max_filesize' => $uploadMaxFilesize,
                        'post_max_size' => $postMaxSize
                    ]]);
                    return back()->withErrors(['profile_image' => $errorMessage])->withInput();
                }
                
                // Check file size (in KB, max 30MB = 30720KB for high-res images like 3k*4k)
                $fileSize = $file->getSize() / 1024; // Convert to KB
                if ($fileSize > 30720) {
                    return back()->withErrors(['profile_image' => 'The image is too large. Maximum size is 30MB.'])->withInput();
                }
                
                // Get image dimensions
                $imageInfo = getimagesize($file->getRealPath());
                if ($imageInfo) {
                    $width = $imageInfo[0];
                    $height = $imageInfo[1];
                    Log::info('Image dimensions', ['width' => $width, 'height' => $height, 'size' => round($fileSize, 2) . 'KB']);
                }
                
                // Get MIME type
                $mimeType = $file->getMimeType();
                $allowedMimes = [
                    'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 
                    'image/webp', 'image/bmp', 'image/svg+xml', 'image/x-icon',
                    'image/tiff', 'image/tif', 'image/heic', 'image/heif'
                ];
                
                if (!in_array($mimeType, $allowedMimes)) {
                    Log::warning('Unsupported MIME type', ['mime' => $mimeType, 'filename' => $file->getClientOriginalName()]);
                    // Still try to upload if it's an image type
                    if (!str_starts_with($mimeType, 'image/')) {
                        return back()->withErrors(['profile_image' => 'The file must be an image. Detected type: ' . $mimeType])->withInput();
                    }
                }
                
                $path = $file->store('team-members', 'public');
                
                if (!$path) {
                    Log::error('File store returned false', [
                        'filename' => $file->getClientOriginalName(),
                        'mime' => $mimeType,
                        'size' => $fileSize
                    ]);
                    return back()->withErrors(['profile_image' => 'The profile image failed to upload. Please try again.'])->withInput();
                }
                
                $validated['profile_image'] = $path;
                Log::info('Image uploaded successfully', [
                    'path' => $path,
                    'mime' => $mimeType,
                    'size' => round($fileSize, 2) . 'KB'
                ]);
            } catch (\Exception $e) {
                Log::error('Image upload error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'file' => isset($file) ? $file->getClientOriginalName() : 'unknown'
                ]);
                return back()->withErrors(['profile_image' => 'The profile image failed to upload: ' . $e->getMessage()])->withInput();
            }
        }

        TeamMember::create($validated);

        return redirect()->route('admin.team-members.index')
            ->with('success', 'Team member created successfully.');
    }

    /**
     * Show the form for editing a team member
     */
    public function edit($id)
    {
        $teamMember = TeamMember::findOrFail($id);
        return view('admin.team-members.edit', compact('teamMember'));
    }

    /**
     * Update a team member
     */
    public function update(Request $request, $id)
    {
        $teamMember = TeamMember::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'profile_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp,bmp,svg,ico,tiff,tif,heic,heif|max:30720',
            'telegram_link' => 'nullable|url|max:255',
            'facebook_link' => 'nullable|url|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        if ($request->hasFile('profile_image')) {
            try {
                $file = $request->file('profile_image');
                
                // Check PHP upload limits
                $uploadMaxFilesize = ini_get('upload_max_filesize');
                $postMaxSize = ini_get('post_max_size');
                $uploadMaxBytes = $this->convertToBytes($uploadMaxFilesize);
                $postMaxBytes = $this->convertToBytes($postMaxSize);
                
                // Check if file exists and is valid
                if (!$file || !$file->isValid()) {
                    $error = $file ? $file->getError() : 'File not found';
                    $errorMessage = 'The uploaded file is not valid. Error code: ' . $error;
                    
                    // Provide helpful message for PHP limit errors
                    if ($error == UPLOAD_ERR_INI_SIZE || $error == UPLOAD_ERR_FORM_SIZE) {
                        $errorMessage .= '. PHP upload limit is ' . $uploadMaxFilesize . '. Please increase upload_max_filesize and post_max_size in php.ini.';
                    }
                    
                    Log::error('Invalid file upload', ['error' => $error, 'php_limits' => [
                        'upload_max_filesize' => $uploadMaxFilesize,
                        'post_max_size' => $postMaxSize
                    ]]);
                    return back()->withErrors(['profile_image' => $errorMessage])->withInput();
                }
                
                // Check file size (in KB, max 30MB = 30720KB for high-res images like 3k*4k)
                $fileSize = $file->getSize() / 1024; // Convert to KB
                if ($fileSize > 30720) {
                    return back()->withErrors(['profile_image' => 'The image is too large. Maximum size is 30MB.'])->withInput();
                }
                
                // Get image dimensions
                $imageInfo = getimagesize($file->getRealPath());
                if ($imageInfo) {
                    $width = $imageInfo[0];
                    $height = $imageInfo[1];
                    Log::info('Image dimensions', ['width' => $width, 'height' => $height, 'size' => round($fileSize, 2) . 'KB']);
                }
                
                // Get MIME type
                $mimeType = $file->getMimeType();
                $allowedMimes = [
                    'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 
                    'image/webp', 'image/bmp', 'image/svg+xml', 'image/x-icon',
                    'image/tiff', 'image/tif', 'image/heic', 'image/heif'
                ];
                
                if (!in_array($mimeType, $allowedMimes)) {
                    Log::warning('Unsupported MIME type', ['mime' => $mimeType, 'filename' => $file->getClientOriginalName()]);
                    // Still try to upload if it's an image type
                    if (!str_starts_with($mimeType, 'image/')) {
                        return back()->withErrors(['profile_image' => 'The file must be an image. Detected type: ' . $mimeType])->withInput();
                    }
                }
                
                // Delete old image if exists
                if ($teamMember->profile_image) {
                    Storage::disk('public')->delete($teamMember->profile_image);
                }
                
                // Store the file with original extension preserved
                $path = $file->store('team-members', 'public');
                
                if (!$path) {
                    Log::error('File store returned false', [
                        'filename' => $file->getClientOriginalName(),
                        'mime' => $mimeType,
                        'size' => $fileSize
                    ]);
                    return back()->withErrors(['profile_image' => 'The profile image failed to upload. Please try again.'])->withInput();
                }
                
                $validated['profile_image'] = $path;
                Log::info('Image uploaded successfully', [
                    'path' => $path,
                    'mime' => $mimeType,
                    'size' => round($fileSize, 2) . 'KB'
                ]);
            } catch (\Exception $e) {
                Log::error('Image upload error: ' . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                    'file' => $file->getClientOriginalName() ?? 'unknown'
                ]);
                return back()->withErrors(['profile_image' => 'The profile image failed to upload: ' . $e->getMessage()])->withInput();
            }
        } else {
            // Keep existing image if no new image uploaded
            $validated['profile_image'] = $teamMember->profile_image;
        }

        $teamMember->update($validated);

        return redirect()->route('admin.team-members.index')
            ->with('success', 'Team member updated successfully.');
    }

    /**
     * Delete a team member
     */
    public function destroy($id)
    {
        $teamMember = TeamMember::findOrFail($id);

        // Delete profile image if exists
        if ($teamMember->profile_image) {
            Storage::disk('public')->delete($teamMember->profile_image);
        }

        $teamMember->delete();

        return redirect()->route('admin.team-members.index')
            ->with('success', 'Team member deleted successfully.');
    }

    // ========== API METHODS ==========

    /**
     * Get all team members (API)
     */
    public function apiIndex()
    {
        $teamMembers = TeamMember::orderBy('created_at', 'desc')->get();
        
        // Transform image paths to full URLs
        $teamMembers = $teamMembers->map(function ($member) {
            if ($member->profile_image) {
                $member->profile_image = asset('storage/' . $member->profile_image);
            }
            return $member;
        });

        return response()->json([
            'success' => true,
            'data' => $teamMembers
        ]);
    }

    /**
     * Get a single team member (API)
     */
    public function apiShow($id)
    {
        $teamMember = TeamMember::findOrFail($id);

        if ($teamMember->profile_image) {
            $teamMember->profile_image = asset('storage/' . $teamMember->profile_image);
        }

        return response()->json([
            'success' => true,
            'data' => $teamMember
        ]);
    }

    /**
     * Create a team member (API)
     */
    public function apiStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'profile_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp,bmp,svg,ico,tiff,tif,heic,heif|max:30720',
            'telegram_link' => 'nullable|url|max:255',
            'facebook_link' => 'nullable|url|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        if ($request->hasFile('profile_image')) {
            try {
                $file = $request->file('profile_image');
                
                if (!$file || !$file->isValid()) {
                    $error = $file ? $file->getError() : 'File not found';
                    $errorMessage = 'The uploaded file is not valid. Error code: ' . $error;
                    
                    // Provide helpful message for PHP limit errors
                    if ($error == UPLOAD_ERR_INI_SIZE || $error == UPLOAD_ERR_FORM_SIZE) {
                        $uploadMaxFilesize = ini_get('upload_max_filesize');
                        $errorMessage .= '. PHP upload limit is ' . $uploadMaxFilesize . '. Please increase upload_max_filesize and post_max_size in php.ini.';
                    }
                    
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 422);
                }
                
                $fileSize = $file->getSize() / 1024;
                if ($fileSize > 30720) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The image is too large. Maximum size is 30MB.'
                    ], 422);
                }
                
                // Get image dimensions for logging
                $imageInfo = getimagesize($file->getRealPath());
                if ($imageInfo) {
                    Log::info('API Image dimensions', [
                        'width' => $imageInfo[0], 
                        'height' => $imageInfo[1], 
                        'size' => round($fileSize, 2) . 'KB'
                    ]);
                }
                
                $path = $file->store('team-members', 'public');
                if (!$path) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The profile image failed to upload.'
                    ], 500);
                }
                
                $validated['profile_image'] = $path;
            } catch (\Exception $e) {
                Log::error('API Image upload error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'The profile image failed to upload: ' . $e->getMessage()
                ], 500);
            }
        }

        $teamMember = TeamMember::create($validated);

        if ($teamMember->profile_image) {
            $teamMember->profile_image = asset('storage/' . $teamMember->profile_image);
        }

        return response()->json([
            'success' => true,
            'message' => 'Team member created successfully.',
            'data' => $teamMember
        ], 201);
    }

    /**
     * Update a team member (API)
     */
    public function apiUpdate(Request $request, $id)
    {
        $teamMember = TeamMember::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'profile_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp,bmp,svg,ico,tiff,tif,heic,heif|max:30720',
            'telegram_link' => 'nullable|url|max:255',
            'facebook_link' => 'nullable|url|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        if ($request->hasFile('profile_image')) {
            try {
                $file = $request->file('profile_image');
                
                if (!$file || !$file->isValid()) {
                    $error = $file ? $file->getError() : 'File not found';
                    $errorMessage = 'The uploaded file is not valid. Error code: ' . $error;
                    
                    // Provide helpful message for PHP limit errors
                    if ($error == UPLOAD_ERR_INI_SIZE || $error == UPLOAD_ERR_FORM_SIZE) {
                        $uploadMaxFilesize = ini_get('upload_max_filesize');
                        $errorMessage .= '. PHP upload limit is ' . $uploadMaxFilesize . '. Please increase upload_max_filesize and post_max_size in php.ini.';
                    }
                    
                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage
                    ], 422);
                }
                
                $fileSize = $file->getSize() / 1024;
                if ($fileSize > 30720) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The image is too large. Maximum size is 30MB.'
                    ], 422);
                }
                
                // Get image dimensions for logging
                $imageInfo = getimagesize($file->getRealPath());
                if ($imageInfo) {
                    Log::info('API Image dimensions', [
                        'width' => $imageInfo[0], 
                        'height' => $imageInfo[1], 
                        'size' => round($fileSize, 2) . 'KB'
                    ]);
                }
                
                // Delete old image if exists
                if ($teamMember->profile_image) {
                    Storage::disk('public')->delete($teamMember->profile_image);
                }
                
                $path = $file->store('team-members', 'public');
                if (!$path) {
                    return response()->json([
                        'success' => false,
                        'message' => 'The profile image failed to upload.'
                    ], 500);
                }
                
                $validated['profile_image'] = $path;
            } catch (\Exception $e) {
                Log::error('API Image upload error: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'The profile image failed to upload: ' . $e->getMessage()
                ], 500);
            }
        } else {
            // Keep existing image if no new image uploaded
            $validated['profile_image'] = $teamMember->profile_image;
        }

        $teamMember->update($validated);

        if ($teamMember->profile_image) {
            $teamMember->profile_image = asset('storage/' . $teamMember->profile_image);
        }

        return response()->json([
            'success' => true,
            'message' => 'Team member updated successfully.',
            'data' => $teamMember
        ]);
    }

    /**
     * Delete a team member (API)
     */
    public function apiDestroy($id)
    {
        $teamMember = TeamMember::findOrFail($id);

        // Delete profile image if exists
        if ($teamMember->profile_image) {
            Storage::disk('public')->delete($teamMember->profile_image);
        }

        $teamMember->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team member deleted successfully.'
        ]);
    }

    /**
     * Convert PHP ini size string to bytes
     */
    private function convertToBytes($size)
    {
        $size = trim($size);
        $last = strtolower($size[strlen($size) - 1]);
        $size = (int) $size;
        
        switch ($last) {
            case 'g':
                $size *= 1024;
            case 'm':
                $size *= 1024;
            case 'k':
                $size *= 1024;
        }
        
        return $size;
    }
}

