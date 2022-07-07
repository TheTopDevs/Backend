<?php

namespace App\Http\Controllers\Api\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Lister\MakeSellOfferRequest;
use App\Http\Requests\Market\AlternativeOffersRequest;
use App\Http\Requests\Market\OfferForShardHoldersRequest;
use App\Http\Resources\Market\AlternativeOffersResource;
use App\Http\Resources\Market\ShardManagmentResource;
use App\Models\Lister;
use App\Models\Permission;
use App\Models\SellOffer;
use App\Services\Market\BalancePortfolioStock;
use App\Services\Market\MarketService;
use App\Services\Market\OfferService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OfferController extends Controller
{

    public function __construct()
    {
        $this->middleware(['permission:' . Permission::MAKE_OFFER]);
    }

    public function shardManagment()
    {
        $lister = Auth::user()->lister;
        $portfolioStock = BalancePortfolioStock::make($lister->id, Auth::user()->id);
        return ShardManagmentResource::make($lister)->setPortfolioStock($portfolioStock);
    }

    public function makeBuyOfferForShardHolders(
        Lister $lister,
        OfferForShardHoldersRequest $request,
        OfferService $offerService
    ) {
        $data = $request->validated();
        $offers = [];
        foreach ($data['buy_offers_shard_holders'] as $buyOffer) {
            $buyOffer = $offerService->makeOfferForShardHolder(
                $lister->id,
                $buyOffer['user_id'],
                $buyOffer['amount'],
                $buyOffer['price']
            );
            $offers[] = $buyOffer;
        }
        return AlternativeOffersResource::collection($offers);
    }

    public function makeAlternativeOffers(AlternativeOffersRequest $request, OfferService $offerService)
    {
        $data = $request->validated();
        DB::beginTransaction();
        $offers = [];
        try {
            foreach ($data['alternative_offers'] as $alternativeOffer) {
                //sell_offer can be (not reserved, not complete, not declined)
                $sellOffer = SellOffer::find($alternativeOffer['sell_offer_id']);
                $offers[] = $offerService->makeAlternativeOffer($sellOffer, $alternativeOffer['price']);
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
        return AlternativeOffersResource::collection($offers);
    }

    public function listerMakeSellsOffer(MakeSellOfferRequest $request, MarketService $marketService)
    {
        $data = $request->validated();
        DB::beginTransaction();
        try {
            foreach ($data['offers'] as $offer) {
                $sellOffer = $marketService->listerMakeSellsOffer(
                    $offer['amount'],
                    $offer['price'],
                    $offer['allow_alternative_offers']
                );
                if (!$sellOffer) {
                    DB::rollback();
                    abort(401, "Sell offer was not created");
                }
            }
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollback();
            throw $exception;
        }
        return response()->json([
            'message' => __('Your sell offer number has been submitted.
                Your sell offers were placed on Market, you will receive an email confirmation and updates on your offer status.'),
            'success' => true,
        ]);
    }
}
