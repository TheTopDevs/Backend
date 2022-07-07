<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Building\BuildingCreateRequest;
use App\Http\Requests\Building\BuildingUpdateRequest;
use App\Http\Resources\BuildingResource;
use App\Models\Building;
use App\UseCases\BuildingService;
use App\UseCases\PropertyPhotoService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class BuildingController extends Controller
{
    /** @var BuildingService */
    protected $buildingService;

    public function __construct()
    {
        $this->buildingService = new BuildingService();
    }

    /**
     * @OA\Get(
     *     tags={"Building"},
     *     path="/api/admin/building",
     *     summary="Get all buildings with pagination",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Get all buildings with pagination"),
     * )
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return BuildingResource::collection(Building::latest()->paginate(15));
    }

    /**
     * @OA\Post(
     *      tags={"Building"},
     *      path="/api/admin/building",
     *      summary="User create new building",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(property="name",type="string",required={"true"}),
     *                 @OA\Property(property="hotelBrand",type="string",required={"true"}),
     *                 @OA\Property(property="generalMarkedServed", type="string", required={"false"}),
     *                 @OA\Property(property="specialException",type="string",required={"false"}),
     *                 @OA\Property(property="street",type="string",required={"false"}),
     *                 @OA\Property(property="numberOfRooms",type="integer",required={"false"}),
     *                 @OA\Property(property="stories",type="integer",required={"false"}),
     *                 @OA\Property(property="floodZone",type="integer",required={"false"}),
     *                 @OA\Property(property="city",type="string",required={"false"}),
     *                 @OA\Property(property="squareFeet",type="integer",required={"false"}),
     *                 @OA\Property(property="parkingSpaces",type="integer",required={"false"}),
     *                 @OA\Property(property="buildDate",type="string",format="date",required={"false"}),
     *                 @OA\Property(property="renovateDate",type="string",format="date",required={"false"}),
     *                 @OA\Property(property="garageParking",type="integer",required={"false"}),
     *                 @OA\Property(property="state",type="string",required={"false"}),
     *                 @OA\Property(property="landArea",type="string",required={"false"}),
     *                 @OA\Property(property="zoning",type="integer",required={"false"}),
     *                 @OA\Property(property="electricVehicleCharging",type="integer",required={"false"}),
     *                 @OA\Property(property="zipCode",type="string",required={"false"}),
     *                 @OA\Property(property="propertyId",type="integer",required={"false"}),
     *                 @OA\Property(property="countyTownship",type="string",required={"false"}),
     *                 @OA\Property(property="stateTaxId",type="integer",required={"false"}),
     *                 @OA\Property(property="hotelWebLink",type="string",required={"false"}),
     *                 @OA\Property(property="deadReference",type="string",required={"false"}),
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
     *                     @OA\Property(property="id",type="integer",description="Building id"),
     *                     @OA\Property(property="name",type="string"),
     *                     @OA\Property(property="hotelBrand",type="string"),
     *                     @OA\Property(property="generalMarkedServed", type="string"),
     *                     @OA\Property(property="specialException",type="string"),
     *                     @OA\Property(property="street",type="string"),
     *                     @OA\Property(property="numberOfRooms",type="integer"),
     *                     @OA\Property(property="stories",type="integer"),
     *                     @OA\Property(property="floodZone",type="integer"),
     *                     @OA\Property(property="city",type="string"),
     *                     @OA\Property(property="squareFeet",type="integer"),
     *                     @OA\Property(property="parkingSpaces",type="integer"),
     *                     @OA\Property(property="buildDate",type="string",format="date"),
     *                     @OA\Property(property="renovateDate",type="string",format="date"),
     *                     @OA\Property(property="garageParking",type="integer"),
     *                     @OA\Property(property="state",type="string"),
     *                     @OA\Property(property="landArea",type="string"),
     *                     @OA\Property(property="zoning",type="integer"),
     *                     @OA\Property(property="electricVehicleCharging",type="integer"),
     *                     @OA\Property(property="zipCode",type="string"),
     *                     @OA\Property(property="propertyId",type="integer"),
     *                     @OA\Property(property="countyTownship",type="string"),
     *                     @OA\Property(property="stateTaxId",type="integer"),
     *                     @OA\Property(property="hotelWebLink",type="string"),
     *                     @OA\Property(property="deadReference",type="string"),
     *                 )
     *             )
     *         }
     *     )
     * )
     *
     * @param BuildingCreateRequest $request
     * @return BuildingResource|JsonResource
     */

    public function store(BuildingCreateRequest $request)
    {
        $result = $this->buildingService->createBuilding($request);
        if ($result) {
            return new BuildingResource($result);
        }
        return response()->json(['status' => false, 'message' => 'Building was not created'], 422);
    }

    /**
     * @OA\Get(
     *     tags={"Building"},
     *     path="/api/admin/building/{id}",
     *     summary="Get one building for user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Building id",
     *         required=true,
     *     ),
     *     @OA\Response(response="200", description="Get one building with description"),
     * )
     *
     * @param Building $building
     * @return BuildingResource
     */

    public function show(Building $building): BuildingResource
    {
        return new BuildingResource($building);
    }

    /**
     * @OA\Patch (
     *      tags={"Building"},
     *      path="/api/admin/building/{id}",
     *      summary="User update exist building",
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
     *                 @OA\Property(property="name",type="string",required={"true"}),
     *                 @OA\Property(property="hotelBrand",type="string",required={"true"}),
     *                 @OA\Property(property="generalMarkedServed", type="string", required={"false"}),
     *                 @OA\Property(property="specialException",type="string",required={"false"}),
     *                 @OA\Property(property="street",type="string",required={"false"}),
     *                 @OA\Property(property="numberOfRooms",type="integer",required={"false"}),
     *                 @OA\Property(property="stories",type="integer",required={"false"}),
     *                 @OA\Property(property="floodZone",type="integer",required={"false"}),
     *                 @OA\Property(property="city",type="string",required={"false"}),
     *                 @OA\Property(property="squareFeet",type="integer",required={"false"}),
     *                 @OA\Property(property="parkingSpaces",type="integer",required={"false"}),
     *                 @OA\Property(property="buildDate",type="string",format="date",required={"false"}),
     *                 @OA\Property(property="renovateDate",type="string",format="date",required={"false"}),
     *                 @OA\Property(property="garageParking",type="integer",required={"false"}),
     *                 @OA\Property(property="state",type="string",required={"false"}),
     *                 @OA\Property(property="landArea",type="string",required={"false"}),
     *                 @OA\Property(property="zoning",type="integer",required={"false"}),
     *                 @OA\Property(property="electricVehicleCharging",type="integer",required={"false"}),
     *                 @OA\Property(property="zipCode",type="string",required={"false"}),
     *                 @OA\Property(property="propertyId",type="integer",required={"false"}),
     *                 @OA\Property(property="countyTownship",type="string",required={"false"}),
     *                 @OA\Property(property="stateTaxId",type="integer",required={"false"}),
     *                 @OA\Property(property="hotelWebLink",type="string",required={"false"}),
     *                 @OA\Property(property="deadReference",type="string",required={"false"}),
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
     *                     @OA\Property(property="id",type="integer",description="Building id"),
     *                     @OA\Property(property="name",type="string"),
     *                     @OA\Property(property="hotelBrand",type="string"),
     *                     @OA\Property(property="generalMarkedServed", type="string"),
     *                     @OA\Property(property="specialException",type="string"),
     *                     @OA\Property(property="street",type="string"),
     *                     @OA\Property(property="numberOfRooms",type="integer"),
     *                     @OA\Property(property="stories",type="integer"),
     *                     @OA\Property(property="floodZone",type="integer"),
     *                     @OA\Property(property="city",type="string"),
     *                     @OA\Property(property="squareFeet",type="integer"),
     *                     @OA\Property(property="parkingSpaces",type="integer"),
     *                     @OA\Property(property="buildDate",type="string",format="date"),
     *                     @OA\Property(property="renovateDate",type="string",format="date"),
     *                     @OA\Property(property="garageParking",type="integer"),
     *                     @OA\Property(property="state",type="string"),
     *                     @OA\Property(property="landArea",type="string"),
     *                     @OA\Property(property="zoning",type="integer"),
     *                     @OA\Property(property="electricVehicleCharging",type="integer"),
     *                     @OA\Property(property="zipCode",type="string"),
     *                     @OA\Property(property="propertyId",type="integer"),
     *                     @OA\Property(property="countyTownship",type="string"),
     *                     @OA\Property(property="stateTaxId",type="integer"),
     *                     @OA\Property(property="hotelWebLink",type="string"),
     *                     @OA\Property(property="deadReference",type="string"),
     *                 )
     *             )
     *         }
     *     )
     * )
     *
     * @param BuildingUpdateRequest $request
     * @param Building $building
     * @return BuildingResource|JsonResource
     */
    public function update(BuildingUpdateRequest $request, Building $building)
    {
        $result = $this->buildingService->updateBuilding($request, $building);
        if ($result) {
            return new BuildingResource($result);
        }
        return response()->json(['status' => false, 'message' => 'Building was not updated'], 422);
    }

    /**
     * @OA\Delete  (
     *      tags={"Building"},
     *      path="/api/admin/building/{id}",
     *      summary="User delete exist building",
     *      security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Building id",
     *         required=true,
     *     ),
     *     @OA\Response(response="204", description="Building successful deleted"),
     *     @OA\Response(response="422", description="Building was not deleted"),
     * )
     *
     * @param Building $building
     * @param PropertyPhotoService $propertyPhotoService
     * @return BuildingResource|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|JsonResource|Response
     * @throws \Exception
     */
    public function destroy(Building $building, PropertyPhotoService $propertyPhotoService)
    {
        $this->buildingService->deleteBuilding($building, $propertyPhotoService);
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
