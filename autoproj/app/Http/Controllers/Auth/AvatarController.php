<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class AvatarController {
  public function upload(Request $request) {
    $request->validate([
      'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    $user = auth()->user();

    // Delete old avatar if exists
    if ($user->avatar_url && Storage::disk('public')->exists($user->avatar_url)) {
      Storage::disk('public')->delete($user->avatar_url);
    }

    // Upload & optimize
    $file = $request->file('avatar');
    $filename = 'avatars/' . $user->id . '-' . time() . '.jpg';

    // Optimize image using Intervention
    $image = Image::make($file)
      ->resize(300, 300, function ($constraint) {
        $constraint->aspectRatio();
        $constraint->upsize();
      })
      ->encode('jpg', 80); //80% kval

    Storage::disk('public')->put($filename, $image);

    // Update user
    $user->update(['avatar_url' => $filename]);

    return response()->json([
      'message' => 'Avatar uploaded successfully',
      'avatar_url' => Storage::disk('public')->url($filename),
    ]);
  }
}
