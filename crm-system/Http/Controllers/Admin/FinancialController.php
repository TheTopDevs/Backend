<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Financial\FinancialUpdateRequest;
use App\Http\Resources\FinancialInformationResource;
use App\Models\Building;
use App\Models\FinancialInformation;
use App\UseCases\BuildingService;
use Illuminate\Http\Response;

class FinancialController extends Controller
{

    /**
     * @OA\Get(
     *     tags={"Building financial information"},
     *     path="/api/admin/financials/{id}",
     *     summary="Get financial property of building",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Building id",
     *         required=true,
     *     ),
     *     @OA\Response(response="200", description="Get financial information of building"),
     *     @OA\Response(response="404", description="Not found financial information of building"),
     * )
     *
     * @param Building $building
     * @return FinancialInformationResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function index(Building $building)
    {
        try {
            if ($building->financialInformation()->exists()) {
                return FinancialInformationResource::make($building->financialInformation()->first());
            } else {
                return response(["error" => "Not found financial information of building"], 404);
            }
        } catch (\Exception $exception) {
            return response(["error" => "Not found financial information of building"], 404);
        }
    }

    /**
     * @OA\Post(
     *      tags={"Building financial information"},
     *      path="/api/admin/financials/{id}",
     *      summary="Update financial information of building",
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
     *                 @OA\Property(property="loanBalance", type="integer", required={"false"}),
     *                 @OA\Property(property="accruedInterest", type="integer", required={"false"}),
     *                 @OA\Property(property="penalty", type="integer", required={"false"}),
     *                 @OA\Property(property="lateFee", type="integer", required={"false"}),
     *                 @OA\Property(property="lenderWriteDown", type="integer", required={"false"}),
     *                 @OA\Property(property="totalDebt", type="integer", required={"false"}),
     *                 @OA\Property(property="taxAssisment", type="integer", required={"false"}),
     *                 @OA\Property(property="taxBill", type="integer", required={"false"}),
     *                 @OA\Property(property="lenderBankName", type="string", required={"false"}),
     *                 @OA\Property(property="lenderAddress", type="string", required={"false"}),
     *                 @OA\Property(property="lenderPhone", type="string", required={"false"}),
     *                 @OA\Property(property="lenderEmail", type="string", required={"false"}),
     *                 @OA\Property(property="specialServicerCompanyName", type="string", required={"false"}),
     *                 @OA\Property(property="specialServicerPhone", type="string", required={"false"}),
     *                 @OA\Property(property="specialServicerEmail", type="email", required={"false"}),
     *                 @OA\Property(property="repositioningBudget", type="integer", required={"false"}),
     *                 @OA\Property(property="stabilizedBudget", type="integer", required={"false"}),
     *                 @OA\Property(property="maxPrice", type="integer", required={"false"}),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="ok",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                      @OA\Property(property="loanBalance", type="integer"),
     *                      @OA\Property(property="accruedInterest", type="integer"),
     *                      @OA\Property(property="penalty", type="integer"),
     *                      @OA\Property(property="lateFee", type="integer"),
     *                      @OA\Property(property="lenderWriteDown", type="integer"),
     *                      @OA\Property(property="totalDebt", type="integer"),
     *                      @OA\Property(property="taxAssisment", type="integer"),
     *                      @OA\Property(property="taxBill", type="string"),
     *                      @OA\Property(property="lenderBankName", type="string"),
     *                      @OA\Property(property="lenderAddress", type="string"),
     *                      @OA\Property(property="lenderPhone", type="string"),
     *                      @OA\Property(property="lenderEmail", type="string"),
     *                      @OA\Property(property="specialServicerCompanyName", type="string"),
     *                      @OA\Property(property="specialServicerPhone", type="string"),
     *                      @OA\Property(property="specialServicerEmail", type="string"),
     *                      @OA\Property(property="repositioningBudget", type="integer"),
     *                      @OA\Property(property="stabilizedBudget", type="integer"),
     *                      @OA\Property(property="maxPrice", type="integer"),
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="ok",
     *         content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                      @OA\Property(property="loanBalance", type="integer"),
     *                      @OA\Property(property="accruedInterest", type="integer"),
     *                      @OA\Property(property="penalty", type="integer"),
     *                      @OA\Property(property="lateFee", type="integer"),
     *                      @OA\Property(property="lenderWriteDown", type="integer"),
     *                      @OA\Property(property="totalDebt", type="integer"),
     *                      @OA\Property(property="taxAssisment", type="integer"),
     *                      @OA\Property(property="taxBill", type="string"),
     *                      @OA\Property(property="lenderBankName", type="string"),
     *                      @OA\Property(property="lenderAddress", type="string"),
     *                      @OA\Property(property="lenderPhone", type="string"),
     *                      @OA\Property(property="lenderEmail", type="string"),
     *                      @OA\Property(property="specialServicerCompanyName", type="string"),
     *                      @OA\Property(property="specialServicerPhone", type="string"),
     *                      @OA\Property(property="specialServicerEmail", type="string"),
     *                      @OA\Property(property="repositioningBudget", type="integer"),
     *                      @OA\Property(property="stabilizedBudget", type="integer"),
     *                      @OA\Property(property="maxPrice", type="integer"),
     *                 )
     *             )
     *         }
     *     )
     * )
     * @param FinancialUpdateRequest $request
     * @param Building $building
     * @param BuildingService $buildingService
     * @return FinancialInformationResource|\Illuminate\Http\JsonResponse
     */
    public function store(FinancialUpdateRequest $request, Building $building, BuildingService $buildingService)
    {
        try {
            /** @var FinancialInformation $financialInformation */
            $financialInformation = $buildingService->updateFinancialInformation($request, $building);
            return new FinancialInformationResource($financialInformation);
        } catch (\Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
