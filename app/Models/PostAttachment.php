<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PostAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'storage_path',
        'post_id',
    ];

    /**
     * Get the post that owns the attachment.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the full URL for the attachment.
     */
    protected $appends = ['url'];

    public function getUrlAttribute()
    {
        return url(Storage::url($this->storage_path));
    }
}
