<?php

namespace App\Http\Controllers\API\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PayMongoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private PayMongoService $payMongoService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items')
            ->orderByDesc('created_at')
            ->paginate(10);

        return response()->json($orders);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)
            ->with(['items', 'shippingAddress'])
            ->findOrFail($id);

        return response()->json($order);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'shipping_address.name' => 'required|string',
            'shipping_address.phone' => 'required|string',
            'shipping_address.address_line1' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.province' => 'required|string',
            'shipping_address.postal_code' => 'required|string',
            'payment_method' => 'required|in:paymongo,cod',
            'coupon_code' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
        ]);

        $sessionId = $request->header('X-Session-Id', '');
        $order = $this->orderService->createFromCart($data, $sessionId, $request->user());

        $paymentData = null;
        if ($data['payment_method'] === 'paymongo') {
            $paymentData = $this->payMongoService->createPaymentIntent($order);
        }

        return response()->json([
            'order' => $order,
            'payment_intent' => $paymentData,
        ], 201);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($id);

        abort_if(
            ! in_array($order->status, ['pending', 'processing']),
            422,
            'Order cannot be cancelled at this stage.'
        );

        $order->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Order cancelled successfully.']);
    }

    public function webhook(Request $request): JsonResponse
    {
        $this->payMongoService->handleWebhook($request->all());

        return response()->json(['received' => true]);
    }
}
