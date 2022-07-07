<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Resources\RolesResource;
use App\Http\Resources\UsersResource;
use App\Models\User;
use App\Models\UserRoles;
use App\UseCases\UserService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UserController extends Controller
{
    /** @var UserService $userService */
    protected $userService;

    public function __construct(UserService $service)
    {
        $this->userService = $service;
    }

    /**
     * @OA\Get(
     *     tags={"Users"},
     *     path="/api/admin/users",
     *     summary="Get all users with pagination",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Get all users with pagination"),
     * )
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return UsersResource::collection(User::with('role')->paginate(15));
    }

    /**
     * @OA\Post(
     *      tags={"Users"},
     *      path="/api/admin/users",
     *      summary="User create new user",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(property="name",type="string",required={"true"}),
     *                 @OA\Property(property="email",type="email",required={"true"}),
     *                 @OA\Property(property="password", type="string", required={"true"}),
     *                 @OA\Property(property="role_id",type="integer",required={"true"}),
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
     *                     @OA\Property(property="id",type="integer",description="User id"),
     *                     @OA\Property(property="name",type="string",description="Name of user"),
     *                     @OA\Property(property="email",type="string",description="email of user"),
     *                     @OA\Property(property="role",type="string",description="role of user"),
     *                 )
     *             )
     *         }
     *     )
     * )
     *
     * @param CreateRequest $request
     * @return User|JsonResource
     */

    public function store(CreateRequest $request): UsersResource
    {
        //check access for create/register user
        /** @var UserRoles $role */
        $role = UserRoles::find($request->get('role_id'));
        //try create new user with roles(manager, jv_partner)
        //only admin user can create/registered manager user
        if ($role->name === UserRoles::MANAGER) {
            //only admin user can create manager user
            if (!Auth::user()->isAdminRole()) {
                abort(403);
            }
        }
        //only (managers,admins) - can create/registered jv_profiles users
        if ($role->name === UserRoles::JV_PARTNER) {
            if (!Auth::user()->isAdminRole() && !Auth::user()->isManagerRole()) {
                abort(403);
            }
        }
        return UsersResource::make($this->userService->createUser($request)->load('role'));
    }

    /**
     * @OA\Patch (
     *      tags={"Users"},
     *      path="/api/admin/users/{id}",
     *      summary="Update user info",
     *      security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User id",
     *         required=true,
     *     ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(property="name",type="string",required={"true"}),
     *                 @OA\Property(property="email",type="email",required={"true"}),
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
     *                     @OA\Property(property="id",type="integer",description="User id"),
     *                     @OA\Property(property="name",type="string",description="Name of user"),
     *                     @OA\Property(property="email",type="string",description="email of user"),
     *                     @OA\Property(property="rle",type="string",description="Role of user"),
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(response="422", description="User was not updated"),
     * )
     *
     * @param UpdateRequest $request
     * @param User $user
     * @return User|JsonResource
     */
    public function update(UpdateRequest $request, User $user)
    {
        //check access for update info in profiles
        $this->accessUpdateProfiles($user);

        return UsersResource::make($this->userService->updateUser($user, $request)->load('role'));
    }

    private function accessUpdateProfiles(User $user): void
    {
        //for update admin profiles - check access
        if ($user->isAdminRole()) {
            //only can admin update self profiles
            if (!Gate::allows('admin-update-self-profile', $user)) {
                abort(403);
            }
        }
        //for update manager profiles - check access
        if ($user->isManagerRole()) {
            //manager can update (self profile, all admins can)
            if (!Gate::allows('manager-update-self-profile', $user) || !Auth::user()->isAdminRole()) {
                abort(403);
            }
        }
        //for update jv-partner profiles - check access
        if ($user->isJvPartner()) {
            //jv_partner profile can update(self profiles jv_partner, all managers, all admins)
            if (!Gate::allows('crud_jv_partners', $user) || !Gate::allows('jv-partner-update-self-profile', $user)) {
                abort(403);
            }
        }
    }

    /**
     * @OA\Delete  (
     *      tags={"Users"},
     *      path="/api/admin/users/{id}",
     *      summary="User delete exist user",
     *      security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User id",
     *         required=true,
     *     ),
     *     @OA\Response(response="204", description="User successful deleted"),
     *     @OA\Response(response="422", description="User was not deleted"),
     * )
     *
     * @param User $user
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|Response
     */
    public function destroy(User $user)
    {
        //check access for delete profiles with others roles
        if ($user->isManagerRole()) {
            //for manager profiles, only admins can delete
            if (!Auth::user()->isAdminRole()) {
                abort(403);
            }
        }

        //check access for delete jv_partner profiles
        if ($user->isJvPartner()) {
            //only managers and admins can delete profiles
            if (!Gate::allows('crud_jv_partners', $user)) {
                abort(403);
            }
        }

        if ($this->userService->deleteUser($user)) {
            return response()->json(['status' => "Success"], 204);
        }
        return response()->json(['status' => "Error"], 422);
    }

    /**
     * @OA\Post(
     *      tags={"Users"},
     *      path="/api/admin/users/{id}/changePassword",
     *      summary="Update password for user",
     *      security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User id",
     *         required=true,
     *     ),
     *      @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/x-www-form-urlencoded",
     *             @OA\Schema(
     *                 @OA\Property(property="password_old",type="string",required={"true"}),
     *                 @OA\Property(property="password_new",type="string",required={"true"}),
     *                 @OA\Property(property="password_confirm",type="string",required={"true"}),
     *             )
     *         )
     *     ),
     *     @OA\Response(response="200", description="Success update password", @OA\MediaType(mediaType="application/json")),
     *     @OA\Response(response="422", description="Fail update password", @OA\MediaType(mediaType="application/json")),
     * )
     *
     * @param UpdatePasswordRequest $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(User $user, UpdatePasswordRequest $request)
    {
        //check access for update info in profiles
        $this->accessUpdateProfiles($user);

        if (!$this->userService->updatePassword($user, $request)) {
            return response()->json(['status' => "Error"], 422);
        } else {
            return response()->json(['status' => "Success"], 200);
        }
    }


    /**
     * @OA\Get(
     *     tags={"Users"},
     *     path="/api/admin/users/roles",
     *     summary="Get roles for users",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response="200", description="Get all roles for users"),
     * )
     *
     * @return AnonymousResourceCollection
     */
    public function roles(): AnonymousResourceCollection
    {
        return RolesResource::collection(UserRoles::all());
    }
}
