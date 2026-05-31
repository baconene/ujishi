<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShippingAddress;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createFromCart(array $data, string $sessionId, ?User $user): Order
    {
        return DB::transaction(function () use ($data, $sessionId, $user) {
            $items = CartItem::with('product')
                ->when($user, fn ($q) => $q->where('user_id', $user->id))
                ->when(! $user, fn ($q) => $q->where('session_id', $sessionId))
                ->get();

            abort_if($items->isEmpty(), 422, 'Cart is empty.');

            $subtotal = $items->sum(fn ($i) => $i->product->effective_price * $i->quantity);

            $discount = 0;
            $coupon = null;
            if (! empty($data['coupon_code'])) {
                $coupon = Coupon::where('code', $data['coupon_code'])->where('is_active', true)->first();
                if ($coupon && $coupon->isValid() && $subtotal >= $coupon->min_order_amount) {
                    $discount = $coupon->calculateDiscount($subtotal);
                    $coupon->increment('used_count');
                }
            }

            $shippingFee = (float) ($data['shipping_fee'] ?? 150);
            $total = max(0, $subtotal - $discount + $shippingFee);

            $order = Order::create([
                'user_id' => $user?->id,
                'coupon_id' => $coupon?->id,
                'order_number' => Order::generateOrderNumber(),
                'status' => 'pending',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_fee' => $shippingFee,
                'tax' => 0,
                'total' => $total,
                'payment_status' => 'pending',
                'payment_method' => $data['payment_method'] ?? 'paymongo',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'sku' => $item->product->sku,
                    'thumbnail' => $item->product->thumbnail,
                    'price' => $item->product->effective_price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->product->effective_price * $item->quantity,
                ]);

                if ($item->product->stock > 0) {
                    $item->product->decrement('stock', $item->quantity);
                }
            }

            OrderShippingAddress::create(array_merge(
                ['order_id' => $order->id],
                $data['shipping_address']
            ));

            CartItem::when($user, fn ($q) => $q->where('user_id', $user->id))
                ->when(! $user, fn ($q) => $q->where('session_id', $sessionId))
                ->delete();

            return $order->load(['items', 'shippingAddress']);
        });
    }

    public function updateStatus(Order $order, string $status): Order
    {
        $updates = ['status' => $status];

        if ($status === 'shipped') {
            $updates['shipped_at'] = now();
        } elseif ($status === 'delivered') {
            $updates['delivered_at'] = now();
        }

        $order->update($updates);

        return $order->fresh();
    }
}
