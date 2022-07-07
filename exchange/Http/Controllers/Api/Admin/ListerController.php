<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Lister\InitPortfolioStockRequest;
use App\Http\Resources\User\ListerPortfolioStockResource;
use App\Models\Permission;
use App\Models\Role;
use App\Services\ListerService;
use App\Services\Market\MarketService;

class ListerController extends Controller
{

    public function __construct()
    {
        $this->middleware(['permission:' . Permission::INIT_PORTFOLIO_STOCK_LISTER])->only('initListerPortfolioStock');
    }


    /** make portfolio stock for lister */
    public function initListerPortfolioStock(
        InitPortfolioStockRequest $request,
        MarketService $marketService,
        ListerService $listerService
    ) {
        $data = $request->validated();

        $lister = $listerService->getListerForUser($data['user_id']);

        //Lister_profile must be approved by admin, can create offers
        if (!$lister->user->hasPermissionTo(Permission::MAKE_OFFER, 'web')) {
            abort(401, __("Lister profile status must be approved"));
        }

        //check - already exist lister portfolio-stock in db
        if ($listerService->listerPortfolioStockExist($lister->id)) {
            abort(401, __("Init portfolio stock already exist for selected lister"));
        }

        //init portfolio stock for lister
        $marketService->initPortfolioStockForLister($lister, $data['amount'], $data['price'], $data['rating']);

        //get users with lister portfolio stock
        return ListerPortfolioStockResource::collection($listerService->getPortfolioStocksForLister($lister->id));
    }
}
