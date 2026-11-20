<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuyerOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->buyerOrders()
            ->with(['seller:id,name', 'items'])
            ->latest()
            ->paginate(12);

        return view('orders.index', compact('orders'));
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);
        $order->update(['status' => 'cancelled']);

        return redirect()->route('dashboard.orders')->with('status', 'Order request cancelled.');
    }
}
