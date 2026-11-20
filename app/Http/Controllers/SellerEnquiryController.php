<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SellerEnquiryController extends Controller
{
    public function index(Request $request): View
    {
        $enquiries = Enquiry::query()
            ->whereHas('listing', fn (Builder $listings) => $listings->where('user_id', $request->user()->id))
            ->with('listing:id,slug,title,status')
            ->latest('created_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('enquiries.index', compact('enquiries'));
    }
}
