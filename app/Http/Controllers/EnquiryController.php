<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactSellerRequest;
use App\Models\Listing;
use App\Notifications\SellerEnquiryReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;

class EnquiryController extends Controller
{
    public function store(ContactSellerRequest $request, Listing $listing): RedirectResponse
    {
        $this->authorize('contactSeller', $listing);

        $enquiry = $listing->enquiries()->create($request->validated());
        $listing->loadMissing('user');

        Notification::route('mail', $listing->user->email)
            ->notify(new SellerEnquiryReceived($enquiry));

        return redirect()->route('listings.show', $listing)
            ->with('status', 'Your message has been sent to the seller.');
    }
}
