<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getUserDetail($username)
{
    // Pastikan pengguna sudah login
    $authUser = Auth::user();
    if (!$authUser) {
        return response()->json([
            'message' => 'Unauthenticated.'
        ], 401);
    }

    // Cari user berdasarkan username
    $user = User::where('username', $username)->first();

    if (!$user) {
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }

    // Cek apakah user yang login adalah akun target
    $isYourAccount = $authUser->id === $user->id;

    // Cek apakah pengguna yang login mengikuti akun ini
    $followingStatus = 'not-following';
    if ($authUser->following()->where('users.id', $user->id)->exists()) {
        $followingStatus = 'following';
    } elseif ($user->followers()->where('follower_id', $authUser->id)->where('status', 'requested')->exists()) {
        $followingStatus = 'requested';
    }

    // Hitung jumlah followers, following, dan posts
    $followersCount = $user->followers()->count();
    $followingCount = $user->following()->count();
    $postsCount = $user->post()->count();

    // Jika akun private dan bukan akun sendiri serta belum mengikuti, sembunyikan posts
    $posts = [];
    if (!$user->is_private || $isYourAccount || $followingStatus === 'following') {
        $posts = $user->post()->with('attachments')->get()->map(function ($post) {
            return [
                'id' => $post->id,
                'caption' => $post->caption,
                'created_at' => $post->created_at,
                'deleted_at' => $post->deleted_at,
                'attachments' => $post->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'storage_path' => $attachment->storage_path,
                    ];
                }),
            ];
        });
    }

    return response()->json([
        'id' => $user->id,
        'full_name' => $user->full_name,
        'username' => $user->username,
        'bio' => $user->bio,
        'is_private' => $user->is_private,
        'created_at' => $user->created_at,
        'is_your_account' => $isYourAccount,
        'following_status' => $followingStatus,
        'posts_count' => $postsCount,
        'followers_count' => $followersCount,
        'following_count' => $followingCount,
        'posts' => $posts,
    ], 200);
}
public function getUnfollowedUsers()
{
    // Pastikan pengguna sudah login
    $authUser = Auth::user();
    if (!$authUser) {
        return response()->json([
            'message' => 'Unauthenticated.'
        ], 401);
    }

    // Ambil ID user yang telah diikuti oleh user yang login
    $followedUserIds = $authUser->following()->pluck('users.id')->toArray();

    // Ambil semua user yang tidak diikuti dan bukan user yang login
    $users = User::whereNotIn('id', $followedUserIds)
        ->where('id', '!=', $authUser->id)
        ->get(['id', 'full_name', 'username', 'bio', 'is_private', 'created_at', 'updated_at']);

    return response()->json([
        'users' => $users
    ], 200);
}
}
