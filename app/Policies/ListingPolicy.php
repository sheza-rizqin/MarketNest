<?php

namespace App\Policies;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ListingPolicy
{
    public function view(?User $user, Listing $listing): Response
    {
        return $listing->status === 'active' || $user?->id === $listing->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function viewImage(?User $user, Listing $listing): Response
    {
        return $listing->status === 'active' || $user?->id === $listing->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    public function delete(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    public function manageImages(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    public function changeStatus(User $user, Listing $listing): bool
    {
        return $user->id === $listing->user_id;
    }

    public function favorite(User $user, Listing $listing): Response
    {
        return $listing->status === 'active' ? Response::allow() : Response::denyAsNotFound();
    }

    public function contactSeller(?User $user, Listing $listing): Response
    {
        return $listing->status === 'active' && $user?->id !== $listing->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    public function purchase(?User $user, Listing $listing): Response
    {
        return $listing->status === 'active' && $user?->id !== $listing->user_id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
