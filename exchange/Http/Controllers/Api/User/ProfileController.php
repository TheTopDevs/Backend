<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ListerPhotoRequest;
use App\Http\Requests\User\ProfileUpdateRequest;
use App\Http\Requests\User\UserListerGeneralDataRequest;
use App\Http\Requests\User\UserPhotoRequest;
use App\Http\Resources\User\ListerInfoResource;
use App\Http\Resources\User\ProfileResource;
use App\Http\Resources\User\UserResource;
use App\Models\Lister;
use App\Models\Permission;
use App\Models\Profile;
use App\Services\FileService;
use App\Services\ListerService;
use App\Services\User\ProfileService;
use App\Services\User\UserService;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{

    public function __construct(
        private ProfileService $profileService,
        private UserService $userService,
        private FileService $fileService
    ) {
        $this->middleware(['permission:' . Permission::UPDATE_GENERAL_INFO])
            ->only(['updateProfileInfo', 'updateUserPhoto']);
        $this->middleware(['permission:' . Permission::UPDATE_LISTING_INFO])
            ->only(['updateListingInfo', 'updateListerPhoto']);
    }

    public function updateUserPhoto(UserPhotoRequest $request)
    {
        $userId = Auth::id();
        $path = $this->fileService->uploadUserPhoto($userId, $request->photo);
        $profile = $this->profileService->updateOrCreateUserProfile($userId, ['photo_path' => $path]);
        return ProfileResource::make($profile);
    }

    public function updateListerPhoto(ListerPhotoRequest $request)
    {
        $path = $this->fileService->uploadListerPhoto(Auth::id(), $request->photo);
        $listerInfo = $this->profileService->updateOrCreateListerInfo(Auth::id(), ['photo_path' => $path]);
        $lister = Auth::user()->lister ?? (new Lister());
        return ListerInfoResource::make($listerInfo)->setLister($lister);
    }

    public function updateProfileInfo(ProfileUpdateRequest $profileUpdateRequest)
    {
        $user = Auth::user();
        $dto = $profileUpdateRequest->profileFields();
        $this->userService->updateUser($user->id, ['username' => $profileUpdateRequest->username]);
        $dto = array_merge(
            $dto,
            $this->fileService->uploadProfileDocs($user->id, $profileUpdateRequest->filesFields())
        );
        $profile = $this->profileService->updateOrCreateUserProfile($user->id, $dto);
        return ProfileResource::make($profile);
    }

    public function updateListingInfo(UserListerGeneralDataRequest $request, ListerService $listerService)
    {
        $userId = Auth::id();
        $listerInfo = $this->profileService->updateOrCreateListerInfo($userId, $request->listerInfoFields());
        $lister = $listerService->updateOrCreateLister($userId, ['symbol' => $request->input('symbol')]);
        return ListerInfoResource::make($listerInfo)->setLister($lister);
    }

    public function getProfileInfo()
    {
        $user = Auth::user();
        $profile = $user->profile ?? Profile::query()->create(['user_id' => $user->id]);
        return response()->json(
            [
                'user' => UserResource::make($user),
                'profile' => ProfileResource::make($profile),
            ]
        );
    }

    public function getListingInfo()
    {
        $lister = Auth::user()->lister ?? (new Lister());
        return ListerInfoResource::make(Auth::user()->listerInfo)->setLister($lister);
    }
}
