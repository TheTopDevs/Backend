<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Systems\SystemsUpdateRequest;
use App\Http\Resources\SystemsResource;
use App\Models\Building;
use App\UseCases\BuildingService;
use Illuminate\Http\Request;

class SystemsController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Building Systems Attributes Details"},
     *     path="/api/admin/systems/{id}",
     *     summary="Get systems property of building",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Building id",
     *         required=true,
     *     ),
     *     @OA\Response(response="200", description="Get system information of building"),
     *     @OA\Response(response="404", description="Not found system information of building"),
     * )
     * @param Building $building
     * @return SystemsResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function index(Building $building)
    {
        try {
            if ($building->systems()->exists()) {
                return SystemsResource::make($building->systems()->first());
            } else {
                return response(["error" => "Not found systems information of building"], 404);
            }
        } catch (\Exception $exception) {
            return response(["error" => "Not found systems information of building"], 404);
        }
    }

    /**
     * @OA\Post(
     *      tags={"Building Systems Attributes Details"},
     *      path="/api/admin/systems/{id}",
     *      summary="Update systems details of building",
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
     *                 @OA\Property(property="hotWater", type="string", required={"false"}),
     *                 @OA\Property(property="roomHVAC", type="string", required={"false"}),
     *                 @OA\Property(property="commonAreaHVAC", type="string", required={"false"}),
     *                 @OA\Property(property="otherHVAC", type="string", required={"false"}),
     *                 @OA\Property(property="elevator", type="string", required={"false"}),
     *                 @OA\Property(property="escalator", type="string", required={"false"}),
     *                 @OA\Property(property="sprinkler", type="string", required={"false"}),
     *                 @OA\Property(property="buildingAccsess", type="string", required={"false"}),
     *                 @OA\Property(property="roomAccess", type="string", required={"false"}),
     *                 @OA\Property(property="keySystem", type="string", required={"false"}),
     *                 @OA\Property(property="knoxBox", type="string", required={"false"}),
     *                 @OA\Property(property="greaceTraps", type="string", required={"false"}),
     *                 @OA\Property(property="laundaryFacility", type="string", required={"false"}),
     *                 @OA\Property(property="kitchenFireSystem", type="string", required={"false"}),
     *                 @OA\Property(property="fireAlarmSystem", type="string", required={"false"}),
     *                 @OA\Property(property="buildingAlarmSystem", type="string", required={"false"}),
     *                 @OA\Property(property="roofType", type="string", required={"false"}),
     *                 @OA\Property(property="landscapeIrrigation", type="string", required={"false"}),
     *                 @OA\Property(property="waterFountain", type="string", required={"false"}),
     *                 @OA\Property(property="poolSpaSystem", type="string", required={"false"}),
     *                 @OA\Property(property="exteriorFacadeType", type="string", required={"false"}),
     *                 @OA\Property(property="lighting", type="string", required={"false"}),
     *                 @OA\Property(property="sewerEjectorPit", type="string", required={"false"}),
     *                 @OA\Property(property="fiberCable", type="string", required={"false"}),
     *                 @OA\Property(property="stormWaterManagementPond", type="string", required={"false"}),
     *                 @OA\Property(property="undergroundTanks", type="string", required={"false"}),
     *                 @OA\Property(property="naturalDisasterArea", type="string", required={"false"}),
     *                 @OA\Property(property="utilitiesWater", type="string", required={"false"}),
     *                 @OA\Property(property="utilitiesSewer", type="string", required={"false"}),
     *                 @OA\Property(property="utilitiesSwm", type="string", required={"false"}),
     *                 @OA\Property(property="utilitiesGas", type="string", required={"false"}),
     *                 @OA\Property(property="utilitiesPropone", type="string", required={"false"}),
     *                 @OA\Property(property="utilitiesElectric", type="string", required={"false"}),
     *                 @OA\Property(property="utilitiesMetering", type="string", required={"false"}),
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
     *                    @OA\Property(property="hotWater", type="string"),
     *                    @OA\Property(property="roomHVAC", type="string"),
     *                    @OA\Property(property="commonAreaHVAC", type="string"),
     *                    @OA\Property(property="otherHVAC", type="string"),
     *                    @OA\Property(property="elevator", type="string"),
     *                    @OA\Property(property="escalator", type="string"),
     *                    @OA\Property(property="sprinkler", type="string"),
     *                    @OA\Property(property="buildingAccsess", type="string"),
     *                    @OA\Property(property="roomAccess", type="string"),
     *                    @OA\Property(property="keySystem", type="string"),
     *                    @OA\Property(property="knoxBox", type="string"),
     *                    @OA\Property(property="greaceTraps", type="string"),
     *                    @OA\Property(property="laundaryFacility", type="string"),
     *                    @OA\Property(property="kitchenFireSystem", type="string"),
     *                    @OA\Property(property="fireAlarmSystem", type="string"),
     *                    @OA\Property(property="buildingAlarmSystem", type="string"),
     *                    @OA\Property(property="roofType", type="string"),
     *                    @OA\Property(property="landscapeIrrigation", type="string"),
     *                    @OA\Property(property="waterFountain", type="string"),
     *                    @OA\Property(property="poolSpaSystem", type="string"),
     *                    @OA\Property(property="exteriorFacadeType", type="string"),
     *                    @OA\Property(property="lighting", type="string"),
     *                    @OA\Property(property="sewerEjectorPit", type="string"),
     *                    @OA\Property(property="fiberCable", type="string"),
     *                    @OA\Property(property="stormWaterManagementPond", type="string"),
     *                    @OA\Property(property="undergroundTanks", type="string"),
     *                    @OA\Property(property="naturalDisasterArea", type="string"),
     *                    @OA\Property(property="utilitiesWater", type="string"),
     *                    @OA\Property(property="utilitiesSewer", type="string"),
     *                    @OA\Property(property="utilitiesSwm", type="string"),
     *                    @OA\Property(property="utilitiesGas", type="string"),
     *                    @OA\Property(property="utilitiesPropone", type="string"),
     *                    @OA\Property(property="utilitiesElectric", type="string"),
     *                    @OA\Property(property="utilitiesMetering", type="string"),
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
     *                    @OA\Property(property="hotWater", type="string"),
     *                    @OA\Property(property="roomHVAC", type="string"),
     *                    @OA\Property(property="commonAreaHVAC", type="string"),
     *                    @OA\Property(property="otherHVAC", type="string"),
     *                    @OA\Property(property="elevator", type="string"),
     *                    @OA\Property(property="escalator", type="string"),
     *                    @OA\Property(property="sprinkler", type="string"),
     *                    @OA\Property(property="buildingAccsess", type="string"),
     *                    @OA\Property(property="roomAccess", type="string"),
     *                    @OA\Property(property="keySystem", type="string"),
     *                    @OA\Property(property="knoxBox", type="string"),
     *                    @OA\Property(property="greaceTraps", type="string"),
     *                    @OA\Property(property="laundaryFacility", type="string"),
     *                    @OA\Property(property="kitchenFireSystem", type="string"),
     *                    @OA\Property(property="fireAlarmSystem", type="string"),
     *                    @OA\Property(property="buildingAlarmSystem", type="string"),
     *                    @OA\Property(property="roofType", type="string"),
     *                    @OA\Property(property="landscapeIrrigation", type="string"),
     *                    @OA\Property(property="waterFountain", type="string"),
     *                    @OA\Property(property="poolSpaSystem", type="string"),
     *                    @OA\Property(property="exteriorFacadeType", type="string"),
     *                    @OA\Property(property="lighting", type="string"),
     *                    @OA\Property(property="sewerEjectorPit", type="string"),
     *                    @OA\Property(property="fiberCable", type="string"),
     *                    @OA\Property(property="stormWaterManagementPond", type="string"),
     *                    @OA\Property(property="undergroundTanks", type="string"),
     *                    @OA\Property(property="naturalDisasterArea", type="string"),
     *                    @OA\Property(property="utilitiesWater", type="string"),
     *                    @OA\Property(property="utilitiesSewer", type="string"),
     *                    @OA\Property(property="utilitiesSwm", type="string"),
     *                    @OA\Property(property="utilitiesGas", type="string"),
     *                    @OA\Property(property="utilitiesPropone", type="string"),
     *                    @OA\Property(property="utilitiesElectric", type="string"),
     *                    @OA\Property(property="utilitiesMetering", type="string"),
     *                 )
     *             )
     *         }
     *     )
     * )
     * @param SystemsUpdateRequest $request
     * @param Building $building
     * @param BuildingService $buildingService
     * @return \Illuminate\Http\JsonResponse|SystemsResource
     */

    public function store(SystemsUpdateRequest $request, Building $building, BuildingService $buildingService): \Illuminate\Http\JsonResponse|SystemsResource
    {
        try {
            //create new system params details of building or update exist record in db
            $saleDetailModel = $buildingService->updateSystemAttributes($request, $building);
            return new SystemsResource($saleDetailModel);
        } catch (\Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
