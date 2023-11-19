<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;

class OrderService
{
    protected $affiliateService;

    public function __construct(AffiliateService $affiliateService)
    {
        $this->affiliateService = $affiliateService;
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        $existingOrder = Order::where('order_id', $data['order_id'])->first();

        if ($existingOrder) {
            return;
        }

        $merchant = Merchant::firstOrCreate(['domain' => $data['merchant_domain']]);
        $customer = User::firstOrCreate([
            'email' => $data['customer_email'],
            'name' => $data['customer_name'],
            'type' => User::CUSTOMER_TYPE,
        ]);

        $affiliate = Affiliate::where('user_id', $customer->id)->first();

        if (!$affiliate) {
            $affiliate = $this->affiliateService->register($merchant, $customer->email, $customer->name, 0.1);
        }

        $order = Order::create([
            'order_id' => $data['order_id'],
            'subtotal_price' => $data['subtotal_price'],
            'discount_code' => $data['discount_code'],
            'merchant_id' => $merchant->id,
            'affiliate_id' => $affiliate->id,
        ]);
        \Log::info('Order processed successfully', ['order_id' => $data['order_id']]);
    }
}
