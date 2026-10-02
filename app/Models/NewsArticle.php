<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class NewsArticle extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'category',
        'author',
        'summary',
        'content',
        'cover_image',
        'video_url',
        'gallery_images',
        'cta_text',
        'cta_url',
        'published_at',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'published_at' => 'datetime',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($article) {
            if (empty($article->slug)) {
                $article->slug = Str::slug($article->title) . '-' . strtolower(Str::random(5));
            }
            if (empty($article->published_at)) {
                $article->published_at = now();
            }
        });
    }

    /**
     * Parse video URL for YouTube or Vimeo embed.
     */
    public function getEmbedUrlAttribute()
    {
        if (empty($this->video_url)) {
            return null;
        }

        $url = $this->video_url;

        // YouTube
        if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches)) {
            return 'https://www.youtube.com/embed/' . $matches[1];
        }

        // Vimeo
        if (preg_match('/vimeo\.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)/', $url, $matches)) {
            return 'https://player.vimeo.com/video/' . $matches[3];
        }

        return $url;
    }
}
