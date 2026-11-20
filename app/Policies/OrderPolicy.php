<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function manage(User $user, Order $order): bool
    {
        return $user->id === $order->seller_id;
    }

    public function updateStatus(User $user, Order $order, string $status): bool
    {
        if (! $this->manage($user, $order)) {
            return false;
        }

        return match ($order->status) {
            'pending' => in_array($status, ['accepted', 'declined'], true),
            'accepted' => $status === 'completed',
            default => false,
        };
    }

    public function cancel(User $user, Order $order): bool
    {
        return $user->id === $order->buyer_id && $order->status === 'pending';
    }
}
