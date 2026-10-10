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

    /**
     * Get auto-formatted article content preserving paragraphs, line breaks, bullet points and headings.
     */
    public function getFormattedContentAttribute(): string
    {
        $content = trim($this->content ?? '');
        if ($content === '') {
            return '';
        }

        // If it already has block-level HTML tags, return as-is
        if (preg_match('/<(?:p|div|h[1-6]|ul|ol|li|blockquote|table|section|article)\b/i', $content)) {
            return $content;
        }

        // Normalize newlines
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Split into paragraphs / blocks by 2 or more newlines
        $blocks = preg_split('/\n{2,}/', $content);
        $htmlParts = [];

        foreach ($blocks as $block) {
            $block = trim($block);
            if ($block === '') {
                continue;
            }

            // Handle blockquote (> quote)
            if (str_starts_with($block, '>')) {
                $quoteLines = array_map(function ($line) {
                    return preg_replace('/^>\s?/', '', $line);
                }, explode("\n", $block));
                $htmlParts[] = '<blockquote class="p-6 bg-slate-50 border-l-4 border-[#cd202c] rounded-r-xl my-6 font-medium text-slate-900 text-base md:text-lg italic">' . 
                    implode('<br>', array_map('htmlspecialchars', $quoteLines)) . 
                    '</blockquote>';
                continue;
            }

            // Handle markdown headings (# Heading, ## Heading, ### Heading)
            if (preg_match('/^(#{1,6})\s+(.+)$/', $block, $hMatches)) {
                $level = strlen($hMatches[1]);
                $hTag = $level <= 2 ? 'h2' : 'h3';
                $hClass = $level <= 2 
                    ? 'text-2xl md:text-3xl font-black uppercase text-slate-900 tracking-tight mt-8 mb-4' 
                    : 'text-xl md:text-2xl font-black uppercase text-slate-900 tracking-tight mt-6 mb-3';
                $htmlParts[] = "<{$hTag} class=\"{$hClass}\">" . htmlspecialchars($hMatches[2]) . "</{$hTag}>";
                continue;
            }

            $lines = explode("\n", $block);

            // Check if all lines are bullet points
            $isBulletList = true;
            foreach ($lines as $line) {
                $trimmedLine = trim($line);
                if ($trimmedLine === '') continue;
                if (!preg_match('/^([•\-\*]|\d+\.)\s+(.+)$/u', $trimmedLine)) {
                    $isBulletList = false;
                    break;
                }
            }

            if ($isBulletList && count($lines) > 0) {
                $listItems = '';
                foreach ($lines as $line) {
                    $trimmedLine = trim($line);
                    if ($trimmedLine === '') continue;
                    if (preg_match('/^([•\-\*]|\d+\.)\s+(.+)$/u', $trimmedLine, $matches)) {
                        $itemText = $this->formatInlineMarkdown(htmlspecialchars($matches[2]));
                        $listItems .= '<li class="text-slate-700 leading-relaxed font-normal pl-1">' . $itemText . '</li>';
                    } else {
                        $listItems .= '<li class="text-slate-700 leading-relaxed font-normal pl-1">' . $this->formatInlineMarkdown(htmlspecialchars($trimmedLine)) . '</li>';
                    }
                }
                $htmlParts[] = '<ul class="list-disc pl-6 space-y-2 my-5 text-slate-700">' . $listItems . '</ul>';
                continue;
            }

            // Check if block starts with a heading line (e.g. "Career Coaching Accomplishments:" or short title) followed by bullets or text
            if (count($lines) > 1 && (str_ends_with(trim($lines[0]), ':') || (strlen(trim($lines[0])) <= 60 && !preg_match('/^([•\-\*]|\d+\.)/u', trim($lines[0])) && preg_match('/^([•\-\*]|\d+\.)/u', trim($lines[1]))))) {
                $firstLine = trim(array_shift($lines));
                $subHeading = '<h3 class="text-xl md:text-2xl font-black uppercase text-slate-900 tracking-tight mt-8 mb-3">' . htmlspecialchars(rtrim($firstLine, ':')) . '</h3>';
                
                // Check remaining lines
                $remainingBullets = true;
                foreach ($lines as $line) {
                    $t = trim($line);
                    if ($t === '') continue;
                    if (!preg_match('/^([•\-\*]|\d+\.)\s+(.+)$/u', $t)) {
                        $remainingBullets = false;
                        break;
                    }
                }

                if ($remainingBullets && count($lines) > 0) {
                    $listItems = '';
                    foreach ($lines as $line) {
                        $t = trim($line);
                        if ($t === '') continue;
                        if (preg_match('/^([•\-\*]|\d+\.)\s+(.+)$/u', $t, $m)) {
                            $listItems .= '<li class="text-slate-700 leading-relaxed font-normal pl-1">' . $this->formatInlineMarkdown(htmlspecialchars($m[2])) . '</li>';
                        }
                    }
                    $htmlParts[] = $subHeading . "\n" . '<ul class="list-disc pl-6 space-y-2 my-4 text-slate-700">' . $listItems . '</ul>';
                    continue;
                } else {
                    $formattedLines = array_map(function ($l) {
                        return $this->formatInlineMarkdown(htmlspecialchars($l));
                    }, $lines);
                    $htmlParts[] = $subHeading . "\n" . '<p class="text-base md:text-lg leading-relaxed text-slate-700 mb-6 font-normal">' . implode('<br>', $formattedLines) . '</p>';
                    continue;
                }
            }

            // Check if single short line is a standalone title/heading:
            // Ends with ':' or (less than 65 chars, no ending period, or is in uppercase/title)
            $trimmedSingle = trim($block);
            if (count($lines) === 1 && (
                str_ends_with($trimmedSingle, ':') || 
                (strlen($trimmedSingle) <= 65 && !preg_match('/[\.!?]$/', $trimmedSingle)) ||
                (strlen($trimmedSingle) <= 60 && preg_match('/^[A-Z0-9\s—–\.\:\-]{10,60}$/', $trimmedSingle))
            )) {
                $htmlParts[] = '<h3 class="text-xl md:text-2xl font-black uppercase text-slate-900 tracking-tight mt-8 mb-4">' . htmlspecialchars(rtrim($trimmedSingle, ':')) . '</h3>';
                continue;
            }

            // Standard paragraph
            $formattedLines = array_map(function ($l) {
                return $this->formatInlineMarkdown(htmlspecialchars($l));
            }, $lines);
            $htmlParts[] = '<p class="text-base md:text-lg leading-relaxed text-slate-700 mb-6 font-normal">' . implode('<br>', $formattedLines) . '</p>';
        }

        return implode("\n", $htmlParts);
    }

    /**
     * Parse inline markdown like bold and italics.
     */
    protected function formatInlineMarkdown(string $text): string
    {
        // Bold **text**
        $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
        // Italic *text*
        $text = preg_replace('/(?<!\*)\*([^*]+?)\*(?!\*)/s', '<em>$1</em>', $text);
        return $text;
    }
}

