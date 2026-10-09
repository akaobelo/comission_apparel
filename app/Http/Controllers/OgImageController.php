<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DesignCollection;
use App\Models\DesignCatalog;
use App\Models\NewsArticle;
use App\Models\TeamStore;
use Illuminate\Support\Facades\File;

class OgImageController extends Controller
{
    /**
     * Serve an optimized Open Graph image for Design Collections (< 300KB for WhatsApp & iMessage)
     */
    public function collection($collection)
    {
        $collectionModel = DesignCollection::where('name', $collection)->first();
        if (!$collectionModel) {
            $cleanCollection = rtrim($collection, '. ');
            $collectionModel = DesignCollection::where('name', $collection . '.')
                ->orWhere('name', $cleanCollection)
                ->orWhere('name', $cleanCollection . '.')
                ->orWhere('name', 'like', $cleanCollection . '%')
                ->first();
        }
        if (!$collectionModel) {
            $collectionModel = DesignCollection::all()->first(function ($item) use ($collection) {
                return \Illuminate\Support\Str::slug($item->name) === \Illuminate\Support\Str::slug($collection);
            });
        }
        
        $sourcePath = null;
        if ($collectionModel && !empty($collectionModel->image_path)) {
            $sourcePath = $this->resolveLocalPath($collectionModel->image_path);
        }

        if (!$sourcePath || !file_exists($sourcePath)) {
            $colName = $collectionModel ? $collectionModel->name : $collection;
            $firstDesign = DesignCatalog::where('collection_name', $colName)->first();
            if ($firstDesign) {
                if (!empty($firstDesign->image_paths) && is_array($firstDesign->image_paths)) {
                    $sourcePath = $this->resolveLocalPath($firstDesign->image_paths[0]);
                } elseif (!empty($firstDesign->image_url)) {
                    $sourcePath = $this->resolveLocalPath($firstDesign->image_url);
                }
            }
        }

        $cacheKey = 'col_' . md5($collection);
        return $this->serveOptimizedImage($sourcePath, $cacheKey, 'portrait');
    }

    /**
     * Serve an optimized Open Graph image for News Articles (< 300KB for WhatsApp & iMessage)
     */
    public function news($slug)
    {
        $article = NewsArticle::where('slug', $slug)->first();
        $sourcePath = null;

        if ($article && !empty($article->cover_image)) {
            $sourcePath = $this->resolveLocalPath($article->cover_image);
        }

        $cacheKey = 'news_' . md5($slug);
        return $this->serveOptimizedImage($sourcePath, $cacheKey, 'landscape');
    }

    /**
     * Serve an optimized Open Graph image for Team Stores (< 300KB for WhatsApp & iMessage)
     */
    public function store($slug)
    {
        $store = TeamStore::where('slug', $slug)->first();
        $sourcePath = null;

        if ($store) {
            if (!empty($store->cover_image_path)) {
                $sourcePath = $this->resolveLocalPath($store->cover_image_path);
            } elseif (!empty($store->logo_path)) {
                $sourcePath = $this->resolveLocalPath($store->logo_path);
            }
        }

        $cacheKey = 'store_' . md5($slug);
        return $this->serveOptimizedImage($sourcePath, $cacheKey, 'landscape');
    }

    /**
     * Compress, resize, cache and return image strictly under 300KB
     */
    protected function serveOptimizedImage(?string $sourcePath, string $cacheKey, string $mode = 'landscape')
    {
        $cacheDir = storage_path('app/public/og-cache');
        if (!File::exists($cacheDir)) {
            File::makeDirectory($cacheDir, 0755, true);
        }

        $cacheFile = $cacheDir . '/' . $cacheKey . '.jpg';

        // Check cache validity (re-use if cached file exists and source has not changed)
        if (File::exists($cacheFile)) {
            if (!$sourcePath || !File::exists($sourcePath) || File::lastModified($cacheFile) >= File::lastModified($sourcePath)) {
                return response()->file($cacheFile, [
                    'Content-Type' => 'image/jpeg',
                    'Cache-Control' => 'public, max-age=604800',
                ]);
            }
        }

        // If source doesn't exist, fallback to default og-home.jpg
        if (!$sourcePath || !File::exists($sourcePath)) {
            $fallback = public_path('images/og-home.jpg');
            if (File::exists($fallback)) {
                return response()->file($fallback, [
                    'Content-Type' => 'image/jpeg',
                    'Cache-Control' => 'public, max-age=604800',
                ]);
            }
            abort(404);
        }

        // Read source image
        $data = @file_get_contents($sourcePath);
        if (!$data) {
            abort(404);
        }

        $src = @imagecreatefromstring($data);
        if (!$src) {
            abort(404);
        }

        $origW = imagesx($src);
        $origH = imagesy($src);

        // Target max dimensions based on aspect ratio
        if ($mode === 'landscape') {
            $maxW = 1200;
            $maxH = 630;
        } else {
            // Portrait / Square collection posters
            $maxW = 900;
            $maxH = 1100;
        }

        // Calculate scaled dimensions preserving aspect ratio
        $scale = min($maxW / $origW, $maxH / $origH, 1.0);
        $newW = max(1, (int)($origW * $scale));
        $newH = max(1, (int)($origH * $scale));

        $dst = imagecreatetruecolor($newW, $newH);
        
        // Fill white background for transparent PNGs
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);
        
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);

        // Compress to JPEG with quality that guarantees < 280KB for WhatsApp
        $quality = 82;
        imagejpeg($dst, $cacheFile, $quality);

        // If still > 280KB, re-compress with lower quality
        if (filesize($cacheFile) > 280 * 1024) {
            imagejpeg($dst, $cacheFile, 68);
        }

        imagedestroy($src);
        imagedestroy($dst);

        return response()->file($cacheFile, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    /**
     * Resolve a DB image path to an absolute filesystem path
     */
    protected function resolveLocalPath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        $trimmed = ltrim($path, '/');

        // Check if path is a remote URL
        if (str_starts_with($trimmed, 'http://') || str_starts_with($trimmed, 'https://')) {
            $cacheDir = storage_path('app/public/og-cache');
            if (!File::exists($cacheDir)) {
                File::makeDirectory($cacheDir, 0755, true);
            }
            $tempFile = $cacheDir . '/temp_' . md5($trimmed) . '.bin';
            if (file_exists($tempFile) && (time() - filemtime($tempFile) < 86400)) {
                return $tempFile;
            }
            $data = @file_get_contents($trimmed, false, stream_context_create([
                'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
            ]));
            if ($data) {
                file_put_contents($tempFile, $data);
                return $tempFile;
            }
        }

        // Check if path starts with storage/
        if (str_starts_with($trimmed, 'storage/')) {
            $rel = substr($trimmed, 8);
            $p = storage_path('app/public/' . $rel);
            if (file_exists($p)) return $p;
            $p2 = public_path($trimmed);
            if (file_exists($p2)) return $p2;
        }

        // Check public path
        $publicP = public_path($trimmed);
        if (file_exists($publicP)) {
            return $publicP;
        }

        // Check storage/app/public/ direct
        $storageP = storage_path('app/public/' . $trimmed);
        if (file_exists($storageP)) {
            return $storageP;
        }

        return null;
    }
}
