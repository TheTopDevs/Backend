<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyPhotos\PropertyPhotosUploadRequest;
use App\Http\Resources\PropertyPhotoResource;
use App\Models\Building;
use App\Models\PropertyPhoto;
use App\UseCases\PropertyPhotoService;
use Exception;
use Illuminate\Http\JsonResponse;

class PropertyPhotoController extends Controller
{
    private $servicePhoto;

    public function __construct(PropertyPhotoService $photoService)
    {
        $this->servicePhoto = $photoService;
    }

    /**
     * @OA\Get(
     *     tags={"Building Property Photo"},
     *     path="/api/admin/building-property/{buildingId}",
     *     summary="Get all property photos for building",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="buildingId",
     *         in="path",
     *         description="Building id",
     *         required=true,
     *     ),
     *     @OA\Response(response="200", description="Get all property photos for building"),
     * )
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Building $building)
    {
        return PropertyPhotoResource::collection($building->propertyPhotos()->get());
    }

    /**
     * @OA\Post(
     *      tags={"Building Property Photo"},
     *      path="/api/admin/building-property/uploadPhoto",
     *      summary="Upload property photo for building",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file",type="file",required={"true"}),
     *                 @OA\Property(property="type",type="integer",required={"true"}),
     *                 @OA\Property(property="buildingId",type="integer",required={"true"}),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Photo uploaded success", @OA\MediaType(mediaType="application/json")),
     *     @OA\Response(response="422", description="Photo upload fial", @OA\MediaType(mediaType="application/json")),
     * )
     *
     * @param PropertyPhotosUploadRequest $request
     * @return JsonResponse
     */

    public function uploadPhoto(PropertyPhotosUploadRequest $request)
    {
        try {
            $result = $this->servicePhoto->uploadPhoto($request);
            if (!$result) {
                return response()->json(['status' => "Error", "message" => "Not saved in DB photo"], 422);
            } else {
                return response()->json(['status' => "Success"], 200);
            }
        } catch (Exception $exception) {
            return response()->json(['status' => "Error", "message" => $exception->getMessage()], 422);
        }
    }

    /**
     * @OA\Post(
     *      tags={"Building Property Photo"},
     *      path="/api/admin/building-property/{id}/setMainPhoto",
     *      summary="Set main photo in building",
     *      security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Property Photo Id",
     *         required=true,
     *     ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *         )
     *     ),
     *     @OA\Response(response="200", description="Set main photo for building success", @OA\MediaType(mediaType="application/json")),
     *     @OA\Response(response="422", description="Set main photo for building is fail", @OA\MediaType(mediaType="application/json")),
     * )
     *
     * @param PropertyPhoto $propertyPhoto
     * @return JsonResponse
     */

    public function setMainPhoto(PropertyPhoto $propertyPhoto)
    {
        $result = $this->servicePhoto->setMainPhotoInBuilding($propertyPhoto);
        if (!$result) {
            return response()->json(['status' => "Error"], 422);
        } else {
            return response()->json(['status' => "Success"], 200);
        }
    }

    /**
     * @OA\Delete (
     *      tags={"Building Property Photo"},
     *      path="/api/admin/building-property/{id}/destroy",
     *      summary="Delete property photo in building",
     *      security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Property Photo Id",
     *         required=true,
     *     ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success delete property photo from building", @OA\MediaType(mediaType="application/json")),
     *     @OA\Response(response="422", description="Fail delete property photo from building", @OA\MediaType(mediaType="application/json")),
     * )
     *
     * @param PropertyPhoto $propertyPhoto
     * @return JsonResponse
     */
    public function destroy(PropertyPhoto $propertyPhoto)
    {
        try {
            $result = $this->servicePhoto->deletePhoto($propertyPhoto);
            if ($result) {
                return response()->json(['status' => "Success"], 200);
            } else {
                return response()->json(['status' => "Error"], 422);
            }
        } catch (Exception $exception) {
            return response()->json(['status' => "Error", "message" => $exception->getMessage()], 422);
        }
    }
}
