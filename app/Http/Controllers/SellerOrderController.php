<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SellerOrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = $request->user()->sellerOrders()
            ->with(['buyer:id,name,email', 'items'])
            ->latest()
            ->paginate(12);

        return view('orders.sales', compact('orders'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $status = $request->validated('status');
        $this->authorize('updateStatus', [$order, $status]);
        $order->update(['status' => $status]);

        $message = match ($status) {
            'accepted' => 'Order request accepted.',
            'declined' => 'Order request declined.',
            'completed' => 'Order request marked as completed.',
        };

        return redirect()->route('dashboard.sales')->with('status', $message);
    }
}
