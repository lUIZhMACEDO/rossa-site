<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\NewsPostRequest;
use App\Models\NewsPost;

class NewsPostController extends Controller
{
    public function index()
    {
        $newsPosts = NewsPost::query()
            ->latest('published_at')
            ->latest('id')
            ->paginate(25);

        return view('admin.news.index', compact('newsPosts'));
    }

    public function create()
    {
        return view('admin.news.create');
    }

    public function store(NewsPostRequest $request)
    {
        NewsPost::create($request->newsPostData());

        return redirect()->route('admin.news.index')
            ->with('success', 'News post published successfully.');
    }

    public function edit(NewsPost $newsPost)
    {
        return view('admin.news.edit', compact('newsPost'));
    }

    public function update(NewsPostRequest $request, NewsPost $newsPost)
    {
        $newsPost->update($request->newsPostData());

        return redirect()->route('admin.news.index')
            ->with('success', 'News post updated successfully.');
    }

    public function destroy(NewsPost $newsPost)
    {
        $newsPost->delete();

        return redirect()->route('admin.news.index')
            ->with('success', 'News post deleted successfully.');
    }
}
