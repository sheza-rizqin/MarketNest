<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class SellerDashboardController extends Controller
{
    public function dashboard(Request $request): View
    {
        $statistics = $request->user()->listings()
            ->selectRaw('COUNT(*) AS total_listings')
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END), 0) AS active_listings")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END), 0) AS sold_listings")
            ->selectRaw("COALESCE(SUM(CASE WHEN status = 'archived' THEN 1 ELSE 0 END), 0) AS archived_listings")
            ->first();

        $recentListings = $request->user()->listings()
            ->with(['category', 'subcategory', 'images'])
            ->latest('created_at')
            ->latest('id')
            ->limit(5)
            ->get();

        return view('dashboard', compact('statistics', 'recentListings'));
    }

    public function myListings(Request $request): View
    {
        $listings = $request->user()->listings()
            ->with(['category', 'subcategory', 'images'])
            ->latest('created_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('listings.mine', compact('listings'));
    }
}
