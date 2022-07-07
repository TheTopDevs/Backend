<?php

namespace App\Http\Controllers\Api\Market;

use App\Http\Controllers\Controller;
use App\Http\Requests\Market\AddOffersToCard;
use App\Http\Requests\Market\DeleteOffersFromCard;
use App\Http\Resources\Market\SellOfferResource;
use App\Models\Permission;
use App\Services\Market\OfferService;
use Illuminate\Support\Facades\Auth;

class CardController extends Controller
{
    public function __construct(private OfferService $offerService)
    {
        $this->middleware(['permission:' . Permission::MAKE_OFFER]);
    }

    public function addOffersToCard(AddOffersToCard $offerToCard)
    {
        $ids = $offerToCard->validated('sell_offers_ids');
        //reserved all selected sell-offers by user
        $this->offerService->addOfferToCard($ids, Auth::user());
        return SellOfferResource::collection($this->offerService->getOffersFromCard(Auth::user()));
    }

    public function viewCard()
    {
        return SellOfferResource::collection($this->offerService->getOffersFromCard(Auth::user()));
    }

    public function deleteFromCard(DeleteOffersFromCard $request)
    {
        $id = $request->validated('id');
        $this->offerService->removeOfferFromUserCard([$id]);
        return SellOfferResource::collection($this->offerService->getOffersFromCard(Auth::user()));
    }
}
