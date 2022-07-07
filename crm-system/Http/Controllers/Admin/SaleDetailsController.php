<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\SaleDetails\SaleDetailsUpdateRequest;
use App\Http\Resources\SaleDetailsResource;
use App\Models\Building;
use App\Http\Controllers\Controller;
use App\UseCases\BuildingService;
use Illuminate\Http\Response;

class SaleDetailsController extends Controller
{

    /**
     * @OA\Get(
     *     tags={"Building Sale Details"},
     *     path="/api/admin/sale-details/{id}",
     *     summary="Get sale details of building",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Building id",
     *         required=true,
     *     ),
     *     @OA\Response(response="200", description="Get sale details of building"),
     *     @OA\Response(response="404", description="Not found sale details of building"),
     * )
     *
     * @param Building $building
     * @return SaleDetailsResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function index(Building $building)
    {
        try {
            if ($building->saleDetails()->exists()) {
                return SaleDetailsResource::make($building->saleDetails()->first());
            } else {
                return response(["error" => "Not found sale details of building"], 404);
            }
        } catch (\Exception $exception) {
            return response(["error" => "Not found sale details of building"], 404);
        }
    }

    /**
     * @OA\Post(
     *      tags={"Building Sale Details"},
     *      path="/api/admin/sale-details/{id}",
     *      summary="Update sale details of building",
     *      security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Building id",
     *         required=true,
     *     ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(property="auctionCompany", type="string", required={"true"}),
     *                 @OA\Property(property="auctionLink", type="string", required={"true"}),
     *                 @OA\Property(property="auctionDate", type="string", format="date" , required={"false"}),
     *                 @OA\Property(property="auctionIsOnline", type="string", required={"false"}),
     *                 @OA\Property(property="brokerCompany", type="string", required={"false"}),
     *                 @OA\Property(property="brokerContactName", type="string", required={"false"}),
     *                 @OA\Property(property="brokerPhone", type="string", required={"false"}),
     *                 @OA\Property(property="brokerEmail", type="string", required={"false"}),
     *                 @OA\Property(property="minBid", type="string", required={"false"}),
     *                 @OA\Property(property="propertyTourDate", type="string", format="date", required={"false"}),
     *                 @OA\Property(property="propertyIsLenderOwned", type="string", required={"false"}),
     *                 @OA\Property(property="saleTerm", type="string", required={"false"}),
     *                example={"auctionCompany":"Bednar","auctionLink":"http:\/\/www.becker.info\/minima-nesciunt-consequuntur-quidem-eum-sunt-ut-sit","auctionDate":"2007-03-20","auctionIsOnline":"1","brokerCompany":"Volkman","brokerContactName":"Leanne","brokerPhone":"13379286167","brokerEmail":"abbie.greenfelder@hotmail.com","minBid":"680","propertyTourDate":"1999-01-06","propertyIsLenderOwned":"0","saleTerm":"She had quite a conversation of it had come back and finish your story!' Alice called after it; and as for the moment she appeared on the bank, with her head! Off--' 'Nonsense!' said Alice, 'I've."}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="ok",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                      @OA\Property(property="auctionCompany", type="string"),
     *                      @OA\Property(property="auctionLink", type="string"),
     *                      @OA\Property(property="auctionDate", type="string"),
     *                      @OA\Property(property="auctionIsOnline", type="string"),
     *                      @OA\Property(property="brokerCompany", type="string"),
     *                      @OA\Property(property="brokerContactName", type="string"),
     *                      @OA\Property(property="brokerPhone", type="string"),
     *                      @OA\Property(property="brokerEmail", type="string"),
     *                      @OA\Property(property="minBid", type="string"),
     *                      @OA\Property(property="propertyTourDate", type="string"),
     *                      @OA\Property(property="propertyIsLenderOwned", type="string"),
     *                      @OA\Property(property="saleTerm", type="string")
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="ok",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                      @OA\Property(property="auctionCompany", type="string"),
     *                      @OA\Property(property="auctionLink", type="string"),
     *                      @OA\Property(property="auctionDate", type="string"),
     *                      @OA\Property(property="auctionIsOnline", type="string"),
     *                      @OA\Property(property="brokerCompany", type="string"),
     *                      @OA\Property(property="brokerContactName", type="string"),
     *                      @OA\Property(property="brokerPhone", type="string"),
     *                      @OA\Property(property="brokerEmail", type="string"),
     *                      @OA\Property(property="minBid", type="string"),
     *                      @OA\Property(property="propertyTourDate", type="string"),
     *                      @OA\Property(property="propertyIsLenderOwned", type="string"),
     *                      @OA\Property(property="saleTerm", type="string")
     *                 )
     *             )
     *         }
     *     )
     * )
     * @param SaleDetailsUpdateRequest $request
     * @param Building $building
     * @param BuildingService $buildingService
     * @return SaleDetailsResource|\Illuminate\Http\JsonResponse
     */
    public function store(SaleDetailsUpdateRequest $request, Building $building, BuildingService $buildingService)
    {
        try {
            //create new sale details of building or update exist record in db
            $saleDetailModel = $buildingService->updateSaleDetails($request, $building);
            return new SaleDetailsResource($saleDetailModel);
        } catch (\Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
