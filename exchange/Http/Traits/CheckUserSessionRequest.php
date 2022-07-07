<?php

namespace App\Http\Traits;

use App\Services\UserAuthService;

trait CheckUserSessionRequest
{
    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    public function prepareForValidation()
    {
        if ($this->request->has('user_session')) {
            /** @var UserAuthService $class */
            $class = app()->make(UserAuthService::class);
            $class->getUserBySession($this->request->get('user_session'));
        }
    }
}
