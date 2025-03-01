<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowerController extends Controller
{
    public function follow(Request $request, $username)
    {
        // 🔍 Ambil token dengan aman
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // 🔍 Cari user yang akan di-follow
        $userToFollow = User::where('username', $username)->first();
        if (!$userToFollow) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // 🔍 Cek apakah user mencoba follow dirinya sendiri
        if (Auth::id() === $userToFollow->id) {
            return response()->json(['message' => 'You are not allowed to follow yourself'], 422);
        }

        // 🔍 Cek apakah sudah mengikuti user ini
        $alreadyFollowed = Follow::where('follower_id', Auth::id())
            ->where('following_id', $userToFollow->id)
            ->exists();

        if ($alreadyFollowed) {
            return response()->json(['message' => 'You are already followed', 'status' => 'following'], 422);
        }

        // ✅ Buat data follow
        Follow::create([
            'follower_id' => Auth::id(),
            'following_id' => $userToFollow->id,
            'is_accepted' => 0,
        ]);

        return response()->json(['message' => 'Follow success', 'status' => 'following'], 200);
    }

    public function unfollow($username)
    {
        // Cari user yang ingin di-unfollow
        $userToUnfollow = User::where('username', $username)->first();

        // Jika user tidak ditemukan, return 404
        if (!$userToUnfollow) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // Cek apakah user saat ini sudah mengikuti user tersebut
        $follow = Follow::where('follower_id', Auth::id())
            ->where('following_id', $userToUnfollow->id)
            ->first();

        // Jika belum mengikuti, return 422
        if (!$follow) {
            return response()->json([
                'message' => 'You are not following the user'
            ], 422);
        }

        // Hapus data follow
        $follow->delete();

        // Berhasil unfollow, return 204 (No Content)
        return response()->json([], 204);
    }

    public function followers($username)
    {
        // Cari user berdasarkan username
        $user = User::where('username', $username)->first();

        // Jika user tidak ditemukan, return 404
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // Ambil daftar followers user
        $followers = Follow::where('following_id', $user->id)
            ->join('users', 'follows.follower_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.full_name',
                'users.username',
                'users.bio',
                'users.is_private',
                'users.created_at',
                'follows.is_accepted as is_requested'
            )
            ->get();

        return response()->json([
            'followers' => $followers
        ], 200);
    }

    public function acceptFollow($username)
{
    // Ambil user yang sedang login
    $user = Auth::user();

    // Cari user berdasarkan username
    $follower = User::where('username', $username)->first();

    if (!$follower) {
        return response()->json([
            'message' => 'User not found'
        ], 404);
    }

    // Cari data follow di tabel follows
    $follow = Follow::where('follower_id', $follower->id)
        ->where('following_id', $user->id)
        ->first();

    // Jika tidak ada data follow, user tidak sedang di-follow
    if (!$follow) {
        return response()->json([
            'message' => 'The user is not following you'
        ], 422);
    }

    // Jika sudah diterima, kembalikan error
    if ($follow->is_accepted) {
        return response()->json([
            'message' => 'Follow request is already accepted'
        ], 422);
    }

    // Perbarui status follow
    $follow->is_accepted = true;
    $follow->save();

    return response()->json([
        'message' => 'Follow request accepted'
    ], 200);
}

}
