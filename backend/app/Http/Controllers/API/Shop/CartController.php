<?php

namespace App\Http\Controllers\API\Shop;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index(Request $request): JsonResponse
    {
        $items = $this->cart->getItems($this->sessionId($request), $request->user());
        $totals = $this->cart->getTotal($items);

        return response()->json(['items' => $items, ...$totals]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'integer|min:1|max:99',
        ]);

        $item = $this->cart->addItem(
            $this->sessionId($request),
            $request->user(),
            $request->product_id,
            $request->get('quantity', 1)
        );

        return response()->json($item, 201);
    }

    public function update(Request $request, int $itemId): JsonResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:99']);

        $item = $this->cart->updateItem($this->sessionId($request), $request->user(), $itemId, $request->quantity);

        return response()->json($item);
    }

    public function destroy(Request $request, int $itemId): JsonResponse
    {
        $this->cart->removeItem($this->sessionId($request), $request->user(), $itemId);

        return response()->json(['message' => 'Item removed.']);
    }

    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);

        $coupon = Coupon::where('code', strtoupper($request->code))->where('is_active', true)->first();

        if (! $coupon || ! $coupon->isValid()) {
            return response()->json(['message' => 'Invalid or expired coupon.'], 422);
        }

        $items = $this->cart->getItems($this->sessionId($request), $request->user());
        $totals = $this->cart->getTotal($items);

        if ($totals['subtotal'] < $coupon->min_order_amount) {
            return response()->json([
                'message' => "Minimum order amount of ₱{$coupon->min_order_amount} required.",
            ], 422);
        }

        $discount = $coupon->calculateDiscount($totals['subtotal']);

        return response()->json([
            'coupon' => $coupon,
            'discount' => $discount,
        ]);
    }

    private function sessionId(Request $request): string
    {
        return $request->header('X-Session-Id', session()->getId());
    }
}
