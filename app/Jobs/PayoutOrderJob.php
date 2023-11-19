<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\ApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PayoutOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        public Order $order
    ) {}

    /**
     * Use the API service to send a payout of the correct amount.
     * Note: The order status must be paid if the payout is successful, or remain unpaid in the event of an exception.
     *
     * @return void
     */
    public function handle(ApiService $apiService)
    {
        // TODO: Complete this method
        try {
            $amountToPayout = $this->calculatePayoutAmount($this->order);
            $apiService->sendPayout($this->order->affiliate->user->email, $amountToPayout);

            DB::transaction(function () {
                $this->order->update(['paid' => true]);
            });
        } catch (\Exception $exception) {
            \Log::error("Error processing payout for order {$this->order->order_id}: {$exception->getMessage()}");
        }
    }

    protected function calculatePayoutAmount(Order $order): float
    {
        return $order->subtotal_price * $order->affiliate->commission_rate;
    }

}
