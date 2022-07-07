<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactInformation\ContactInformationUpdateRequest;
use App\Http\Resources\ContactInformationResource;
use App\Models\Building;
use App\Models\ContactInformation;
use App\UseCases\BuildingService;

class ContactInformationController extends Controller
{
    /**
     * @OA\Get(
     *     tags={"Building contact information"},
     *     path="/api/admin/contacts/{id}",
     *     summary="Get contact information property of building",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Building id",
     *         required=true,
     *     ),
     *     @OA\Response(response="200", description="Get contact information of building"),
     *     @OA\Response(response="404", description="Not found contact information of building"),
     * )
     *
     */
    public function index(Building $building)
    {
        try {
            if ($building->contactInformation()->exists()) {
                return ContactInformationResource::make($building->contactInformation()->first());
            } else {
                return response(["error" => "Not found contact information of building"], 404);
            }
        } catch (\Exception $exception) {
            return response(["error" => "Not found contact information of building"], 404);
        }
    }

    /**
     * @OA\Post(
     *      tags={"Building contact information"},
     *      path="/api/admin/contacts/{id}",
     *      summary="Update contact information of building",
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
     *                 @OA\Property(property="ownerName", type="string", required={"false"}),
     *                 @OA\Property(property="ownerAddress", type="string", required={"false"}),
     *                 @OA\Property(property="ownerPhone", type="string", required={"false"}),
     *                 @OA\Property(property="ownerEmail", type="string", required={"false"}),
     *                 @OA\Property(property="managerCompanyName", type="string", required={"false"}),
     *                 @OA\Property(property="managerName", type="string", required={"false"}),
     *                 @OA\Property(property="managerAddress", type="string", required={"false"}),
     *                 @OA\Property(property="managerPhone", type="string", required={"false"}),
     *                 @OA\Property(property="managerEmail", type="string", required={"false"}),
     *                 @OA\Property(property="lenderBankName", type="string", required={"false"}),
     *                 @OA\Property(property="lenderName", type="string", required={"false"}),
     *                 @OA\Property(property="lenderAddress", type="string", required={"false"}),
     *                 @OA\Property(property="lenderPhone", type="string", required={"false"}),
     *                 @OA\Property(property="lenderEmail", type="string", required={"false"}),
     *                 @OA\Property(property="specialServicerCompanyName", type="string", required={"false"}),
     *                 @OA\Property(property="specialServicerName", type="string", required={"false"}),
     *                 @OA\Property(property="specialServicerAddress", type="string", required={"false"}),
     *                 @OA\Property(property="specialServicerPhone", type="string", required={"false"}),
     *                 @OA\Property(property="specialServicerEmail", type="string", required={"false"}),
     *                 @OA\Property(property="auctioneerCompanyName", type="string", required={"false"}),
     *                 @OA\Property(property="auctioneerLink", type="string", required={"false"}),
     *                 @OA\Property(property="brokerCompanyName", type="string", required={"false"}),
     *                 @OA\Property(property="brokerName", type="string", required={"false"}),
     *                 @OA\Property(property="brokerPhone", type="string", required={"false"}),
     *                 @OA\Property(property="brokerEmail", type="string", required={"false"}),
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
     *                     @OA\Property(property="ownerName", type="string", required={"false"}),
     *                     @OA\Property(property="ownerAddress", type="string", required={"false"}),
     *                     @OA\Property(property="ownerPhone", type="string", required={"false"}),
     *                     @OA\Property(property="ownerEmail", type="string", required={"false"}),
     *                     @OA\Property(property="managerCompanyName", type="string", required={"false"}),
     *                     @OA\Property(property="managerName", type="string", required={"false"}),
     *                     @OA\Property(property="managerAddress", type="string", required={"false"}),
     *                     @OA\Property(property="managerPhone", type="string", required={"false"}),
     *                     @OA\Property(property="managerEmail", type="string", required={"false"}),
     *                     @OA\Property(property="lenderBankName", type="string", required={"false"}),
     *                     @OA\Property(property="lenderName", type="string", required={"false"}),
     *                     @OA\Property(property="lenderAddress", type="string", required={"false"}),
     *                     @OA\Property(property="lenderPhone", type="string", required={"false"}),
     *                     @OA\Property(property="lenderEmail", type="string", required={"false"}),
     *                     @OA\Property(property="specialServicerCompanyName", type="string", required={"false"}),
     *                     @OA\Property(property="specialServicerName", type="string", required={"false"}),
     *                     @OA\Property(property="specialServicerAddress", type="string", required={"false"}),
     *                     @OA\Property(property="specialServicerPhone", type="string", required={"false"}),
     *                     @OA\Property(property="specialServicerEmail", type="string", required={"false"}),
     *                     @OA\Property(property="auctioneerCompanyName", type="string", required={"false"}),
     *                     @OA\Property(property="auctioneerLink", type="string", required={"false"}),
     *                     @OA\Property(property="brokerCompanyName", type="string", required={"false"}),
     *                     @OA\Property(property="brokerName", type="string", required={"false"}),
     *                     @OA\Property(property="brokerPhone", type="string", required={"false"}),
     *                     @OA\Property(property="brokerEmail", type="string", required={"false"}),
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
     *                     @OA\Property(property="ownerName", type="string"),
     *                     @OA\Property(property="ownerAddress", type="string"),
     *                     @OA\Property(property="ownerPhone", type="string"),
     *                     @OA\Property(property="ownerEmail", type="string"),
     *                     @OA\Property(property="managerCompanyName", type="string"),
     *                     @OA\Property(property="managerName", type="string"),
     *                     @OA\Property(property="managerAddress", type="string"),
     *                     @OA\Property(property="managerPhone", type="string"),
     *                     @OA\Property(property="managerEmail", type="string"),
     *                     @OA\Property(property="lenderBankName", type="string"),
     *                     @OA\Property(property="lenderName", type="string"),
     *                     @OA\Property(property="lenderAddress", type="string"),
     *                     @OA\Property(property="lenderPhone", type="string"),
     *                     @OA\Property(property="lenderEmail", type="string"),
     *                     @OA\Property(property="specialServicerCompanyName", type="string"),
     *                     @OA\Property(property="specialServicerName", type="string"),
     *                     @OA\Property(property="specialServicerAddress", type="string"),
     *                     @OA\Property(property="specialServicerPhone", type="string"),
     *                     @OA\Property(property="specialServicerEmail", type="string"),
     *                     @OA\Property(property="auctioneerCompanyName", type="string"),
     *                     @OA\Property(property="auctioneerLink", type="string"),
     *                     @OA\Property(property="brokerCompanyName", type="string"),
     *                     @OA\Property(property="brokerName", type="string"),
     *                     @OA\Property(property="brokerPhone", type="string"),
     *                     @OA\Property(property="brokerEmail", type="string"),
     *                 )
     *             )
     *         }
     *     )
     * )
     * @param ContactInformationUpdateRequest $request
     * @param Building $building
     * @param BuildingService $buildingService
     * @return ContactInformationResource|\Illuminate\Http\JsonResponse
     */
    public function store(ContactInformationUpdateRequest $request, Building $building, BuildingService $buildingService)
    {
        try {
            /** @var ContactInformation $contactInformationModel */
            $contactInformationModel = $buildingService->updateContactInformation($request, $building);
            return new ContactInformationResource($contactInformationModel);
        } catch (\Exception $exception) {
            return response()->json(['status' => false, 'message' => $exception->getMessage()], 422);
        }
    }
}
