<?php

namespace App\Http\Controllers\Api\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Holding\ListersRequest;
use App\Http\Requests\Holding\UpdateSellOfferRequest;
use App\Http\Resources\Holding\AlternativeOffersResource;
use App\Http\Resources\Holding\BuyOffersResource;
use App\Http\Resources\Holding\HoldingsListResource;
use App\Http\Resources\Holding\PurchaseRequestsResource;
use App\Http\Resources\Market\ViewSellersResource;
use App\Models\BuyOffer;
use App\Models\Lister;
use App\Models\Portfolio;
use App\Models\SellOffer;
use App\Services\Filters\Fields\PriceFromFilter;
use App\Services\Filters\Fields\PriceToFilter;
use App\Services\Filters\FilterQuery;
use App\Services\Market\BalancePortfolioStock;
use App\Services\Market\MarketService;
use App\Services\Market\OfferService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class HoldingController extends Controller
{
    public function __construct(private OfferService $offerService)
    {
    }

    public function index(ListersRequest $request, MarketService $marketService): AnonymousResourceCollection
    {
        $data = $request->validated();
        $filterQuery = new FilterQuery();
        $query = $marketService->getHoldingsOfUser(Auth::id());

        $query = $filterQuery
            ->setFilter(new PriceFromFilter($data, 'last_sell_price', 'lastSalePriceFrom'))
            ->setFilter(new PriceToFilter($data, 'last_sell_price', 'lastSalePriceTo'))
            ->acceptFilters($query);

        return HoldingsListResource::collection($query->paginate($request->perPage()));
    }

    public function purchaseRequests(): AnonymousResourceCollection
    {
        $query = $this->offerService->getPurchaseRequestsOfUser(Auth::user()->id);
        return PurchaseRequestsResource::collection($query->paginate());
    }

    public function alternativeOffers(Lister $lister): BuyOffersResource
    {
        $shardsInfo = BalancePortfolioStock::make($lister->id, Auth::user()->id);
        $query = $this->offerService->getAlternativeOffersForUser($lister->id, Auth::user()->id);
        return BuyOffersResource::make($query->paginate())->setShardsOnSale($shardsInfo->getReservedAmount());
    }

    public function alternativeOffersForSellOffer(SellOffer $sellOffer): BuyOffersResource
    {
        $shardsInfo = BalancePortfolioStock::make($sellOffer->lister_id, Auth::user()->id);
        $query = $this->offerService->getAlternativeOffersForUser($sellOffer->lister_id, Auth::user()->id);
        $query->where('sell_offer_id', '=', $sellOffer->id);
        return BuyOffersResource::make($query->paginate())->setShardsOnSale($shardsInfo->getReservedAmount());
    }

    public function setPurchaseRequests(Lister $lister): JsonResponse
    {
        $portfolioLister = $this->offerService->togglePurchaseRequests($lister->id, Auth::user()->id);
        return response()->json(
            [
                'data' => [
                    'purchaseRequest' => $portfolioLister->purchase_request,
                    'listerId' => $lister->id
                ]
            ]
        );
    }

    public function updateSellOffer(SellOffer $sellOffer, UpdateSellOfferRequest $request): JsonResource
    {
        $sellOffer->update($request->validated());
        return ViewSellersResource::make($sellOffer);
    }

    public function removeSellOffer(SellOffer $sellOffer): JsonResponse
    {
        if ($sellOffer->user_id !== Auth::user()->id) {
            abort(403, __('Access denied'));
        }
        $this->offerService->removeSellOffer($sellOffer);
        return response()->json(['success' => true, 'message' => __("Sell offer removed successfully")]);
    }

    public function acceptAlternativeOffer(BuyOffer $buyOffer): JsonResponse
    {
        $this->offerService->acceptAlternativeOffer($buyOffer);
        return response()->json(['success' => true, 'message' => __("Alternative offer was accepted")]);
    }

    public function declineAlternativeOffer(BuyOffer $buyOffer): JsonResponse
    {
        $this->offerService->declineAlternativeOffer($buyOffer);
        return response()->json(['success' => true, 'message' => __("Alternative offer was declined")]);
    }
}
