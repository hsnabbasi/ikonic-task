<?php

namespace App\Http\Controllers;

use App\Services\AffiliateService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Pass the necessary data to the process order method
     *
     * @param  Request $request
     * @return JsonResponse
     */

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|integer',
            'status' => 'required|string',
        ]);

        $orderId = $request->input('order_id');
        $status = $request->input('status');

        $this->orderService->processOrder($orderId, $status);

        return response()->json(['message' => 'Order processed successfully']);
    }
}
