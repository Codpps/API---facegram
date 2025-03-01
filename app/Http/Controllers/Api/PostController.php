<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Follow;
use App\Models\Post;
use App\Models\PostAttachment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'nullable|integer|min:0',
            'size' => 'nullable|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid field',
                'errors' => $validator->errors()
            ], 422);
        }

        $page = (int) $request->query('page', 0);
        $size = (int) $request->query('size', 10);

        $posts = Post::with(['user:id,full_name,username,bio,is_private,created_at', 'attachments:id,storage_path'])
            ->orderBy('created_at', 'desc')
            ->skip($page * $size)
            ->take($size)
            ->get();

        return response()->json([
            'page' => $page,
            'size' => $size,
            'posts' => $posts
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'caption' => 'required|string',
            'attachments' => 'required|array',
            'attachments.*' => 'required|file|mimes:jpeg,png,jpg,gif,mp4|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $post = Post::create([
            'caption' => $request->caption,
            'user_id' => $request->user()->id,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('posts');

                    PostAttachment::create([
                        'storage_path' => $path,
                        'post_id' => $post->id,
                    ]);
                }
            }
        }

        $post->load('attachments');

        return response()->json([
            'status' => true,
            'message' => 'Post created successfully',
            'data' => $post
        ], 201);
    }


    public function destroy($id)
    {
        $post = Post::with('attachments')->find($id);

        if (!$post) {
            return response()->json([
                'message' => 'Post not found'
            ], 404);
        }

        if (Auth::id() !== $post->user_id) {
            return response()->json([
                'message' => 'Forbidden access'
            ], 403);
        }

        // Hapus lampiran dari storage
        foreach ($post->attachments as $attachment) {
            Storage::delete($attachment->storage_path);
            $attachment->delete();
        }

        // Hapus postingan
        $post->delete();

        return response()->json([
            'status' => true,
            'message' => 'Post deleted successfully'
        ], 200);
    }
}
