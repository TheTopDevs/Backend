<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attributes\AttributesUpdateRequest;
use App\Http\Resources\AttributesResource;
use App\Models\Attributes;
use App\Models\Building;
use App\UseCases\BuildingService;
use Illuminate\Http\Response;

class AttributesController extends Controller
{

    /**
     * @OA\Post(
     *      tags={"Building Attributes"},
     *      path="/api/admin/attributes/{id}",
     *      summary="Update attributes details of building",
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
     *                 @OA\Property(property="lobby", type="bool", required={"true"}),
     *                 @OA\Property(property="frontDesk", type="bool", required={"true"}),
     *                 @OA\Property(property="storageRoomConcierge", type="bool", required={"true"}),
     *                 @OA\Property(property="storage", type="bool", required={"true"}),
     *                 @OA\Property(property="cafe", type="bool", required={"true"}),
     *                 @OA\Property(property="restaurant", type="bool", required={"true"}),
     *                 @OA\Property(property="bar", type="bool", required={"true"}),
     *                 @OA\Property(property="indoorPool", type="bool", required={"true"}),
     *                 @OA\Property(property="outdoorPool", type="bool", required={"true"}),
     *                 @OA\Property(property="meetingRoom", type="bool", required={"true"}),
     *                 @OA\Property(property="sprinkleSystem", type="bool", required={"true"}),
     *                 @OA\Property(property="elevators", type="bool", required={"true"}),
     *                 @OA\Property(property="rooftopDeck", type="bool", required={"true"}),
     *                 @OA\Property(property="fitnessCenter", type="bool", required={"true"}),
     *                 @OA\Property(property="publicWater", type="bool", required={"true"}),
     *                 @OA\Property(property="publicSewer", type="bool", required={"true"}),
     *                 @OA\Property(property="greenCertification", type="bool", required={"true"}),
     *                 @OA\Property(property="otherAmenities", type="string", required={"false"}),
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
     *                      @OA\Property(property="lobby", type="integer"),
     *                      @OA\Property(property="frontDesk", type="integer"),
     *                      @OA\Property(property="storageRoomConcierge", type="integer"),
     *                      @OA\Property(property="storage", type="integer"),
     *                      @OA\Property(property="cafe", type="integer"),
     *                      @OA\Property(property="restaurant", type="integer"),
     *                      @OA\Property(property="bar", type="integer"),
     *                      @OA\Property(property="indoorPool", type="integer"),
     *                      @OA\Property(property="outdoorPool", type="integer"),
     *                      @OA\Property(property="meetingRoom", type="integer"),
     *                      @OA\Property(property="sprinkleSystem", type="integer"),
     *                      @OA\Property(property="elevators", type="integer"),
     *                      @OA\Property(property="rooftopDeck", type="integer"),
     *                      @OA\Property(property="fitnessCenter", type="integer"),
     *                      @OA\Property(property="publicWater", type="integer"),
     *                      @OA\Property(property="publicSewer", type="integer"),
     *                      @OA\Property(property="greenCertification", type="integer"),
     *                      @OA\Property(property="otherAmenities", type="string"),
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
     *                      @OA\Property(property="lobby", type="integer"),
     *                      @OA\Property(property="frontDesk", type="integer"),
     *                      @OA\Property(property="storageRoomConcierge", type="integer"),
     *                      @OA\Property(property="storage", type="integer"),
     *                      @OA\Property(property="cafe", type="integer"),
     *                      @OA\Property(property="restaurant", type="integer"),
     *                      @OA\Property(property="bar", type="integer"),
     *                      @OA\Property(property="indoorPool", type="integer"),
     *                      @OA\Property(property="outdoorPool", type="integer"),
     *                      @OA\Property(property="meetingRoom", type="integer"),
     *                      @OA\Property(property="sprinkleSystem", type="integer"),
     *                      @OA\Property(property="elevators", type="integer"),
     *                      @OA\Property(property="rooftopDeck", type="integer"),
     *                      @OA\Property(property="fitnessCenter", type="integer"),
     *                      @OA\Property(property="publicWater", type="integer"),
     *                      @OA\Property(property="publicSewer", type="integer"),
     *                      @OA\Property(property="greenCertification", type="integer"),
     *                      @OA\Property(property="otherAmenities", type="string"),
     *                 )
     *             )
     *         }
     *     )
     * )
     * @param AttributesUpdateRequest $request
     * @param Building $building
     * @param BuildingService $buildingService
     * @return AttributesResource
     */
    public function store(AttributesUpdateRequest $request, Building $building, BuildingService $buildingService)
    {
        try {
            /** @var Attributes $attributesModel */
            $attributesModel = $buildingService->updateAttributes($request, $building);
            return new AttributesResource($attributesModel);
        } catch (\Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }

    /**
     * @OA\Get(
     *     tags={"Building Attributes"},
     *     path="/api/admin/attributes/{id}",
     *     summary="Get attributes property of building",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Building id",
     *         required=true,
     *     ),
     *     @OA\Response(response="200", description="Get attributes details of building"),
     *     @OA\Response(response="404", description="Not found attributes of building"),
     * )
     *
     * @param Building $building
     * @return AttributesResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function index(Building $building)
    {
        try {
            if ($building->attributes()->exists()) {
                return AttributesResource::make($building->attributes()->first());
            } else {
                return response(["error" => "Not found attributes details of building"], 404);
            }
        } catch (\Exception $exception) {
            return response(["error" => "Not found attributes details of building"], 404);
        }
    }
}
