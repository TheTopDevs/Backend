<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyFiles\GetFilesRequest;
use App\Http\Requests\PropertyFiles\UploadFileRequest;
use App\Http\Resources\PropertyFileResource;
use App\Models\Building;
use App\Models\PropertyFiles;
use App\UseCases\PropertyFilesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PropertyFileController extends Controller
{
    protected PropertyFilesService $propertyFileService;

    public function __construct(PropertyFilesService $filesService)
    {
        $this->propertyFileService = $filesService;
    }

    /**
     * @OA\Get(
     *     tags={"Building Property File"},
     *     path="/api/admin/property-files/{buildingId}",
     *     summary="Get all categories with counters of files for building",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="buildingId",
     *         in="path",
     *         description="Building id",
     *         required=true,
     *     ),
     *     @OA\Response(response="200", description="Get categories of property files in building"),
     * )
     *
     * @param Building $building
     * @return JsonResponse
     */
    public function categoryList(Building $building): JsonResponse
    {
        return response()->json(['data' => $this->propertyFileService->getCategoriesWithCounters($building)]);
    }

    /**
     * @OA\Post(
     *      tags={"Building Property File"},
     *      path="/api/admin/property-files/{buildingId}",
     *      summary="Upload property file for building",
     *      security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="buildingId",
     *         in="path",
     *         description="Building id",
     *         required=true,
     *     ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="file",type="file",required={"true"}),
     *                 @OA\Property(property="category_id",type="integer",required={"true"}),
     *             )
     *         ),
     *     ),
     *     @OA\Response(response="200", description="Property file uploaded success", @OA\MediaType(mediaType="application/json")),
     *     @OA\Response(response="422", description="Property file upload fial", @OA\MediaType(mediaType="application/json")),
     * )
     *
     * @param UploadFileRequest $request
     * @param Building $building
     * @return JsonResponse
     */
    public function uploadFile(UploadFileRequest $request, Building $building): JsonResponse
    {
        try {
            /** @var PropertyFiles $propertyFileModel */
            $propertyFileModel = $this->propertyFileService->uploadFile(
                $request,
                $building
            );
            return response()->json(
                [
                    'status' => "Success uploaded",
                    "id" => $propertyFileModel->id,
                    "name" => $propertyFileModel->name,
                ],
                200
            );
        } catch (\Exception $exception) {
            return response()->json(['status' => "Error", "message" => $exception->getMessage()], 422);
        }
    }

    /**
     * @OA\Get  (
     *      tags={"Building Property File"},
     *      path="/api/admin/property-files/{id}/download",
     *      summary="Download property file from building category",
     *      security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Property file Id",
     *         required=true,
     *     ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success download property file from building category", @OA\MediaType(mediaType="application/json")),
     *     @OA\Response(response="404", description="Not found file for download", @OA\MediaType(mediaType="application/json")),
     * )
     *
     * @param PropertyFiles $propertyFiles
     * @return JsonResponse
     */
    public function downloadFile(PropertyFiles $propertyFiles)
    {
        try {
            return $this->propertyFileService->downloadFile($propertyFiles);
        } catch (\Exception $exception) {
            return response()->json(['status'=>"Error", "message"=>"File not found"], 404);
        }
    }

    /**
     * @OA\Delete (
     *      tags={"Building Property File"},
     *      path="/api/admin/property-files/{id}",
     *      summary="Delete property file in building category",
     *      security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Property file Id",
     *         required=true,
     *     ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success delete property file from building category", @OA\MediaType(mediaType="application/json")),
     *     @OA\Response(response="422", description="Fail delete property file from building category", @OA\MediaType(mediaType="application/json")),
     * )
     *
     * @param PropertyFiles $propertyFiles
     * @return JsonResponse
     */
    public function deleteFile(PropertyFiles $propertyFiles): JsonResponse
    {
        try {
            $this->propertyFileService->deleteFile($propertyFiles);
        } catch (\Exception $exception) {
            return response()->json(['status' => "Error", "message" => $exception->getMessage()], 422);
        }
        return response()->json(['status' => "Success"], 200);
    }

    /**
     * @OA\Get(
     *     tags={"Building Property File"},
     *     path="/api/admin/property-files/{buildingId}/getFiles",
     *     summary="Get all files of building property category files",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="buildingId",
     *         in="path",
     *         description="Building id",
     *         required=true,
     *     ),
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="Category id of property files",
     *         required=true,
     *     ),
     *     @OA\Response(response="200", description="Get all property files from building category"),
     * )
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getFiles(GetFilesRequest $request, Building $building): AnonymousResourceCollection
    {
        return PropertyFileResource::collection($this->propertyFileService->getFiles($request, $building));
    }
}
