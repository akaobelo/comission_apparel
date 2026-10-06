<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NewsArticle;

class NewsController extends Controller
{
    /**
     * Public Newsroom / Stories Archive
     */
    public function index(Request $request)
    {
        $query = NewsArticle::where('is_active', true)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order', 'asc')
            ->orderBy('published_at', 'desc');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $articles = $query->paginate(9)->withQueryString();
        $categories = NewsArticle::where('is_active', true)->distinct()->pluck('category');

        return view('news.index', compact('articles', 'categories'));
    }

    /**
     * Dedicated Article Detail View
     */
    public function show($slug)
    {
        $article = NewsArticle::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $relatedArticles = NewsArticle::where('is_active', true)
            ->where('id', '!=', $article->id)
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return view('news.show', compact('article', 'relatedArticles'));
    }
}
