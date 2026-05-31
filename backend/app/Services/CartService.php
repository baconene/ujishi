<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;

class CartService
{
    public function getItems(string $sessionId, ?User $user): Collection
    {
        return CartItem::with('product.images')
            ->when($user, fn ($q) => $q->where('user_id', $user->id))
            ->when(! $user, fn ($q) => $q->where('session_id', $sessionId))
            ->get();
    }

    public function addItem(string $sessionId, ?User $user, int $productId, int $quantity = 1): CartItem
    {
        $product = Product::active()->findOrFail($productId);

        $query = CartItem::where('product_id', $productId);
        if ($user) {
            $query->where('user_id', $user->id);
        } else {
            $query->where('session_id', $sessionId);
        }

        $item = $query->first();

        if ($item) {
            $item->increment('quantity', $quantity);

            return $item->fresh('product.images');
        }

        return CartItem::create([
            'session_id' => $user ? null : $sessionId,
            'user_id' => $user?->id,
            'product_id' => $product->id,
            'quantity' => $quantity,
        ])->load('product.images');
    }

    public function updateItem(string $sessionId, ?User $user, int $itemId, int $quantity): CartItem
    {
        $item = $this->findItem($sessionId, $user, $itemId);
        $item->update(['quantity' => $quantity]);

        return $item->fresh('product.images');
    }

    public function removeItem(string $sessionId, ?User $user, int $itemId): void
    {
        $this->findItem($sessionId, $user, $itemId)->delete();
    }

    public function clear(string $sessionId, ?User $user): void
    {
        CartItem::when($user, fn ($q) => $q->where('user_id', $user->id))
            ->when(! $user, fn ($q) => $q->where('session_id', $sessionId))
            ->delete();
    }

    public function mergeGuestCart(string $sessionId, User $user): void
    {
        CartItem::where('session_id', $sessionId)->each(function (CartItem $guestItem) use ($user) {
            $existing = CartItem::where('user_id', $user->id)
                ->where('product_id', $guestItem->product_id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $guestItem->quantity);
                $guestItem->delete();
            } else {
                $guestItem->update(['session_id' => null, 'user_id' => $user->id]);
            }
        });
    }

    public function getTotal(Collection $items): array
    {
        $subtotal = $items->sum(fn (CartItem $item) => $item->product->effective_price * $item->quantity);

        return [
            'subtotal' => round($subtotal, 2),
            'count' => $items->sum('quantity'),
        ];
    }

    private function findItem(string $sessionId, ?User $user, int $itemId): CartItem
    {
        return CartItem::where('id', $itemId)
            ->when($user, fn ($q) => $q->where('user_id', $user->id))
            ->when(! $user, fn ($q) => $q->where('session_id', $sessionId))
            ->firstOrFail();
    }
}
