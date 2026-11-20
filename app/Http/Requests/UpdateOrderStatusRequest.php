<?php

namespace App\Http\Requests;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return $order instanceof Order
            && ($this->user()?->can('manage', $order) ?? false);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:accepted,declined,completed'],
        ];
    }
}
