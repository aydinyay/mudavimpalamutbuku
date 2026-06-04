<?php

namespace App\Modules\Website\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Menu\Models\MenuItem;

class HomeController extends Controller
{
    public function index()
    {
        $featured = MenuItem::where('is_featured', true)
            ->where('is_available', true)
            ->with(['translations', 'category'])
            ->take(6)
            ->get();

        return view('website.home', compact('featured'));
    }

    public function about()
    {
        return view('website.about');
    }

    public function contact()
    {
        return view('website.contact');
    }

    public function gallery()
    {
        return view('website.gallery');
    }
}
