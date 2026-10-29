<?php

namespace App\Http\Controllers;

use App\Models\NewsPost;

class NewsController extends Controller
{
    public function index()
    {
        $newsPosts = NewsPost::query()
            ->latest('published_at')
            ->latest('id')
            ->paginate(10);

        return view('news.index', compact('newsPosts'));
    }
}
