<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AffiliateService
{
    protected $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt(str_random(16)),
            'type' => User::AFFILIATE_TYPE,
        ]);

        $affiliate = Affiliate::create([
            'user_id' => $user->id,
            'merchant_id' => $merchant->id,
            'commission_rate' => $commissionRate,
        ]);

        try {
            Mail::to($email)->send(new AffiliateCreated($affiliate));
        } catch (\Exception $exception) {
            throw new AffiliateCreateException('Error sending affiliate creation email');
        }

        return $affiliate;
    }
}
