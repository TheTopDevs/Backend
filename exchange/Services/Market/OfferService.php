<?php

namespace App\Services\Market;

use App\Models\BuyOffer;
use App\Models\Portfolio;
use App\Models\SellOffer;
use App\Models\User;
use App\Services\TransactionBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OfferService
{
    public function makeOfferForShardHolder(int $listerId, int $userId, int $amount, float $price): BuyOffer
    {
        return BuyOffer::create([
            'price' => $price,
            'amount' => $amount,
            //author of buy-offer
            'user_id' => Auth::user()->id,
            'lister_id' => $listerId,
            //who is owner of portfolio-stock, we create offer for him
            'for_user_id' => $userId,
            'status' => BuyOffer::STATUS_CREATED,
        ]);
    }

    public function removeSellOffer(SellOffer $sellOffer): void
    {
        DB::beginTransaction();
        try {
            //update status on related - alternative_offers
            $sellOffer->alternativeOffers()->update(['status' => BuyOffer::STATUS_DECLINED]);
            //update offer status
            $sellOffer->update(['status' => SellOffer::STATUS_REMOVED]);
            //decrement reserved shard on portfolio-stock
            DB::table('portfolios')
                ->where('user_id', Auth::user()->id)
                ->where('lister_id', $sellOffer->lister_id)
                ->decrement('reserved_shares', $sellOffer->amount);
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function makeAlternativeOffer(SellOffer $sellOffer, float $price): BuyOffer
    {
        //save count of alternative offers
        $sellOffer->active_alternative_offers_count++;
        $sellOffer->save();
        //make buy offer(alternative offer) from source(sell-offer)
        return BuyOffer::create([
            //author of buy-offer
            'user_id' => Auth::user()->id,
            'lister_id' => $sellOffer->lister_id,
            'amount' => $sellOffer->amount,
            'price' => $price,
            'sell_offer_id' => $sellOffer->id,
            'status' => BuyOffer::STATUS_CREATED,
            //offer will be canceled after 48 hours, if lister no accept
            'expires_alternative_offer' => now()->addHours(BuyOffer::EXPIRE_IN),
        ]);
    }

    public function getOffersFromCard(User $user): array|Collection
    {
        return SellOffer::with(['user'])
            ->where('reserved_user_id', $user->id)
            ->where('reserved_until', '>', now())
            ->where('status', SellOffer::STATUS_RESERVED)
            ->get();
    }

    public function addOfferToCard(array $sellOffersIds, User $user): int
    {
        //add sell_offers to card of user, offers is reserved for user
        return SellOffer::query()
            ->where('status', SellOffer::STATUS_CREATED)
            ->whereIn('id', $sellOffersIds)
            ->update([
                'reserved_user_id' => $user->id,
                'reserved_until' => now()->addMinutes(60),
                'status' => SellOffer::STATUS_RESERVED,
            ]);
    }

    public function removeOfferFromUserCard(array $sellOffersIds): int
    {
        return SellOffer::query()
            ->where('status', SellOffer::STATUS_RESERVED)
            ->whereIn('id', $sellOffersIds)
            ->update([
                'reserved_user_id' => 0,
                'reserved_until' => null,
                'status' => SellOffer::STATUS_CREATED,
            ]);
    }

    public function getPurchaseRequestsOfUser(int $userId): Builder
    {
        return BuyOffer::query()
            ->with(['lister', 'user'])
            ->where('for_user_id', '=', $userId)
            ->where('status', '=', BuyOffer::STATUS_CREATED)
            ->latest();
    }

    public function togglePurchaseRequests(int $listerId, int $userId): Portfolio
    {
        $portfolioLister = Portfolio::query()
            ->where('lister_id', '=', $listerId)
            ->where('user_id', '=', $userId)
            ->first();
        $portfolioLister->purchase_request = !$portfolioLister->purchase_request;
        $portfolioLister->save();
        return $portfolioLister;
    }

    public function getAlternativeOffersForUser(int $listerId, int $userId): Builder
    {
        return BuyOffer::query()
            ->with(['user', 'sellOffer'])
            ->where('status', '=', BuyOffer::STATUS_CREATED)
            ->where('lister_id', '=', $listerId)
            ->whereHas('sellOffer', function (Builder $query) use ($listerId, $userId) {
                return $query->where('status', '=', SellOffer::STATUS_CREATED)
                    ->where('user_id', '=', $userId)
                    ->where('lister_id', '=', $listerId)
                    ->where('allow_alternative_offers', '=', 1);
            });
    }

    public function acceptAlternativeOffer(BuyOffer $buyOffer): void
    {
        DB::beginTransaction();
        try {
            $buyOffer->update(['status' => BuyOffer::STATUS_ACCEPTED]);
            //decline others buy_offers related with sell_offer
            BuyOffer::query()
                ->where('sell_offer_id', '=', $buyOffer->sell_offer_id)
                ->where('id', '<>', $buyOffer->id)
                ->where('status', '=', BuyOffer::STATUS_CREATED)
                ->update(['status' => BuyOffer::STATUS_CANCEL_HOLD_MONEY]);
            //TODO make cron-task for un_block money on card,if buy_offer not accepted
            $buyOffer->sellOffer()->update(['status' => SellOffer::STATUS_ACCEPTED]);
            //transfer portfolio-balance of lister
            app()->make(TransactionBuilder::class)
                ->setUnitPrice($buyOffer->price)
                ->setShards($buyOffer->amount)
                ->setListerId($buyOffer->lister_id)
                ->setTransferFrom($buyOffer->sellOffer()->user_id)
                ->setTransferTo($buyOffer->user_id)
                ->execute();
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
    }

    public function declineAlternativeOffer(BuyOffer $buyOffer): void
    {
        $buyOffer->update(['status' => BuyOffer::STATUS_CANCEL_HOLD_MONEY]);
        //update count alternative offers for sell_offer
        $buyOffer->sellOffer()->decrement('active_alternative_offers_count');
    }

    public function unlockSellOffersFromCard(): int
    {
        return SellOffer::query()
            ->where('reserved_until', '<', now())
            ->where('status', SellOffer::STATUS_RESERVED)
            ->update([
                'reserved_user_id' => 0,
                'reserved_until' => null,
                'status' => SellOffer::STATUS_CREATED,
            ]);
    }
}
