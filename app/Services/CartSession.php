<?php

namespace App\Services;

use Illuminate\Session\Store;

class CartSession
{
    private const SESSION_KEY = 'cart_listing_ids';

    public function listingIds(Store $session): array
    {
        $listingIds = $session->get(self::SESSION_KEY, []);

        if (! is_array($listingIds)) {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map('intval', $listingIds),
            fn (int $id) => $id > 0,
        )));
    }

    public function add(Store $session, int $listingId): bool
    {
        $listingIds = $this->listingIds($session);

        if (in_array($listingId, $listingIds, true)) {
            return false;
        }

        $listingIds[] = $listingId;
        $session->put(self::SESSION_KEY, $listingIds);

        return true;
    }

    public function remove(Store $session, int $listingId): void
    {
        $session->put(
            self::SESSION_KEY,
            array_values(array_filter(
                $this->listingIds($session),
                fn (int $id) => $id !== $listingId,
            )),
        );
    }

    public function clear(Store $session): void
    {
        $session->forget(self::SESSION_KEY);
    }
}
