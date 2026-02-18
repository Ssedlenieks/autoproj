<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AvatarController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        try {
            $user = $request->user();

            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Process image
            $file = $request->file('avatar');
            $filename = $user->id . '_' . time() . '.jpg'; // Always save as JPG

            // Initialize Intervention Image
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);

            // Resize to 400x400 (perfect for avatars)
            $image->cover(400, 400);

            // Encode as JPEG with 85% quality (balance between size and quality)
            $encoded = $image->toJpeg(85);

            // Save to storage
            $path = 'avatars/' . $filename;
            Storage::disk('public')->put($path, $encoded);

            // Update user
            $user->avatar = $path;
            $user->save();

            // Generate full URL
            $avatarUrl = url('storage/' . $path);

            return response()->json([
                'success' => true,
                'message' => 'Avatar uploaded successfully',
                'avatar_url' => $avatarUrl,
            ]);

        } catch (\Exception $e) {
            \Log::error('Avatar upload error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload avatar: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        try {
            $user = $request->user();

            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Avatar deleted successfully',
            ]);

        } catch (\Exception $e) {
            \Log::error('Avatar delete error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete avatar',
            ], 500);
        }
    }
}
