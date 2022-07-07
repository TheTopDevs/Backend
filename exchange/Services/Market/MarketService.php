<?php

namespace App\Services\Market;

use App\Models\BuyOffer;
use App\Models\Lister;
use App\Models\Portfolio;
use App\Models\SellOffer;
use App\Models\User;
use App\Services\Market\BalancePortfolioStock;
use App\Services\TransactionBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\User\UserService;


class MarketService
{
    public function __construct(
        private UserService $userService,
        private TransactionBuilder $transactionBuilder
    ) {
    }


    /** Init portfolio-stock for lister
     *  calculate percent-fee for hastex user from total portfolio-stock
     *  transfer portfolio-stock from hashtex user to lister-user
     */
    public function initPortfolioStockForLister(Lister $lister, int $amount, float $price, string $rating)
    {
        DB::beginTransaction();

        try {
            //update lister market params
            $lister->update([
                'init_sale_price' => $price,
                'rating' => $rating,
                //save delay sells start lister date
                'start_of_sales' => now()->addDays(config('auth.hashtex.lister.delay_start_sells')),
                //save total amount in system
                'total_amount' => $amount,
            ]);
            $hashtexUser = $this->userService->getHashtexUser();
            //$amount - total count of lister stock portfolio after created in hashtex platform
            //some fee percent transfered to hashtex-user, other amount transfered to lister
            $listerAmount = $amount - getPercentFromNumber($amount, config('auth.hashtex.fee.init_lister_stock'));

            //add hashtext-user all amount to balance stock portfolio
            Portfolio::create([
                'lister_id' => $lister->id,
                'user_id' => $hashtexUser->id,
                'amount' => $amount,
            ]);

            //fix transaction operation
            $this->transactionBuilder
                ->setListerId($lister->id)
                ->setShards($listerAmount)
                ->setTransferFrom($hashtexUser->id)
                ->setTransferTo($lister->user->id)
                ->setUnitPrice($price)
                ->execute();

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    /**
     * if return 0 - price can be set any price
     * if return not 0 - it`s fixed price for lister
     */
    public function getFixedPriceForListerOffers(Lister $lister): float
    {
        //we use fixed price in lister offers
        //use fixed price if total count of portfolio-stock(hashtex-user) is zero
        //or lister can start sell after 30 days
        $hashtexUser = $this->userService->getHashtexUser();
        $hashtexPortfolioStock = BalancePortfolioStock::make($lister->id, $hashtexUser->id);
        if ($lister->canSetCustomPrice() || $hashtexPortfolioStock->getTotalAmount() === 0) {
            return 0;
        }
        return $lister->init_sale_price;
    }

    public function listerMakeSellsOffer(int $amount, float $price, bool $alternativeProposals): SellOffer
    {
        $user = Auth::user();
        $offer = SellOffer::create([
            'amount' => $amount,
            'price' => $price,
            'allow_alternative_offers' => $alternativeProposals,
            'user_id' => $user->id,
            'lister_id' => $user->lister->id,
        ]);
        //update reserved_shares in portfolio-stock
        DB::table('portfolios')
            ->where('user_id', $user->id)
            ->where('lister_id', $user->lister->id)
            ->increment('reserved_shares', $amount);

        return $offer;
    }

    public function getHoldingsOfUser(int $userId): Builder
    {
        return Portfolio::query()
            ->with(['lister', 'sellOffers'])
            ->where('user_id', $userId);
    }
}
